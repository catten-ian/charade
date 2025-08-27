<?php
session_start();
echo "当前的 \$_SESSION 信息为：";
print_r($_SESSION);
$_SESSION['guess_count'] = 0;
?>
