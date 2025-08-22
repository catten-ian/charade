<?php
// 修复用户房间状态的脚本
// 这个脚本会：
// 1. 查找只有一个用户的房间
// 2. 将这些房间中用户的in_room状态重置为0
// 3. 清理这些不完整的房间

// 引入数据库配置
session_start();

// 读取数据库配置文件
$config = parse_ini_file('db_config.ini', true);

// 连接数据库
$conn = mysqli_connect(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database'],
    $config['database']['port']
);

if (mysqli_connect_errno()) {
    die("数据库连接失败: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// 1. 查找只有一个用户的房间
$sql = "SELECT id, name, user_id0, user_id1 FROM tb_room WHERE user_cnt = 1";
$result = mysqli_query($conn, $sql);
$singleUserRooms = [];

while ($row = mysqli_fetch_assoc($result)) {
    $singleUserRooms[] = $row;
}

// 2. 将这些房间中用户的in_room状态重置为0
foreach ($singleUserRooms as $room) {
    $userId = $room['user_id0'] ?: $room['user_id1'];
    
    if ($userId) {
        // 获取用户信息
        $userSql = "SELECT name FROM tb_user WHERE id = $userId";
        $userResult = mysqli_query($conn, $userSql);
        $user = mysqli_fetch_assoc($userResult);
        $username = $user['name'] ?? '未知用户';
        
        // 重置用户的in_room状态
        $updateUserSql = "UPDATE tb_user SET in_room = 0 WHERE id = $userId";
        mysqli_query($conn, $updateUserSql);
        
        echo "用户 $username (ID: $userId) 的in_room状态已重置为0<br>";
    }
    
    // 3. 清理这个房间 - 将用户计数设为0，清除用户ID
    $updateRoomSql = "UPDATE tb_room SET user_cnt = 0, user_id0 = NULL, user_id1 = NULL WHERE id = {$room['id']}";
    mysqli_query($conn, $updateRoomSql);
    
    echo "房间 {$room['name']} (ID: {$room['id']}) 已清理<br>";
}

// 4. 额外清理：将所有用户的in_room状态重置为0，确保所有人都回到大厅
// 这是一个可选步骤，仅在需要时取消注释
// $resetAllUsersSql = "UPDATE tb_user SET in_room = 0";
// mysqli_query($conn, $resetAllUsersSql);
// echo "所有用户的in_room状态已重置为0<br>";

// 5. 清理tb_room表中所有用户计数为0的房间的word_id
$clearWordIdSql = "UPDATE tb_room SET word_id = 0 WHERE user_cnt = 0";
mysqli_query($conn, $clearWordIdSql);

// 6. 添加额外功能：处理一个用户退出房间的情况
// 当用户状态不为4（不在游戏中）且没有和另一个用户一起进入游戏（状态不为3）
// 查找状态异常的用户和对应的房间
$abnormalUsersSql = "SELECT id, name, in_room FROM tb_user WHERE in_room > 0 AND status NOT IN (3, 4)";
$abnormalUsersResult = mysqli_query($conn, $abnormalUsersSql);
$abnormalUsers = [];

while ($row = mysqli_fetch_assoc($abnormalUsersResult)) {
    $abnormalUsers[] = $row;
}

// 处理每个异常用户
foreach ($abnormalUsers as $user) {
    $roomId = $user['in_room'];
    
    // 获取房间信息
    $roomSql = "SELECT id, name, user_id0, user_id1, user_cnt FROM tb_room WHERE id = $roomId";
    $roomResult = mysqli_query($conn, $roomSql);
    $room = mysqli_fetch_assoc($roomResult);
    
    if ($room) {
        // 获取房间中的另一个用户ID
        $otherUserId = ($room['user_id0'] == $user['id']) ? $room['user_id1'] : $room['user_id0'];
        
        // 如果有另一个用户，也将其in_room状态重置为0
        if ($otherUserId) {
            $otherUserSql = "SELECT name FROM tb_user WHERE id = $otherUserId";
            $otherUserResult = mysqli_query($conn, $otherUserSql);
            $otherUser = mysqli_fetch_assoc($otherUserResult);
            $otherUsername = $otherUser['name'] ?? '未知用户';
            
            $updateOtherUserSql = "UPDATE tb_user SET in_room = 0 WHERE id = $otherUserId";
            mysqli_query($conn, $updateOtherUserSql);
            
            echo "用户 $otherUsername (ID: $otherUserId) 已从异常房间中移出<br>";
        }
        
        // 清理房间
        $deleteRoomSql = "UPDATE tb_room SET user_cnt = 0, user_id0 = NULL, user_id1 = NULL, word_id = 0 WHERE id = $roomId";
        mysqli_query($conn, $deleteRoomSql);
        
        echo "用户 {$user['name']} (ID: {$user['id']}) 所在的异常房间 {$room['name']} (ID: {$room['id']}) 已清理<br>";
        
        // 重置当前用户的in_room状态
        $updateUserSql = "UPDATE tb_user SET in_room = 0 WHERE id = {$user['id']}";
        mysqli_query($conn, $updateUserSql);
    }
}

echo "<br>修复完成！共有 " . count($singleUserRooms) . " 个只有一个用户的房间被清理。";

echo "<br>现在所有用户都应该能够重新从example room开始配对。";

// 关闭数据库连接
mysqli_close($conn);
?>