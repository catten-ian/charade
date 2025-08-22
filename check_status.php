<?php
// 引用数据库配置
include "../config.inc";

// 连接数据库
$conn = mysqli_connect("localhost", $db_user, $db_password, $db_name, $db_port);
if (mysqli_connect_errno()) {
    echo "连接数据库失败: " . mysqli_connect_error();
    exit;
}

// 设置字符集
mysqli_set_charset($conn, "utf8");

// 查询最近更新的用户
$sql = "SELECT id, name, type, last_active_time FROM tb_user ORDER BY last_active_time DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "最近10个用户的状态:\n";
    echo "ID\t姓名\t类型\t最后活动时间\n";
    echo "----------------------------------------\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row["id"] . "\t" . $row["name"] . "\t" . $row["type"] . "\t" . $row["last_active_time"] . "\n";
    }
} else {
    echo "没有找到用户记录";
}

// 关闭连接
mysqli_close($conn);
?>\n