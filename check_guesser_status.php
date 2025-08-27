<?php
    // Start the session
    session_start();
    
    include "../config.inc";
    
    $conn=mysqli_connect("localhost",$db_user,$db_password,$db_name, $db_port);
    if(mysqli_connect_errno())
    {
        echo json_encode(array('allReady' => true)); // 在数据库连接失败的情况下，默认返回所有猜测者都已准备好
        exit;
    }
    
    mysqli_set_charset($conn,"utf8");
    
    // 完全从SESSION中获取房间名称、房间ID和用户标识
    $room = isset($_SESSION['room']) ? $_SESSION['room'] : '';
    $room_id = isset($_SESSION['room_id']) ? (int)$_SESSION['room_id'] : 0;
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    
    // 检查参数是否有效
    if ((empty($room) && $room_id == 0) || ($user_id == 0 && empty($username))) {
        echo json_encode(array('allReady' => true));
        exit;
    }
    
    // 如果没有user_id，通过username获取
    if ($user_id == 0) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM tb_user WHERE name = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $user_id = $row['id'];
        } else {
            // 用户不存在
            echo json_encode(array('allReady' => true));
            exit;
        }
        mysqli_stmt_close($stmt);
    }
    
    try {
        // 获取房间中的所有用户
        // 优先使用room_id进行查询
        if ($room_id > 0) {
            $sql = "SELECT u.id, u.type, u.page_type 
                    FROM tb_user u 
                    JOIN tb_room r ON u.id = r.user_id0 OR u.id = r.user_id1 
                    WHERE r.id = ? AND u.id != ?";
            $stmt = $conn->prepare($sql);
            mysqli_stmt_bind_param($stmt, "ii", $room_id, $user_id);
        } else {
            $sql = "SELECT u.id, u.type, u.page_type 
                    FROM tb_user u 
                    JOIN tb_room r ON u.id = r.user_id0 OR u.id = r.user_id1 
                    WHERE r.name = ? AND u.id != ?";
            $stmt = $conn->prepare($sql);
            mysqli_stmt_bind_param($stmt, "si", $room, $user_id);
        }
        
        mysqli_stmt_execute($stmt);
        $result = $stmt->get_result();
        
        $allReady = true;
        while ($row = mysqli_fetch_assoc($result)) {
            // 检查其他用户是否已准备好（type=3且page_type='guess'）
            if ($row['type'] != 3 || $row['page_type'] != 'guess') {
                $allReady = false;
                break;
            }
        }
        
        echo json_encode(array('allReady' => $allReady));
        
        $stmt->close();
    } catch (Exception $e) {
        // 发生错误时，默认返回所有猜测者都已准备好
        echo json_encode(array('allReady' => true));
    }
    
    mysqli_close($conn);
?>