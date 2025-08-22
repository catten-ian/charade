<?php
// /charade/cron/config.app.inc
// 应用相关配置

// 状态更新时间阈值（秒）
$status_threshold = [
    'type5_seconds' => 180,   // 3分钟未响应设为type=5
    'type2_seconds' => 3600   // 60分钟未响应设为type=2
];

// 其他应用配置可在此继续添加
// $other_config = 'xxx';
?>