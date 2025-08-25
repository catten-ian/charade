<?php
    // Start the session
    session_start();
    
    // 检查是否接收到猜测内容
    if (isset($_POST['guess'])) {
        // 将用户猜测保存到SESSION
        $_SESSION['current_guess'] = $_POST['guess'];
        
        // 返回成功响应
        echo 'success';
    } else {
        // 返回错误响应
        echo 'error: no guess received';
    }
?>