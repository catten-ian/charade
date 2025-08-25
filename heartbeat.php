<?php
include "../config.inc";
// include "log.php"; // 关闭日志功能
session_start();

// 连接数据库
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
if (mysqli_connect_errno()) {
    // Logger::error("数据库连接失败: " . mysqli_connect_error());
    die("数据库连接失败: " . mysqli_connect_error());
} else {
    // Logger::info("数据库连接成功");
}
mysqli_set_charset($conn, "utf8");

// 优先从SESSION中获取用户信息，其次从POST请求中获取
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_POST['user_id']) ? (int)$_POST['user_id'] : null);
$username = isset($_SESSION['username']) ? trim($_SESSION['username']) : (isset($_POST['username']) ? trim($_POST['username']) : '');
$isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
$isOnline = isset($_POST['is_online']) ? (int)$_POST['is_online'] : 1;
$pageType = isset($_POST['page_type']) ? trim($_POST['page_type']) : '';

// 如果从SESSION中获取了用户信息，也可以更新到SESSION中
if ($userId && !isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $userId;
}
if ($username && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $username;
}

// 如果没有user_id，则尝试通过username获取
if ($userId === null) {
    if (empty($username)) {
        mysqli_close($conn);
        exit;
    }
    
    // 使用预处理语句获取用户ID，避免SQL注入
    $userStmt = mysqli_prepare($conn, "SELECT id FROM tb_user WHERE name = ?");
    mysqli_stmt_bind_param($userStmt, 's', $username);
    mysqli_stmt_execute($userStmt);
    $userResult = mysqli_stmt_get_result($userStmt);
    if ($userRow = mysqli_fetch_assoc($userResult)) {
        $userId = $userRow['id'];
        // Logger::info("获取用户ID成功", ["username" => $username, "user_id" => $userId]);
    } else {
        // Logger::warning("用户不存在", ["username" => $username]);
        mysqli_stmt_close($userStmt);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_close($userStmt);
} else {
    // 如果有user_id，验证用户是否存在
    $userStmt = mysqli_prepare($conn, "SELECT name FROM tb_user WHERE id = ?");
    mysqli_stmt_bind_param($userStmt, 'i', $userId);
    mysqli_stmt_execute($userStmt);
    $userResult = mysqli_stmt_get_result($userStmt);
    if ($userRow = mysqli_fetch_assoc($userResult)) {
        $username = $userRow['name']; // 更新username变量
    } else {
        // Logger::warning("用户不存在", ["user_id" => $userId]);
        mysqli_stmt_close($userStmt);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_close($userStmt);
}

// 1. 更新最后活动时间（使用用户ID更高效）
$updateTimeStmt = mysqli_prepare($conn, "UPDATE tb_user SET last_active_time = NOW() WHERE id = ?");
mysqli_stmt_bind_param($updateTimeStmt, 'i', $userId);
if (mysqli_stmt_execute($updateTimeStmt)) {
    // Logger::info("更新用户最后活动时间成功", ["user_id" => $userId]);
} else {
    // Logger::error("更新用户最后活动时间失败", ["user_id" => $userId, "error" => mysqli_stmt_error($updateTimeStmt)]);
}
mysqli_stmt_close($updateTimeStmt);

// 2. 检查用户是否在房间中
$inRoom = 0;
$roomStmt = mysqli_prepare($conn, "SELECT id FROM tb_room WHERE user_id0 = ? OR user_id1 = ?");
mysqli_stmt_bind_param($roomStmt, 'ii', $userId, $userId);
mysqli_stmt_execute($roomStmt);
$roomResult = mysqli_stmt_get_result($roomStmt);
if (mysqli_num_rows($roomResult) > 0) {
    $inRoom = 1;
    // Logger::info("用户在房间中", ["user_id" => $userId]);
} else {
    // Logger::info("用户不在房间中", ["user_id" => $userId]);
}
mysqli_stmt_close($roomStmt);

// 3. 根据状态更新type字段
    if ($isOnline == 0) {
        // 页面关闭/离线：type=5
        $type = 5;
    } else {
        // 检查页面类型
        if ($pageType == 'start') {
            // 在start页面：type=4
            $type = 4;
        } else if (in_array($pageType, ['guess', 'choose', 'right', 'wrong', 'end', 'rest', 'waiting', 'describe'])) {
            // 在其他特殊游戏页面：type=3
            $type = 3;
        } else if ($pageType == 'exampleroom' || $pageType == 'exampleroom2') {
            // 在example room或example room2页面：根据是否在房间内和活跃度判断
            $type = $inRoom ? 1 : ($isActive ? 1 : 6);
        } else {
            // 其他情况：根据活跃度判断
            $type = $isActive ? 1 : 6;
        }
    }

// 执行更新type的SQL
$updateTypeStmt = mysqli_prepare($conn, "UPDATE tb_user SET type = ? WHERE id = ?");
mysqli_stmt_bind_param($updateTypeStmt, 'ii', $type, $userId);
if (mysqli_stmt_execute($updateTypeStmt)) {
    // Logger::info("更新用户类型成功", ["user_id" => $userId, "type" => $type, "page_type" => $pageType]);
} else {
    // Logger::error("更新用户类型失败", ["user_id" => $userId, "type" => $type, "error" => mysqli_stmt_error($updateTypeStmt)]);
}
mysqli_stmt_close($updateTypeStmt);

// 关闭数据库连接
mysqli_close($conn);
?>