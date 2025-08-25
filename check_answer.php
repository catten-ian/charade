<?php
    // Start the session
    session_start();
    
    // 包含数据库配置
    include '../config.inc';
    
    // 连接数据库
    $conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
    if (mysqli_connect_errno()) {
        echo '数据库连接失败: ' . mysqli_connect_error();
        exit();
    }
    
    // 设置字符集
    mysqli_set_charset($conn, 'utf8');
    
    // 从SESSION中获取数据
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username']; // 保留作为辅助显示
    $room = $_SESSION['room'];
    $role = $_SESSION['role'];
    $user_guess = $_SESSION['current_guess']; // 从之前保存的SESSION中获取猜测
    
    // 优先从房间成员列表中获取第一个用户信息
    $first_user_id = '';
    $first_user_name = '';
    if (isset($_SESSION['room']['members']) && !empty($_SESSION['room']['members'])) {
        $first_user_id = $_SESSION['room']['members'][0]['id'];
        $first_user_name = $_SESSION['room']['members'][0]['name'];
    }
    
    // 保存first_user_id到SESSION
    $_SESSION['first_user_id'] = $first_user_id;
    
    // 优先从房间成员列表中获取第一个用户信息
    $first_user_name = '';
    if (isset($_SESSION['room']['members']) && !empty($_SESSION['room']['members'])) {
        $first_user_id = $_SESSION['room']['members'][0]['id'];
        $first_user_name = $_SESSION['room']['members'][0]['name'];
    }
    
    // 初始化猜测次数和历史记录
    if (!isset($_SESSION['guess_count'])) {
        $_SESSION['guess_count'] = 0;
        $_SESSION['guess_history'] = [];
    }
    
    // 增加猜测次数
    $_SESSION['guess_count']++;
    
    // 记录当前猜测
    $_SESSION['guess_history'][] = $user_guess;
    
    // 获取本轮游戏的正确答案
    $stmt = mysqli_prepare($conn, "SELECT current_word FROM tb_room WHERE name = ?");
    mysqli_stmt_bind_param($stmt, 's', $room);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $correct_word = $row['current_word'];
        
        // 保存正确答案到会话中，以便在结果页面显示
        $_SESSION['correct_word'] = $correct_word;
        $_SESSION['current_guess'] = $user_guess;
        
        // 比较用户猜测和正确答案（不区分大小写）
        if (strcasecmp($user_guess, $correct_word) === 0) {
            // 猜测正确
            
            // 更新用户分数
            $stmt = mysqli_prepare($conn, "UPDATE tb_user SET score = score + 1 WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            
            // 设置游戏状态为完成（猜对）
            $stmt = mysqli_prepare($conn, "UPDATE tb_room SET game_status = 'completed', winner_id = ? WHERE name = ?");
            mysqli_stmt_bind_param($stmt, 'is', $user_id, $room);
            mysqli_stmt_execute($stmt);
            
            // 记录猜对的用户
            $_SESSION['guessed_username'] = $username;
            
            // 重定向到正确页面
            header('Location: right.php');
        } else {
            // 猜测错误
            
            // 检查是否达到最大猜测次数
            if ($_SESSION['guess_count'] >= 3) {
                // 3次都猜错了，游戏结束
                
                // 设置游戏状态为完成（猜错）
                $stmt = mysqli_prepare($conn, "UPDATE tb_room SET game_status = 'completed' WHERE name = ?");
                mysqli_stmt_bind_param($stmt, 's', $room);
                mysqli_stmt_execute($stmt);
                
                // 重定向到错误页面
                header('Location: wrong.php');
            } else {
                // 还有猜测机会，返回guess.php继续猜测
                
                // 将当前猜测结果保存到会话，供guess.php显示
                header('Location: guess.php');
            }
        }
    } else {
        // 没有找到对应的房间或正确答案
        echo '游戏数据错误，请重新开始';
    }
    
    // 关闭数据库连接
    mysqli_close($conn);
?>