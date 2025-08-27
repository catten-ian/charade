<?php
// 启动会话以访问SESSION变量
session_start();

// 检查是否为POST请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 准备成功消息
    $success_message = 'No parameters saved';
    
    // 检查是否传递了room_id参数
    if (isset($_POST['room_id']) && !empty($_POST['room_id'])) {
        // 将room_id保存到SESSION中
        $_SESSION['room_id'] = $_POST['room_id'];
        $success_message = 'room_id saved successfully';
    }
    
    // 检查是否传递了role参数
    if (isset($_POST['role']) && !empty($_POST['role'])) {
        // 将role保存到SESSION中
        $_SESSION['role'] = $_POST['role'];
        if ($success_message === 'No parameters saved') {
            $success_message = 'role saved successfully';
        } else {
            $success_message = 'room_id and role saved successfully';
        }
    }
    
    // 返回成功响应
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => $success_message]);
    exit;
} else {
    // 如果不是POST请求，返回错误响应
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Only POST requests are allowed']);
    exit;
}
?>