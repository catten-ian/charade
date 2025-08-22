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
    
    // 获取房间名称和用户标识符
    $room = isset($_POST['room']) ? $_POST['room'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

    // 检查参数是否有效
    if (empty($room) || (empty($username) && empty($user_id))) {
        echo json_encode(array('allReady' => true));
        exit;
    }
    
    try {
        // 检查房间中的所有猜测者是否已经跳转到guess页面
        $sql = "SELECT COUNT(*) as count FROM tb_users WHERE room = ? AND type = 3 AND page_type != 'guess'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $room);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // 如果还有未跳转的猜测者，返回false；否则返回true
        $allReady = ($row['count'] == 0);
        echo json_encode(array('allReady' => $allReady));
        
        $stmt->close();
    } catch (Exception $e) {
        // 发生错误时，默认返回所有猜测者都已准备好
        echo json_encode(array('allReady' => true));
    }
    
    mysqli_close($conn);
?>