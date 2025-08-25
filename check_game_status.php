<?php
// 检查游戏状态的脚本
session_start();

// 优先从SESSION获取参数，其次从POST获取
$room = isset($_SESSION['room']) ? $_SESSION['room'] : (isset($_POST['room']) ? $_POST['room'] : '');
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : (isset($_POST['user_id']) ? intval($_POST['user_id']) : 0);

// 数据库连接
include '../config.inc';
$conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
if (mysqli_connect_errno()) {
    echo json_encode(array('game_over' => false, 'error' => '数据库连接失败: ' . mysqli_connect_error()));
    exit();
}
mysqli_set_charset($conn, 'utf8');

// 默认响应
$response = array(
    'game_over' => false,
    'correct_guess' => false
);

// 查询房间状态
if (!empty($room)) {
    $stmt = mysqli_prepare($conn, "SELECT game_status, winner_id FROM tb_room WHERE name = ?");
    mysqli_stmt_bind_param($stmt, 's', $room);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // 检查游戏是否结束（状态为2表示结束）
        if ($row['game_status'] == 2) {
            $response['game_over'] = true;
            // 检查是否有用户猜对（winner_id不为0）
            if ($row['winner_id'] != 0) {
                $response['correct_guess'] = true;
            }
        }
    }
}

// 关闭数据库连接
mysqli_close($conn);

// 返回JSON响应
echo json_encode($response);
?>