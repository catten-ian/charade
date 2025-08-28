<?php
// 确保这是一个AJAX请求
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    // 非AJAX请求也允许访问，以便调试
}

// 获取参数
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$new_role = isset($_GET['new_role']) ? $_GET['new_role'] : '';

// 验证参数
if ($user_id <= 0 || empty($new_role)) {
    echo "无效的参数: user_id=$user_id, new_role=$new_role";
    http_response_code(400);
    exit;
}

// 验证角色值
$valid_roles = ['describer', 'guesser', 'chooser', 'waiting'];
if (!in_array($new_role, $valid_roles)) {
    echo "无效的角色值: $new_role";
    http_response_code(400);
    exit;
}

// 数据库配置
include '../config.inc';

// 连接数据库
$conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);

// 检查连接
if (!$conn) {
    echo "数据库连接失败: " . mysqli_connect_error();
    http_response_code(500);
    exit;
}

// 更新用户角色
$sql = "UPDATE tb_user SET role = ? WHERE id = ?";

// 准备并执行语句
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $new_role, $user_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "成功更新用户ID $user_id 的角色为 $new_role";
        http_response_code(200);
    } else {
        echo "未找到用户ID $user_id 或角色未变更";
        http_response_code(404);
    }
} else {
    echo "更新角色失败: " . mysqli_error($conn);
    http_response_code(500);
}

// 关闭连接
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>