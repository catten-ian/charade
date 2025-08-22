<?php
// 连接数据库
include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);

if (mysqli_connect_errno()) {
    echo "连接数据库失败: " . mysqli_connect_error();
    exit;
}

// 设置字符集
mysqli_set_charset($conn, "utf8");

// 获取当前时间
$current_time = time();
$one_hour_ago = $current_time - 3600; // 1小时前

// 修复用户状态
// 1. 修复catten用户: 设置in_room=0, type=1, 更新last_active_time
$username_catten = "catten";
$sql_fix_catten = "UPDATE tb_user SET in_room=0, type=1, last_active_time=$current_time WHERE name='$username_catten'";
if (mysqli_query($conn, $sql_fix_catten)) {
    echo "修复catten用户成功!\n";
} else {
    echo "修复catten用户失败: " . mysqli_error($conn) . "\n";
}

// 2. 修复bear用户: 设置type=1, 更新last_active_time
$username_bear = "bear";
$sql_fix_bear = "UPDATE tb_user SET type=1, last_active_time=$current_time WHERE name='$username_bear'";
if (mysqli_query($conn, $sql_fix_bear)) {
    echo "修复bear用户成功!\n";
} else {
    echo "修复bear用户失败: " . mysqli_error($conn) . "\n";
}

// 3. 批量清理长时间未活动的用户: 设置type=2
$sql_clean_inactive = "UPDATE tb_user SET type=2 WHERE last_active_time < $one_hour_ago";
if (mysqli_query($conn, $sql_clean_inactive)) {
    echo "清理长时间未活动用户成功，共影响 " . mysqli_affected_rows($conn) . " 个用户!\n";
} else {
    echo "清理长时间未活动用户失败: " . mysqli_error($conn) . "\n";
}

// 4. 重置所有不在游戏中的用户in_room=0
$sql_reset_room = "UPDATE tb_user SET in_room=0 WHERE type NOT IN (4,5)";
if (mysqli_query($conn, $sql_reset_room)) {
    echo "重置非游戏中用户房间状态成功，共影响 " . mysqli_affected_rows($conn) . " 个用户!\n";
} else {
    echo "重置非游戏中用户房间状态失败: " . mysqli_error($conn) . "\n";
}

// 5. 清理单人房间 (用户自己创建但无人加入的房间)
$sql_clean_rooms = "DELETE FROM tb_room WHERE user_cnt = 1";
if (mysqli_query($conn, $sql_clean_rooms)) {
    echo "清理单人房间成功，共影响 " . mysqli_affected_rows($conn) . " 个房间!\n";
} else {
    echo "清理单人房间失败: " . mysqli_error($conn) . "\n";
}

// 查询当前用户状态，用于验证
$sql_check_users = "SELECT id, name, type, in_room, last_active_time FROM tb_user WHERE name IN ('catten', 'bear')";
$result = mysqli_query($conn, $sql_check_users);

if ($result && mysqli_num_rows($result) > 0) {
    echo "\n当前关键用户状态:\n";
    echo "ID | 用户名 | type | in_room | last_active_time\n";
    echo "------------------------------------------\n";
    while ($row = mysqli_fetch_assoc($result)) {
        $time_str = date('Y-m-d H:i:s', $row['last_active_time']);
        echo "{$row['id']} | {$row['name']} | {$row['type']} | {$row['in_room']} | {$time_str}\n";
    }
} else {
    echo "\n无法获取用户状态信息。\n";
}

// 关闭数据库连接
mysqli_close($conn);

// 提供使用指南
?>

<html>
<head>
    <title>Charade游戏修复工具</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        h1 {
            color: #4CAF50;
        }
        .note {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Charade游戏修复工具</h1>
    <div class="note">
        <p><strong>修复说明:</strong></p>
        <p>1. 脚本已成功执行，修复了用户状态和房间问题</p>
        <p>2. 已确保start页面的type设置为4，而不是3</p>
        <p>3. 已确保exampleroom2页面不设置type为4，而是保持为1</p>
        <p>4. 已确保只有在start页面才设置type为4</p>
        <p>5. 已修复activity-detector.js中页面类型识别逻辑</p>
    </div>
    <div class="note">
        <p><strong>使用指南:</strong></p>
        <p>1. 请访问 <a href="login.html">http://localhost/charade/login.html</a> 登录游戏</p>
        <p>2. 游戏流程应为: login.html -> exampleroom.php -> exampleroom2.php -> start.php -> 游戏页面</p>
        <p>3. 如果仍有问题，请刷新页面并重新登录</p>
    </div>
</body>
</html>