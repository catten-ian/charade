<?php
    // Start the session
    session_start();
    
    include "../config.inc";
    
    $conn=mysqli_connect("localhost",$db_user,$db_password,$db_name, $db_port);
    if(mysqli_connect_errno())
    {
        echo json_encode(array('success' => false, 'error' => 'Database connection failed'));
        exit;
    }
    
    mysqli_set_charset($conn,"utf8");
    
    // 优先从SESSION中获取房间名称和用户标识
    $room = isset($_SESSION['room']) ? $_SESSION['room'] : (isset($_POST['room']) ? $_POST['room'] : '');
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0);
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_POST['username']) ? $_POST['username'] : '');
    
    // 检查参数是否有效
    if (empty($room) || ($user_id == 0 && empty($username))) {
        echo json_encode(array('success' => false, 'error' => 'Invalid parameters'));
        exit;
    }
    
    // 如果没有user_id，通过username获取
    if ($user_id == 0) {
        $sql = "SELECT id FROM tb_user WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user_id = $row['id'];
        } else {
            echo json_encode(array('success' => false, 'error' => 'Current user not found'));
            exit;
        }
        $stmt->close();
    } else {
        // 如果有user_id，获取对应的username
        $sql = "SELECT name FROM tb_user WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $username = $row['name'];
        } else {
            echo json_encode(array('success' => false, 'error' => 'Current user not found'));
            exit;
        }
        $stmt->close();
    }
    
    try {
        // 1. 检查当前用户是否在房间中
        $sql = "SELECT COUNT(*) as count FROM tb_room WHERE (user_id0 = ? OR user_id1 = ?) AND name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $user_id, $room);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row['count'] == 0) {
            echo json_encode(array('success' => false, 'error' => 'User not in this room'));
            exit;
        }
        $stmt->close();
        
        // 2. 获取未响应的猜测者（type为3且未跳转到guess页面的用户）
        // 首先获取房间中的所有用户
        $sql = "SELECT u.id, u.name as username, u.type, u.page_type 
                FROM tb_user u 
                JOIN tb_room r ON u.id = r.user_id0 OR u.id = r.user_id1 
                WHERE r.name = ? AND u.id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $room, $user_id);
        $stmt->execute();
        $unresponsiveGuessers = $stmt->get_result();
        $stmt->close();
        
        // 3. 将未响应的猜测者的type设置为6并从房间中踢出
        while ($guesser = $unresponsiveGuessers->fetch_assoc()) {
            // 只处理type为3且page_type不是guess的用户
            if ($guesser['type'] == 3 && $guesser['page_type'] != 'guess') {
                $sql = "UPDATE tb_user SET type = 6, room = NULL, last_active = NOW() WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $guesser['id']);
                $stmt->execute();
                $stmt->close();
                
                // 记录日志
                $logMessage = "用户 {" . $guesser['username'] . "} 因未响应被踢出房间 {" . $room . "}";
                $sql = "INSERT INTO tb_logs (username, action, log_time) VALUES (?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $guesser['username'], $logMessage);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        // 4. 检查房间人数
        $sql = "SELECT COUNT(*) as count 
                FROM tb_user u 
                JOIN tb_room r ON u.id = r.user_id0 OR u.id = r.user_id1 
                WHERE r.name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $room);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $roomCount = $row['count'];
        $stmt->close();
        
        // 5. 如果房间人数只有一人，将该用户送回example room并重新匹配
        if ($roomCount == 1) {
            // 获取房间中剩余的用户
            $sql = "SELECT u.id, u.name as username 
                    FROM tb_user u 
                    JOIN tb_room r ON u.id = r.user_id0 OR u.id = r.user_id1 
                    WHERE r.name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $room);
            $stmt->execute();
            $remainingUser = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            // 将剩余用户送回example room并重置类型为1
            $sql = "UPDATE tb_user SET type = 1, room = NULL, last_active = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $remainingUser['id']);
            $stmt->execute();
            $stmt->close();
            
            // 记录日志
            $logMessage = "房间 {" . $room . "} 人数不足，用户 {" . $remainingUser['username'] . "} 被送回匹配房间";
            $sql = "INSERT INTO tb_logs (username, action, log_time) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $remainingUser['username'], $logMessage);
            $stmt->execute();
            $stmt->close();
            
            // 标记房间为需要重新匹配
            $sql = "INSERT INTO tb_rematch_queue (room, username, user_id, timestamp) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $room, $remainingUser['username'], $remainingUser['id']);
            $stmt->execute();
            $stmt->close();
        }
        
        echo json_encode(array('success' => true));
    } catch (Exception $e) {
        echo json_encode(array('success' => false, 'error' => $e->getMessage()));
    }
    
    mysqli_close($conn);
?>