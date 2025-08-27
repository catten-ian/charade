<?php
/**
 * 处理前端AJAX日志请求的文件
 */

// 启动会话
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 引入日志文件
include "log.php";

// 定义应该记录日志的用户ID列表
$log_user_ids = [8, 14];

// 检查用户是否应该记录日志的函数
function shouldLog($user_id) {
    global $log_user_ids;
    return in_array($user_id, $log_user_ids);
}

// 获取请求参数
$action = isset($_POST['action']) ? $_POST['action'] : '';
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
$room = isset($_POST['room']) ? $_POST['room'] : '';

// 根据动作类型记录不同的日志
if (shouldLog($user_id)) {
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';
    
    switch ($action) {
        case 'game_timeout':
            Logger::info('游戏超时', [
                'user_id' => $user_id,
                'username' => $username,
                'room' => $room,
                'room_id' => $room_id
            ]);
            break;
        
        case 'check_guesser_status':
            $status = isset($_POST['status']) ? json_decode($_POST['status'], true) : [];
            Logger::debug('检查猜测者状态', [
                'user_id' => $user_id,
                'username' => $username,
                'room' => $room,
                'room_id' => $room_id,
                'status' => $status
            ]);
            break;
        
        case 'check_guesser_status_error':
            $error = isset($_POST['error']) ? $_POST['error'] : '';
            Logger::warning('检查猜测者状态时出错', [
                'user_id' => $user_id,
                'room_id' => $room_id,
                'error' => $error
            ]);
            break;
        
        case 'word_displayed':
            $selected_word = isset($_POST['selected_word']) ? $_POST['selected_word'] : '';
            Logger::info('单词显示完成', [
                'user_id' => $user_id,
                'username' => $username,
                'selected_word' => $selected_word
            ]);
            break;
        
        case 'user_activity':
            $elapsed_seconds = isset($_POST['elapsed_seconds']) ? intval($_POST['elapsed_seconds']) : 0;
            Logger::debug('用户活动记录', [
                'user_id' => $user_id,
                'username' => $username,
                'room_id' => $room_id,
                'elapsed_seconds' => $elapsed_seconds
            ]);
            break;
        
        case 'user_guess':
            $guess = isset($_POST['guess']) ? $_POST['guess'] : '';
            Logger::info('用户猜测单词', [
                'user_id' => $user_id,
                'username' => $username,
                'room_id' => $room_id,
                'guess' => $guess
            ]);
            break;
        
        case 'save_guess_failed':
            $error_code = isset($_POST['error_code']) ? intval($_POST['error_code']) : 0;
            Logger::warning('保存猜测失败', [
                'user_id' => $user_id,
                'room_id' => $room_id,
                'error_code' => $error_code
            ]);
            break;
        
        case 'guess_timeout':
            Logger::info('猜测超时', [
                'user_id' => $user_id,
                'username' => $username,
                'room_id' => $room_id
            ]);
            break;
            
        case 'check_guess_failed':
            $error_code = isset($_POST['error_code']) ? intval($_POST['error_code']) : 0;
            Logger::warning('检查猜测结果请求失败', [
                'user_id' => $user_id,
                'room_id' => $room_id,
                'error_code' => $error_code
            ]);
            break;
        
        // 可以根据需要添加更多的动作类型
        default:
            Logger::debug('未知的AJAX日志动作', [
                'action' => $action,
                'user_id' => $user_id,
                'room' => $room,
                'room_id' => $room_id
            ]);
            break;
    }
}

// 返回成功响应
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>