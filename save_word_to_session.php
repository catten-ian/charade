<?php
// 启动会话
session_start();

// 检查是否通过POST接收到selected_word参数
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_word'])) {
    $selected_word = $_POST['selected_word'];
    
    // 保存到SESSION
    $_SESSION['selected_word'] = $selected_word;
    
    // 可以选择将选择的词语保存到数据库中
    include '../config.inc';
    $conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
    if (mysqli_connect_errno()) {
        echo json_encode(['status' => 'error', 'message' => '数据库连接失败']);
        exit();
    }
    mysqli_set_charset($conn, 'utf8');
    
    // 从SESSION获取房间信息
    $room_id = isset($_SESSION['room_id']) ? (int)$_SESSION['room_id'] : 0;
    $room = isset($_SESSION['room']) ? $_SESSION['room'] : '';
    
    // 更新房间表，设置当前回合的词语
    // 优先使用room_id进行更新
    // if ($room_id > 0) {
    //     $stmt = mysqli_prepare($conn, "UPDATE tb_room SET current_word = ? WHERE id = ?");
    //     mysqli_stmt_bind_param($stmt, 'si', $selected_word, $room_id);
    // } else if (!empty($room)) {
    //     $stmt = mysqli_prepare($conn, "UPDATE tb_room SET current_word = ? WHERE name = ?");
    //     mysqli_stmt_bind_param($stmt, 'ss', $selected_word, $room);
    // }
    
    // if (isset($stmt)) {
    //     mysqli_stmt_execute($stmt);
        
        // 获取所选词汇在tb_words表中的id并更新房间的word_id
    $stmt = mysqli_prepare($conn, "SELECT id FROM tb_words WHERE word = ?");
    mysqli_stmt_bind_param($stmt, 's', $selected_word);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $word_id = $row['id'];
        // 优先使用room_id进行更新
    if ($room_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE tb_room SET word_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $word_id, $room_id);
    } else if (!empty($room)) {
        $stmt = mysqli_prepare($conn, "UPDATE tb_room SET word_id = ? WHERE name = ?");
        mysqli_stmt_bind_param($stmt, 'is', $word_id, $room);
    }
    if (isset($stmt)) {
        mysqli_stmt_execute($stmt);
    }
    }
    
    mysqli_close($conn);
    // }
    
    // 返回成功响应
    echo json_encode(['status' => 'success']);
} else {
    // 返回错误响应
    echo json_encode(['status' => 'error', 'message' => '缺少必要参数']);
}
?>