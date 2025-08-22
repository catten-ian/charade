<?php
// /charade/cron/update_user_status.php
// 引用数据库配置（上层目录）
include "../config.inc";
// 引用应用配置（本层目录）
include "config.app.inc";

$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
mysqli_set_charset($conn, "utf8");

// 使用应用配置中的时间阈值
mysqli_query($conn, "UPDATE tb_user SET type = 5 
    WHERE TIMESTAMPDIFF(SECOND, last_active_time, NOW()) > {$status_threshold['type5_seconds']} 
    AND type != 2");

mysqli_query($conn, "UPDATE tb_user SET type = 2 
    WHERE TIMESTAMPDIFF(SECOND, last_active_time, NOW()) > {$status_threshold['type2_seconds']}");

mysqli_close($conn);
?>