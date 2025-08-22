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
    
    // 获取房间名称和用户标识符
    $room = isset($_POST['room']) ? $_POST['room'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

    // 检查参数是否有效
    if (empty($room) || (empty($username) && empty($user_id))) {
        echo json_encode(array('success' => false, 'error' => 'Invalid parameters'));
        exit;
    }
    
    try {
        // 1. 获取当前用户ID
        $currentUserId = null;
        
        // 优先使用user_id获取用户
        if (!empty($user_id)) {
            $sql = "SELECT id FROM tb_users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $currentUserId = $row['id'];
            }
            $stmt->close();
        }
        
        // 如果通过user_id没有找到用户，或者没有提供user_id，则使用username
        if (!$currentUserId && !empty($username)) {
            $sql = "SELECT id FROM tb_users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $currentUserId = $row['id'];
            }
            $stmt->close();
        }
        
        if (!$currentUserId) {
            echo json_encode(array('success' => false, 'error' => 'Current user not found'));
            exit;
        }
        
        // 2. 获取未响应的猜测者（type为3且未跳转到guess页面的用户）
        $sql = "SELECT id, username FROM tb_users WHERE room = ? AND type = 3 AND page_type != 'guess'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $room);
        $stmt->execute();
        $unresponsiveGuessers = $stmt->get_result();
        $stmt->close();
        
        // 3. 将未响应的猜测者的type设置为6并从房间中踢出
        while ($guesser = $unresponsiveGuessers->fetch_assoc()) {
            $sql = "UPDATE tb_users SET type = 6, room = NULL, last_active = NOW() WHERE id = ?";
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
        
        // 4. 检查房间人数
        $sql = "SELECT COUNT(*) as count FROM tb_users WHERE room = ?";
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
            $sql = "SELECT id, username FROM tb_users WHERE room = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $room);
            $stmt->execute();
            $remainingUser = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            // 将剩余用户送回example room并重置类型为1
            $sql = "UPDATE tb_users SET type = 1, room = NULL, last_active = NOW() WHERE id = ?";
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