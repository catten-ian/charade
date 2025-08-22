<?php
include "log.php";
// 会话开始
if (!session_id()) {
    session_start();
}

// 定义需要记录日志的用户ID
$log_user_ids = [8, 13];

// 日志辅助函数 - 只记录特定用户ID的日志
function shouldLog($user_id) {
    global $log_user_ids;
    return in_array($user_id, $log_user_ids);
}

include "../config.inc";
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);

if (mysqli_connect_errno()) {
    // 数据库连接失败是严重错误，无论用户ID都记录
    Logger::error("数据库连接失败: " . mysqli_connect_error());
    echo "connect db failed:" . mysqli_connect_error();
} else {
    // 暂时不记录连接成功日志，等获取用户ID后再判断
    header('Content-Type: application/json');
    mysqli_set_charset($conn, "utf8");
    
    // 优先使用user_id，如果没有则使用username
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $username = '';
    $cnt = 0;
    
    if ($user_id) {
        // 通过user_id获取username
        $stmt = mysqli_prepare($conn, "SELECT name FROM tb_user WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $ret = mysqli_stmt_get_result($stmt);
        $cnt = mysqli_num_rows($ret);
        
        if ($cnt >= 1) {
            $row = mysqli_fetch_row($ret);
            $username = $row[0];
        }
        mysqli_stmt_close($stmt);
    } else if (isset($_POST['username'])) {
        // 兼容旧的username方式
        $username = $_POST['username'];
        Logger::debug("username data:", $username);
        
        // 防止SQL注入
        $stmt = mysqli_prepare($conn, "SELECT id,name FROM tb_user WHERE name = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $ret = mysqli_stmt_get_result($stmt);
        $cnt = mysqli_num_rows($ret);
        $user_id = 0;
        
        if ($cnt < 1) {
            // 创建新用户
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO tb_user(name, in_room) VALUES(?, 0)");
            mysqli_stmt_bind_param($stmt_insert, 's', $username);
            if (mysqli_stmt_execute($stmt_insert)) {
                // 创建成功后再次查询
                $stmt_select = mysqli_prepare($conn, "SELECT id,name FROM tb_user WHERE name = ?");
                mysqli_stmt_bind_param($stmt_select, 's', $username);
                mysqli_stmt_execute($stmt_select);
                $ret = mysqli_stmt_get_result($stmt_select);
                $cnt = mysqli_num_rows($ret);
                mysqli_stmt_close($stmt_select);
            }
            mysqli_stmt_close($stmt_insert);
        }
        
        if ($cnt >= 1) {
            $row = mysqli_fetch_row($ret);
            $user_id = $row[0];
        }
        mysqli_stmt_close($stmt);
    }
    
    // 设置会话信息
    if ($user_id && $username) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
    }
// 现在有了用户ID，可以判断是否需要记录日志
if (shouldLog($user_id)) {
    Logger::info("进入匹配页面", ["username" => $_SESSION['username'], "user_id" => $user_id]);
    Logger::info("数据库连接成功", ["user_id" => $user_id]);
    Logger::info("开始匹配流程", ["username" => $username, "user_id" => $user_id]);
    Logger::info("用户名转义完成", ["username" => $username, "user_id" => $user_id]);
    
    if ($cnt < 1) {
        Logger::info("创建新用户", ["username" => $username, "user_id" => $user_id]);
        Logger::info("新用户创建成功", ["username" => $username, "user_id" => $user_id]);
    } else {
        Logger::info("用户已存在", ["username" => $username, "user_id" => $user_id]);
    }
    
    Logger::info("获取用户ID成功", ["username" => $username, "user_id" => $user_id]);
}

    // 检查用户是否在房间中
$in_room = false;
$current_room = '';
$room_status = 0; // 0:不在房间 1:在房间(等待中) 2:在房间(游戏中)
// 只记录特定用户ID的日志
if (shouldLog($user_id)) {
    Logger::info("检查用户房间状态", ["user_id" => $user_id]);
}
    
    // 查询用户所在房间
$sql_room = "SELECT id,name, user_cnt, user_id0,user_id1 from tb_room WHERE user_id0=$user_id or user_id1=$user_id order by user_cnt DESC";
// 只记录特定用户ID的日志
if (shouldLog($user_id)) {
    Logger::info("查询用户所在房间", ["user_id" => $user_id, "sql" => $sql_room]);
}
$ret_room = mysqli_query($conn, $sql_room);
$room_cnt = mysqli_num_rows($ret_room);
// 只记录特定用户ID的日志
if (shouldLog($user_id)) {
    Logger::info("房间查询结果", ["user_id" => $user_id, "room_count" => $room_cnt]);
}

    // 设置房间状态
if ($room_cnt >= 1) {
    $in_room = true;
    $row_room = mysqli_fetch_row($ret_room);
    $current_room = $row_room[1];
    $room_status = $row_room[2] > 1 ? 2 : 1;
    // 只记录特定用户ID的日志
    if (shouldLog($user_id)) {
        Logger::info("用户已在房间中", ["user_id" => $user_id, "room_name" => $current_room, "room_status" => $room_status]);
    }
    // 更新用户为在房间状态
    if (mysqli_query($conn, "UPDATE tb_user SET in_room=1 WHERE id=$user_id")) {
        // 只记录特定用户ID的日志
        if (shouldLog($user_id)) {
            Logger::info("更新用户房间状态成功", ["user_id" => $user_id, "in_room" => 1]);
        }
    } else {
        // 只记录特定用户ID的日志
        if (shouldLog($user_id)) {
            Logger::error("更新用户房间状态失败", ["user_id" => $user_id, "error" => mysqli_error($conn)]);
        }
    }
} else {
    // 只记录特定用户ID的日志
    if (shouldLog($user_id)) {
        Logger::info("用户不在房间中", ["user_id" => $user_id]);
    }
    // 更新用户为不在房间状态
    if (mysqli_query($conn, "UPDATE tb_user SET in_room=0 WHERE id=$user_id")) {
        // 只记录特定用户ID的日志
        if (shouldLog($user_id)) {
            Logger::info("更新用户房间状态成功", ["user_id" => $user_id, "in_room" => 0]);
        }
    } else {
        // 只记录特定用户ID的日志
        if (shouldLog($user_id)) {
            Logger::error("更新用户房间状态失败", ["user_id" => $user_id, "error" => mysqli_error($conn)]);
        }
    }
}

    class RetClass {
        public $ret_code = 0;
        public $username = "";
        public $rival = "";
        public $user_id = 0;
        public $rival_id = 0;
        public $room = "";
        public $first_user_id = 0;
        public $in_room = false; // 是否在房间中
        public $room_status = 0; // 房间状态 0:不在 1:等待 2:游戏中
    };

    if ($room_cnt >= 1) {
        $row = $row_room;
        if ($row[2] > 1) {
            $jret = new RetClass();
            $jret->ret_code = 0;
            $jret->username = $username;
            $jret->user_id = $user_id;
            $jret->room = $row[1];
            $jret->first_user_id = $row[3];
            $jret->in_room = true;
            $jret->room_status = 2;

            if ($user_id == $row[3]) {
                $jret->rival_id = $row[4];
            } else {
                $jret->rival_id = $row[3];
            }

            $sql_rival = "SELECT name from tb_user WHERE id=" . $jret->rival_id;
            $ret_rival = mysqli_query($conn, $sql_rival);
            $row_rival = mysqli_fetch_row($ret_rival);
            $jret->rival = $row_rival[0];
            // 更新对手为在房间状态
            mysqli_query($conn, "UPDATE tb_user SET in_room=1 WHERE id=" . $jret->rival_id);
            
            // 只记录特定用户ID的日志
            if (shouldLog($user_id)) {
                Logger::info("匹配成功", ["user_id" => $user_id, "rival_id" => $jret->rival_id, "room_name" => $row[1]]);
            }
                echo json_encode($jret, JSON_UNESCAPED_UNICODE);
        } else {
            if ($row[3] == $user_id) {
                $jret = new RetClass();
                $jret->ret_code = 1;
                $jret->room = $row[1];
                $jret->username = $username;
                $jret->user_id = $user_id;
                $jret->in_room = true;
                $jret->room_status = 1;
                
                echo json_encode($jret, JSON_UNESCAPED_UNICODE);
            } else {
                $jret = new RetClass();
                $jret->ret_code = 0;
                $jret->username = $username;
                $jret->user_id = $user_id;
                $jret->room = $row[1];
                $jret->rival_id = $row[3];
                $jret->first_user_id = $row[3];
                $jret->in_room = true;
                $jret->room_status = 2;

                $sql_rival = "SELECT name from tb_user WHERE id=" . $jret->rival_id;
                $ret_rival = mysqli_query($conn, $sql_rival);
                $row_rival = mysqli_fetch_row($ret_rival);
                $jret->rival = $row_rival[0];

                $sql_update = "UPDATE tb_room SET user_cnt=2, user_id1=$user_id WHERE id=" . $row[0];
                mysqli_query($conn, $sql_update);
                // 更新对手为在房间状态
                mysqli_query($conn, "UPDATE tb_user SET in_room=1 WHERE id=" . $jret->rival_id);
                
                echo json_encode($jret, JSON_UNESCAPED_UNICODE);
            }
        }
    } else {
        // 搜索空房间（不与状态为2、3、4、5、6的人匹配）
$sql_select = "SELECT id,name,user_cnt,user_id0 FROM tb_room WHERE user_cnt<=1 and (user_id0 IS NULL OR user_id0 NOT IN (SELECT id FROM tb_user WHERE type IN (2,3,4,5,6))) order by user_cnt desc";
// 只记录特定用户ID的日志
if (shouldLog($user_id)) {
    Logger::info("搜索空房间", ["user_id" => $user_id]);
}
$ret = mysqli_query($conn, $sql_select);
$cnt = mysqli_num_rows($ret);
// 只记录特定用户ID的日志
if (shouldLog($user_id)) {
    Logger::info("搜索空房间结果", ["user_id" => $user_id, "room_count" => $cnt]);
}

        if ($cnt <= 0) {
            $jret = new RetClass();
            $jret->ret_code = 2;
            $jret->username = $username;
            $jret->user_id = $user_id;
            $jret->in_room = false;
            $jret->room_status = 0;
            
            echo json_encode($jret, JSON_UNESCAPED_UNICODE);
        } else {
            $row = mysqli_fetch_row($ret);
            $_SESSION['room'] = $row[1];
            $_SESSION['room_id'] = $row[0];
            // 只记录特定用户ID的日志
            if (shouldLog($user_id)) {
                Logger::info("找到空房间", ["user_id" => $user_id, "room_name" => $_SESSION['room'], "room_id" => $_SESSION['room_id']]);
            }

            if ($row[2] < 1) {
                $sql_update = "UPDATE tb_room SET user_cnt = 1, user_id0=$user_id WHERE id=" . $row[0];
                mysqli_query($conn, $sql_update);
                // 更新当前用户为在房间状态
                mysqli_query($conn, "UPDATE tb_user SET in_room=1 WHERE id=$user_id");
                
                $jret = new RetClass();
                $jret->ret_code = 1;
                $jret->room = $_SESSION['room'];
                $jret->username = $username;
                $jret->user_id = $user_id;
                $jret->in_room = true;
                $jret->room_status = 1;
                
                echo json_encode($jret, JSON_UNESCAPED_UNICODE);
            } else {
                $jret = new RetClass();
                $jret->ret_code = 0;
                $jret->username = $username;
                $jret->user_id = $user_id;
                $jret->room = $row[1];
                $jret->rival_id = $row[3];
                $jret->first_user_id = $row[3];
                $jret->in_room = true;
                $jret->room_status = 2;

                $sql_update = "UPDATE tb_room SET user_cnt=2, user_id1=$user_id WHERE id=" . $row[0];
                mysqli_query($conn, $sql_update);
                // 更新当前用户和对手为在房间状态
                mysqli_query($conn, "UPDATE tb_user SET in_room=1 WHERE id=$user_id");
                mysqli_query($conn, "UPDATE tb_user SET in_room=1 WHERE id=" . $jret->rival_id);

                $sql_rival = "SELECT name from tb_user WHERE id=" . $jret->rival_id;
                $ret_rival = mysqli_query($conn, $sql_rival);
                $row_rival = mysqli_fetch_row($ret_rival);
                $jret->rival = $row_rival[0];
                
                echo json_encode($jret, JSON_UNESCAPED_UNICODE);
            }
        }
    }
    mysqli_close($conn);
}
?>