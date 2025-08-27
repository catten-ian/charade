<?php
    // 启动会话
    session_start();
    
    // 数据库连接配置
    include '../config.inc';
    $servername = "localhost";
    $username = $db_user;
    $password = $db_password;
    $dbname = $db_name;
    $db_port = $db_port;
    
    // 创建数据库连接
    $conn = new mysqli($servername, $username, $password, $dbname, $db_port);
    
    // 检查连接是否成功
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }
    
    // 优先从SESSION获取参数，其次从POST获取
    $room = isset($_SESSION['room']) ? $_SESSION['room'] : ($_POST['room'] ?? '');
    $room_id = isset($_SESSION['room_id']) ? (int)$_SESSION['room_id'] : (isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0);
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0);
    $status = $_POST['status'] ?? '';
    
    // 验证必要参数是否存在，必须同时有room和room_id
    if (empty($room) || empty($room_id) || empty($user_id) || empty($status)) {
        echo json_encode(['success' => false, 'message' => '缺少必要参数']);
        exit;
    }
    
    // 根据状态更新游戏数据
    switch ($status) {
        case 'correct_guess':
            // 用户猜对了，更新房间状态和获胜者
            // 优先使用room_id来更新房间状态
            $sql = "UPDATE tb_room SET game_status = 'completed', winner_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $room_id);  // user_id和room_id都是整数类型
            $stmt->execute();
            $stmt->close();
            
            // 记录用户猜对的信息
            $sql = "INSERT INTO tb_game_record (room, room_id, user_id, action, timestamp) VALUES (?, ?, ?, 'correct_guess', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $room, $room_id, $user_id);  // room_id和user_id是整数类型
            $stmt->execute();
            $stmt->close();
            break;
            
        case 'all_wrong':
            // 用户3次全猜错了，记录信息
            $sql = "INSERT INTO tb_game_record (room, room_id, user_id, action, timestamp) VALUES (?, ?, ?, 'all_wrong', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $room, $room_id, $user_id);  // room_id和user_id是整数类型
            $stmt->execute();
            $stmt->close();
            
            // 检查是否所有猜测者都猜错了
            $sql = "SELECT COUNT(*) as total, COUNT(CASE WHEN action = 'all_wrong' THEN 1 END) as wrong_count 
                    FROM tb_game_record 
                    WHERE room_id = ? AND timestamp > DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            // 假设房间中有2名玩家，1名描述者，1名猜测者
            if ($row['wrong_count'] == 1) {
                // 所有猜测者都猜错了，更新房间状态
                $sql = "UPDATE tb_room SET game_status = 'completed', winner_id = NULL WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $room_id);
                $stmt->execute();
                $stmt->close();
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => '未知状态']);
            exit;
    }
    
    // 关闭数据库连接
    $conn->close();
    
    // 返回成功响应
    echo json_encode(['success' => true, 'message' => '游戏状态更新成功']);
?>