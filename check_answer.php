<?php
    // 设置响应类型为JSON
    header('Content-Type: application/json');
    
    // Start the session
    session_start();
    
    // 包含数据库配置
    include '../config.inc';
    
    // 包含日志功能
    include 'log.php';
        
    // 定义应该记录日志的用户ID列表
    $log_user_ids = [8, 14];
    
    // 检查是否应该记录日志
    function shouldLog() {
        global $log_user_ids, $_SESSION;
        return isset($_SESSION['user_id']) && in_array($_SESSION['user_id'], $log_user_ids);
    }
    
    // 连接数据库
    $conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
    if (mysqli_connect_errno()) {
        // 记录错误日志
        if (shouldLog()) {
            $context = ['user_id' => $_SESSION['user_id'] ?? '', 'username' => $_SESSION['username'] ?? '', 'room' => ''];
            Logger::error('数据库连接失败: ' . mysqli_connect_error(), $context);
        }
        echo json_encode(['status' => 'error', 'message' => '数据库连接失败: ' . mysqli_connect_error()]);
        exit();
    }
    
    // 设置字符集
    mysqli_set_charset($conn, 'utf8');
    
    // 从POST参数获取猜测内容
    if (isset($_POST['guess'])) {
        $user_guess = $_POST['guess'];
        // 保存到SESSION
        $_SESSION['current_guess'] = $user_guess;
    } else {
        // 如果没有POST参数，尝试从SESSION获取
        $user_guess = isset($_SESSION['current_guess']) ? $_SESSION['current_guess'] : '';
    }
    
    // 从SESSION获取数据
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $room = $_SESSION['room']['name'];
    $room_id = isset($_SESSION['room']['id']) ? $_SESSION['room']['id'] : '';
    $role = $_SESSION['role'];
    
    // 记录开始检查答案的日志
    if (shouldLog()) {
        $context = ['user_id' => $user_id, 'username' => $username, 'room' => $room, 'room_id' => $room_id, 'user_guess' => $user_guess];
        Logger::info('开始检查用户猜测答案', $context);
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
    
    // 结果数组
    $response = ['status' => 'error', 'message' => '未知错误'];
    
    // 获取本轮游戏的正确答案
    if (!empty($room_id) && is_numeric($room_id)) {
        $stmt = mysqli_prepare($conn, "SELECT word_id FROM tb_room WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $room_id);
        if (shouldLog()) {
            $context = ['user_id' => $_SESSION['user_id'] ?? '', 'username' => $_SESSION['username'] ?? '', 'room' => $room, 'room_id' => $room_id, 'sql' => "SELECT word_id FROM tb_room WHERE id = $room_id"];
            Logger::debug('准备执行SQL查询房间对应的word_id', $context);
        }
    } else {
        $stmt = mysqli_prepare($conn, "SELECT word_id FROM tb_room WHERE name = ?");
        mysqli_stmt_bind_param($stmt, 's', $room);
        if (shouldLog()) {
            $context = ['user_id' => $_SESSION['user_id'] ?? '', 'username' => $_SESSION['username'] ?? '', 'room' => $room, 'room_id' => $room_id, 'sql' => "SELECT word_id FROM tb_room WHERE name = '$room'"];
            Logger::debug('准备执行SQL查询房间对应的word_id', $context);
        }
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $selected_word = $row['word_id'];
        // 从单词表中获取正确单词
        $stmt = mysqli_prepare($conn, "SELECT word FROM tb_words WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $selected_word);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        // 记录开始获取正确单词的日志
        if (shouldLog()) {
            $context = ['user_id' => $user_id, 'username' => $username, 'room' => $room, 'room_id' => $room_id, 'selected_word_id' => $selected_word];
            Logger::debug('开始从单词表获取正确单词', $context);
        }
        if ($row = mysqli_fetch_assoc($result)) {
            $selected_word = $row['word'];
        }
        // 保存正确答案到会话中
        $_SESSION['selected_word'] = $selected_word;
        
        // 比较用户猜测和正确答案（不区分大小写）
        if (strcasecmp($user_guess, $selected_word) === 0) {
            // 猜测正确
            
            // 记录猜对日志
            if (shouldLog()) {
                $context = ['user_id' => $user_id, 'username' => $username, 'room' => $room, 'room_id' => $room_id, 'user_guess' => $user_guess, 'correct_word' => $selected_word];
                Logger::info('用户猜测正确', $context);
            }
            
            // 更新用户分数
            $stmt = mysqli_prepare($conn, "UPDATE tb_user SET score = score + 1 WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            
            // 设置游戏状态为完成（猜对）
            if (!empty($room_id) && is_numeric($room_id)) {
                $stmt = mysqli_prepare($conn, "UPDATE tb_room SET status = 3, winner = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'ii', $user_id, $room_id);
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE tb_room SET status = 3, winner = ? WHERE name = ?");
                mysqli_stmt_bind_param($stmt, 'is', $user_id, $room);
            }
            mysqli_stmt_execute($stmt);
            
            // 记录猜对的用户
                $_SESSION['room']['winner'] = $user_id;
                $_SESSION['room']['winner_name'] = $username;
                
                // 记录游戏完成日志
                if (shouldLog()) {
                    $context = ['user_id' => $user_id, 'username' => $username, 'room' => $room, 'room_id' => $room_id, 'winner_id' => $user_id, 'winner_name' => $username];
                    Logger::info('游戏完成 - 用户猜对', $context);
                }
                
                // 返回正确结果
            $response = ['status' => 'correct', 'word' => $selected_word];
        } else {
            // 猜测错误
            
            // 记录猜错日志
            if (shouldLog()) {
                $context = ['user_id' => $user_id, 'username' => $username, 'room' => $room, 'room_id' => $room_id, 'user_guess' => $user_guess, 'correct_word' => $selected_word, 'guess_count' => $_SESSION['guess_count']];
                Logger::info('用户猜测错误', $context);
            }
            
            // 检查是否达到最大猜测次数
            if ($_SESSION['guess_count'] >= 3) {
                // 3次都猜错了，游戏结束
                
                // 设置游戏状态为完成（猜错）
                
                // 记录游戏完成日志
                if (shouldLog()) {
                    $context = ['user_id' => $user_id, 'username' => $username, 'room' => $room, 'room_id' => $room_id, 'correct_word' => $selected_word];
                    Logger::info('游戏完成 - 达到最大猜测次数', $context);
                }
                if (!empty($room_id) && is_numeric($room_id)) {
                    $stmt = mysqli_prepare($conn, "UPDATE tb_room SET status = 3 WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, 'i', $room_id);
                } else {
                    $stmt = mysqli_prepare($conn, "UPDATE tb_room SET status = 3 WHERE name = ?");
                    mysqli_stmt_bind_param($stmt, 's', $room);
                }
                mysqli_stmt_execute($stmt);
                
                // 返回错误结果并标记游戏结束
                $response = ['status' => 'wrong', 'game_over' => true, 'remaining_guesses' => 0, 'word' => $selected_word];
            } else {
                // 还有猜测机会，返回继续猜测
                $remaining_guesses = 3 - $_SESSION['guess_count'];
                $response = ['status' => 'wrong', 'game_over' => false, 'remaining_guesses' => $remaining_guesses];
            }
        }
    } else {
        // 没有找到对应的房间或正确答案
        $response = ['status' => 'error', 'message' => '游戏数据错误，请重新开始'];
        
        // 记录游戏数据错误日志
        if (shouldLog()) {
            $context = ['user_id' => $user_id, 'username' => $username, 'room' => $room, 'room_id' => $room_id];
            Logger::warning('游戏数据错误', $context);
        }
    }
    
    // 记录响应结果日志
    if (shouldLog()) {
        $context = ['user_id' => $user_id, 'username' => $username, 'room' => $room, 'room_id' => $room_id, 'response_status' => $response['status']];
        Logger::debug('检查答案响应结果', $context);
    }
    
    // 关闭数据库连接
    mysqli_close($conn);
    
    // 返回JSON响应
    echo json_encode($response);
    exit;
?>