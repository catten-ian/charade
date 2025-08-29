<?php
include "log.php";
session_start();
// 关闭错误报告
error_reporting(0);
// 定义需要记录日志的用户ID
$log_user_ids = [8, 13];

// 日志辅助函数 - 只记录特定用户ID的日志
function shouldLog($user_id) {
    global $log_user_ids;
    return in_array($user_id, $log_user_ids);
}
// $_SESSION['username'] = $_POST['username'];
Logger::debug("username data:", $_SESSION['username']);
// 先不记录，等获取到用户ID后再判断是否记录

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
    $username = $_SESSION['username'];
    // 暂时不记录开始匹配流程日志，等获取用户ID后再判断
    
    // 防止SQL注入
$username = mysqli_real_escape_string($conn, $username);

class RetClass {
    public $ret_code = 1;
    public $username = "";
    public $user_id = 0;
    public $room = "";
    public $in_room = false; // 是否在房间中
    public $room_status = 0; // 房间状态 0:不在 1:等待 2:游戏中
};

// 获取或创建用户
$sql_select = "SELECT id,name FROM tb_user WHERE name='$username'";
$ret = mysqli_query($conn, $sql_select);
$cnt = mysqli_num_rows($ret);
$user_id = 0;

if ($cnt < 1) {
    // 暂时不记录创建新用户日志，等获取用户ID后再判断
    $sql_insert = "insert into tb_user(name, in_room) values('$username', 0)"; // 新增用户时默认不在房间
    if (mysqli_query($conn, $sql_insert)) {
        // 暂时不记录创建成功日志，等获取用户ID后再判断
    } else {
        // 暂时不记录创建失败日志，等获取用户ID后再判断
    }
    $ret = mysqli_query($conn, $sql_select);
}
    
$row = mysqli_fetch_row($ret);
$_SESSION['user_id'] = $row[0];
$user_id = $row[0];
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

mysqli_query($conn, "UPDATE tb_user SET role=0 WHERE id=$user_id");
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
    if($row_room[3] != NULL && $row_room[4] != NULL)
    {
        if($row_room[2] != 2)
        {
            mysqli_query($conn, "UPDATE tb_room SET user_cnt=2 WHERE id=$row_room[0]");
        }
        $jret = new RetClass();
        $jret->ret_code = 0;
        $jret->username = $username;
        $jret->user_id = $user_id;
        $jret->room = $row_room[1];
        $jret->in_room = true;
        $jret->room_status = 2;
        $members = [];
        $member_indices = [3, 4, 17, 18, 19, 20, 21, 22, 23, 24];
        foreach ($member_indices as $idx) {
            // 只记录特定用户ID的日志
            $id = $row_room[$idx];
            if ($id === null) {
                $members[] = ['id' => null, 'name' => null, 'score' => 0];
            } else {
                // 查询用户名称
                $sql = "SELECT name FROM tb_user WHERE id = $id";
                $ret = mysqli_query($conn, $sql);
                $name_row = mysqli_fetch_row($ret);
                $name = $name_row ? $name_row[0] : null;
                $members[] = ['id' => $id, 'name' => $name, 'score' => 0, 'role' => 0];
            }
        }

        // 重构后的房间结构，优先使用members数组存储用户信息
        $_SESSION['room'] = [
            'name' => $row_room[1],
            'id' => $row_room[0],
            'user_cnt' => $row_room[2],
            'type' => $row_room[5],
            'word_type' => $row_room[6],
            'level_type' => $row_room[7],
            'max_people' => $row_room[8],
            'least_people' => $row_room[9],
            'personal_try' => $row_room[10],
            'team_try' => $row_room[11],
            'match_random' => $row_room[12],
            'vocab_custom_count' => $row_room[13],
            'vocab_custom' => $row_room[14],
            'winner' => $row_room[15],
            'word_id' => $row_room[16],
            'members' => $members,
            'status' => $row_room[25],
            'round' => $row_room[26],
        ];
        if (shouldLog($user_id)) {
            Logger::info("匹配成功", ["user_id" => $user_id, "room_id" => $row_room[0], "room_name" => $row_room[1], "room_user_ids" => [$row_room[3], $row_room[4]]]);
        }
        echo json_encode($jret, JSON_UNESCAPED_UNICODE); //用户所在房间满员
    }
    else if($row_room[3] != NULL || $row_room[4] != NULL)
    {
        if($row_room[2] != 1)
        {
            mysqli_query($conn, "UPDATE tb_room SET user_cnt=1 WHERE id=$row_room[0]");
        }
        $jret = new RetClass();
        $jret->ret_code = 1;
        $jret->username = $username;
        $jret->user_id = $user_id;
        $jret->room = $row_room[1];
        $jret->in_room = true;
        $jret->room_status = 0;
        $members = [];
        $member_indices = [3, 4, 17, 18, 19, 20, 21, 22, 23, 24];
        foreach ($member_indices as $idx) {
            // 只记录特定用户ID的日志
            $id = $row_room[$idx];
            if ($id === null) {
                $members[] = ['id' => null, 'name' => null, 'score' => 0];
            } else {
                // 查询用户名称
                $sql = "SELECT name FROM tb_user WHERE id = $id";
                $ret = mysqli_query($conn, $sql);
                $name_row = mysqli_fetch_row($ret);
                $name = $name_row ? $name_row[0] : null;
                $members[] = ['id' => $id, 'name' => $name, 'score' => 0, 'role' => 0];
            }
        }

        // 重构后的房间结构，优先使用members数组存储用户信息
        $_SESSION['room'] = [
            'name' => $row_room[1],
            'id' => $row_room[0],
            'user_cnt' => $row_room[2],
            'type' => $row_room[5],
            'word_type' => $row_room[6],
            'level_type' => $row_room[7],
            'max_people' => $row_room[8],
            'least_people' => $row_room[9],
            'personal_try' => $row_room[10],
            'team_try' => $row_room[11],
            'match_random' => $row_room[12],
            'vocab_custom_count' => $row_room[13],
            'vocab_custom' => $row_room[14],
            'winner' => $row_room[15],
            'word_id' => $row_room[16],
            'members' => $members,
            'status' => $row_room[25],
            'round' => $row_room[26],
        ];
        if (shouldLog($user_id)) {
            Logger::info("用户在空房间中", ["user_id" => $user_id, "room_id" => $row_room[0], "room_name" => $row_room[1], "room_user_ids" => [$row_room[3], $row_room[4]]]);
        }
        echo json_encode($jret, JSON_UNESCAPED_UNICODE); //用户在空房间中
    }
    else
    {
        $jret = new RetClass();
        $jret->ret_code = 1;
        $jret->username = $username;
        $jret->user_id = $user_id;
        $jret->room = $row_room[1];
        $jret->in_room = false;
        $jret->room_status = 0;
        if (shouldLog($user_id)) {

            Logger::info("意料之外的错误", ["user_id" => $user_id, "room_id" => $row_room[0], "room_name" => $row_room[1], "room_user_ids" => [$row_room[3], $row_room[4]]]);
        }
        echo json_encode($jret, JSON_UNESCAPED_UNICODE); //用户在空房间中
    }
} else {
    // 只记录特定用户ID的日志
    // if (shouldLog($user_id)) {
    //     Logger::info("用户不在房间中", ["user_id" => $user_id]);
    // }
    // // 更新用户为不在房间状态
    // if (mysqli_query($conn, "UPDATE tb_user SET in_room=0 WHERE id=$user_id")) {
    //     // 只记录特定用户ID的日志
    //     if (shouldLog($user_id)) {
    //         Logger::info("更新用户房间状态成功", ["user_id" => $user_id, "in_room" => 0]);
    //     }
    // } else {
    //     // 只记录特定用户ID的日志
    //     if (shouldLog($user_id)) {
    //         Logger::error("更新用户房间状态失败", ["user_id" => $user_id, "error" => mysqli_error($conn)]);
    //     }
    // }
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

        if ($cnt <= 0) { // 没有空房间，创建新房间
            
            $jret = new RetClass();
            $jret->ret_code = 1;
            $jret->room = $row[1];
            $jret->username = $username;
            $jret->user_id = $user_id;
            $jret->in_room = true;
            $jret->room_status = 1;
            
            echo json_encode($jret, JSON_UNESCAPED_UNICODE);
            Logger::debug("没有空房间", ["user_id" => $user_id]);
            // 步骤1: 插入基础数据
            $conn->beginTransaction();
            $sql1 = "INSERT INTO tb_room (user_cnt, user_id0) VALUES (1, :user_id)";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt1->execute();

            // 步骤2: 获取刚插入的ID并更新name字段
            $room_id = $pdo->lastInsertId();
            $sql2 = "UPDATE tb_room SET name = CONCAT('room', :room_id) WHERE id = :room_id";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->bindParam(':room_id', $room_id, PDO::PARAM_INT);
            $stmt2->execute();

            $conn->commit();
            
            echo json_encode($jret, JSON_UNESCAPED_UNICODE);
            Logger::debug("创建新空房间", ["user_id" => $user_id, "room_name" => $row[1], "room_id" => $row[0]]);
        
        } else {
            $row = mysqli_fetch_row($ret);
            if($row[2] <= 0)
            {
                $sql_update = "UPDATE tb_room SET user_cnt = 1, user_id0=$user_id WHERE id=" . $row[0];
                mysqli_query($conn, $sql_update);

                $jret = new RetClass();
                $jret->ret_code = 1;
                $jret->room = $row[1];
                $jret->username = $username;
                $jret->user_id = $user_id;
                $jret->in_room = true;
                $jret->room_status = 1;
                
                Logger::debug("加入无人房间", ["user_id" => $user_id, "room_name" => $row[1], "room_id" => $row[0]]);
                echo json_encode($jret, JSON_UNESCAPED_UNICODE);
            }
            else{
                if (shouldLog($user_id)) {
                   Logger::info("找到空房间", ["user_id" => $user_id, "room_name" => $row[1], "room_id" => $row[0]]);
               }
                $sql_update = "UPDATE tb_room SET user_cnt = user_cnt+1 WHERE id=" . $row[0];
                mysqli_query($conn, $sql_update);

                $jret = new RetClass();
                $jret->ret_code = 2;
                $jret->room = $row[1];
                $jret->username = $username;
                $jret->user_id = $user_id;
                $jret->in_room = true;
                $jret->room_status = 2;
                Logger::debug("准备加入房间", ["user_id" => $user_id, "room_name" => $row[1], "room_id" => $row[0], "room_user_ids" => [$row[3], $row[4]]]);
                if(is_null($row[3]))
                {
                    $sql_update = "UPDATE tb_room SET user_id0 = $user_id WHERE id=" . $row[0];
                    mysqli_query($conn, $sql_update);
                }
                else if(is_null($row[4]))
                {
                    $sql_update = "UPDATE tb_room SET user_id1 = $user_id WHERE id=" . $row[0];
                    mysqli_query($conn, $sql_update);
                }
                else
                {
                    Logger::error("房间已满", ["user_id" => $user_id, "room_name" => $row[1], "room_id" => $row[0]]);
                }
                // 初始化成员数组
                $members = [];
                $member_indices = [3, 4, 17, 18, 19, 20, 21, 22, 23, 24];
                foreach ($member_indices as $idx) {
                    $id = $row[$idx];
                    if ($id === null) {
                        $members[] = ['id' => null, 'name' => null, 'score' => 0];
                    } else {
                        // 查询用户名称
                        $sql = "SELECT name FROM tb_user WHERE id = $id";
                        $ret = mysqli_query($conn, $sql);
                        $name_row = mysqli_fetch_row($ret);
                        $name = $name_row ? $name_row[0] : null;
                        $members[] = ['id' => $id, 'name' => $name, 'score' => 0, 'role' => 0];
                    }
                }

                // 重构后的房间结构，优先使用members数组存储用户信息
                $_SESSION['room'] = [
                    'name' => $row[1],
                    'id' => $row[0],
                    'user_cnt' => $row[2],
                    'type' => $row[5],
                    'word_type' => $row[6],
                    'level_type' => $row[7],
                    'max_people' => $row[8],
                    'least_people' => $row[9],
                    'personal_try' => $row[10],
                    'team_try' => $row[11],
                    'match_random' => $row[12],
                    'vocab_custom_count' => $row[13],
                    'vocab_custom' => $row[14],
                    'winner' => $row[15],
                    'word_id' => $row[16],
                    'members' => $members,
                    'status' => $row[25],
                    'round' => $row[26]
                ];
                Logger::debug("加入房间", ["user_id" => $user_id, "room_name" => $_SESSION['room']['name'], "room_id" => $_SESSION['room_id'], "room_user_names" => [$_SESSION['room']['members'][0]['name'], $_SESSION['room']['members'][1]['name']]]);
                echo json_encode($jret, JSON_UNESCAPED_UNICODE);
            }
            
        }
}

    mysqli_close($conn);
}
?>