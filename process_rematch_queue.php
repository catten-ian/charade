<?php
    // Start the session
    session_start();
    
    include "../config.inc";
    
    $conn=mysqli_connect("localhost",$db_user,$db_password,$db_name, $db_port);
    if(mysqli_connect_errno())
    {
        echo "Database connection failed: " . mysqli_connect_error();
        exit;
    }
    
    mysqli_set_charset($conn,"utf8");
    
    // 定期执行该脚本，处理重新匹配队列中的用户
    try {
        // 1. 检查是否有用户在重新匹配队列中等待超过10秒
        $sql = "SELECT id, username, user_id FROM tb_rematch_queue WHERE timestamp < NOW() - INTERVAL 10 SECOND";
        $result = mysqli_query($conn, $sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $queueId = $row['id'];
            $username = $row['username'];
            $userId = $row['user_id'];
            
            // 2. 将用户类型重新设置为1
            $sql = "UPDATE tb_users SET type = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
            
            // 3. 记录日志
            $logMessage = "用户 {" . $username . "} 重新设置为可匹配状态";
            $sql = "INSERT INTO tb_logs (username, action, log_time) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $logMessage);
            $stmt->execute();
            $stmt->close();
            
            // 4. 从重新匹配队列中删除该用户
            $sql = "DELETE FROM tb_rematch_queue WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $queueId);
            $stmt->execute();
            $stmt->close();
            
            // 5. 尝试为该用户找到新的匹配对象（这里简单实现，实际项目中可能需要更复杂的匹配逻辑）
            $sql = "SELECT id, username FROM tb_users WHERE type = 1 AND id != ? ORDER BY RAND() LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $matchResult = $stmt->get_result();
            
            if ($matchRow = $matchResult->fetch_assoc()) {
                // 找到了匹配对象，创建新房间
                $newRoom = "room_" . uniqid();
                $matchedUserId = $matchRow['id'];
                $matchedUsername = $matchRow['username'];
                
                // 更新两个用户的房间信息
                $sql = "UPDATE tb_users SET type = 2, room = ?, last_active = NOW() WHERE id = ? OR id = ?";
                $stmt2 = $conn->prepare($sql);
                $stmt2->bind_param("sii", $newRoom, $userId, $matchedUserId);
                $stmt2->execute();
                $stmt2->close();
                
                // 记录日志
                $logMessage = "用户 {" . $username . "} 和用户 {" . $matchedUsername . "} 匹配成功，房间: {" . $newRoom . "}";
                $sql = "INSERT INTO tb_logs (username, action, log_time) VALUES (?, ?, NOW())";
                $stmt2 = $conn->prepare($sql);
                $stmt2->bind_param("ss", $username, $logMessage);
                $stmt2->execute();
                $stmt2->close();
            }
            
            $stmt->close();
        }
        
        // 如果没有匹配到，系统将在下次运行时继续尝试
    } catch (Exception $e) {
        // 记录错误日志
        $errorMsg = "处理重新匹配队列时出错: " . $e->getMessage();
        $sql = "INSERT INTO tb_logs (username, action, log_time) VALUES ('system', ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $errorMsg);
        $stmt->execute();
        $stmt->close();
    }
    
    mysqli_close($conn);
?>