<?php
include "log.php";
// 定义需要记录日志的用户ID
$log_user_ids = [8, 14];

// 日志辅助函数 - 只记录特定用户ID的日志
function shouldLog($user_id) {
    global $log_user_ids;
    return in_array($user_id, $log_user_ids);
}

// 服务器端角色分配脚本
// 读取数据库配置
$config = parse_ini_file('db_config.ini');

// 设置响应头
header('Content-Type: application/json');

// 获取请求参数
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;

// 初始化返回结果
$response = array(
    'status' => 'error',
    'message' => '参数无效',
    'role' => null
);

// 记录日志 - 只记录特定用户ID
if (shouldLog($user_id)) {
    Logger::info("接收到角色分配请求", ["user_id" => $user_id, "room_id" => $room_id]);
}

// 验证参数
if ($user_id <= 0 || $room_id <= 0) {
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::warning("角色分配参数验证失败", ["user_id" => $user_id, "room_id" => $room_id]);
    }
    echo json_encode($response);
    exit;
}

try {
    // 连接数据库
    $conn = new mysqli(
        $config['host'],
        $config['user'],
        $config['password'],
        $config['database'],
        $config['port']
    );
    
    // 检查连接
    if ($conn->connect_error) {
        // 数据库连接失败是严重错误，无论用户ID都记录
        Logger::error("数据库连接失败: " . $conn->connect_error);
        throw new Exception('数据库连接失败: ' . $conn->connect_error);
    }
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("数据库连接成功", ["user_id" => $user_id, "room_id" => $room_id]);
    }
    
    // 开始事务
    $conn->begin_transaction();
    
    // 检查用户是否存在于当前房间
    $check_sql = "SELECT COUNT(*) as count FROM tb_room WHERE id = ? AND FIND_IN_SET(?, CONCAT_WS(',', user_id0, user_id1, user_id2, user_id3, user_id4, user_id5, user_id6, user_id7, user_id8, user_id9))";
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("检查用户房间归属", ["user_id" => $user_id, "room_id" => $room_id, "sql" => $check_sql]);
    }
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('ii', $room_id, $user_id);
// 记录即将执行的SQL语句
// if (shouldLog($user_id)) {
//     Logger::info("即将执行检查用户房间归属的SQL语句", ["user_id" => $user_id, "room_id" => $room_id, "sql" => $check_sql, "params" => ["user_id" => $user_id, "room_id" => $room_id]]);
// }
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("用户房间归属检查结果", ["user_id" => $user_id, "room_id" => $room_id, "count" => $check_row['count']]);
    }
    
    if ($check_row['count'] == 0) {
        throw new Exception('用户不在指定房间内');
    }
    
    // 分配角色逻辑
    $assigned_role = 0;
    // 将用户的role在数据库中设置为0
    $update_sql = "UPDATE tb_user SET role = 0 WHERE id = ?";

    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("准备重置用户角色为0", ["user_id" => $user_id, "room_id" => $room_id, "sql" => $update_sql]);
    }

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('i', $user_id);

    if (!$update_stmt->execute()) {
        throw new Exception('重置角色为0失败: ' . $update_stmt->error);
    }

    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("用户角色重置为0成功", ["user_id" => $user_id, "room_id" => $room_id]);
    }
    // 检查房间内已有的角色分配情况
    $role_sql = "SELECT u.role FROM tb_user u JOIN tb_room r ON FIND_IN_SET(u.id, CONCAT_WS(',', r.user_id0, r.user_id1, r.user_id2, r.user_id3, r.user_id4, r.user_id5, r.user_id6, r.user_id7, r.user_id8, r.user_id9)) WHERE r.id = ? AND u.role IN (1, 2)";
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("查询房间内已有角色分配", ["user_id" => $user_id, "room_id" => $room_id, "sql" => $role_sql]);
    }
    
    $role_stmt = $conn->prepare($role_sql);
    $role_stmt->bind_param('i', $room_id);
    $role_stmt->execute();
    $role_result = $role_stmt->get_result();
    
    $roles_in_room = array();
    while ($role_row = $role_result->fetch_assoc()) {
        $roles_in_room[] = $role_row['role'];
    }
    
    
    // 计算房间内各种角色的数量
    $describer_count = count(array_keys($roles_in_room, 1));
    $guesser_count = count(array_keys($roles_in_room, 2));
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("房间角色统计", ["user_id" => $user_id, "room_id" => $room_id, "describer_count" => $describer_count, "guesser_count" => $guesser_count]);
    }
    
    // 优先分配描述者(1)，如果还没有描述者，就分配描述者角色
    if ($describer_count == 0) {
        $assigned_role = 1; // describer
    } else {
        // 否则随机分配猜测者角色
        $assigned_role = 2; // guesser
    }
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("角色分配决策", ["user_id" => $user_id, "room_id" => $room_id, "assigned_role" => $assigned_role]);
    }
    
    // 更新用户的角色信息
    $update_sql = "UPDATE tb_user SET role = ? WHERE id = ?";
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("准备更新用户角色", ["user_id" => $user_id, "room_id" => $room_id, "sql" => $update_sql]);
    }
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ii', $assigned_role, $user_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('更新角色失败: ' . $update_stmt->error);
    }
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("用户角色更新成功", ["user_id" => $user_id, "room_id" => $room_id, "assigned_role" => $assigned_role]);
    }
    
    // 提交事务
    $conn->commit();
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("事务提交成功", ["user_id" => $user_id, "room_id" => $room_id]);
    }
    
    // 准备成功响应
    $response['status'] = 'success';
    $response['message'] = '角色分配成功';
    $response['role'] = $assigned_role == 1 ? 'describer' : 'guesser';
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("角色分配完成", ["user_id" => $user_id, "room_id" => $room_id, "role" => $response['role']]);
    }
    
    // 关闭语句和连接
    $check_stmt->close();
    $role_stmt->close();
    $update_stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // 回滚事务
    if (isset($conn) && method_exists($conn, 'rollback')) {
        $conn->rollback();
        // 记录日志 - 只记录特定用户ID
        if (shouldLog($user_id)) {
            Logger::info("事务回滚成功", ["user_id" => $user_id, "room_id" => $room_id]);
        }
    }
    
    // 记录错误
    $error_message = $e->getMessage();
    $response['message'] = $error_message;
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::error("角色分配失败", ["user_id" => $user_id, "room_id" => $room_id, "error" => $error_message]);
    }
}

// 返回JSON响应
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>