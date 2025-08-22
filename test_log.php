<?php
/**
 * 日志功能测试脚本
 */
include "log.php";

echo "开始测试日志功能...<br>";

// 测试不同级别的日志
Logger::debug("这是一条调试日志");
Logger::info("这是一条信息日志");
Logger::warning("这是一条警告日志");
Logger::error("这是一条错误日志");

// 测试带上下文的日志
Logger::info("用户登录", ["username" => "testuser", "ip" => "127.0.0.1"]);

// 测试自定义日志路径
Logger::setLogPath("./logs/test/");
Logger::info("这是一条写入到测试目录的日志");

// 测试自定义日志文件
Logger::setLogFile("test.log");
Logger::info("这是一条写入到测试文件的日志");

echo "日志测试完成，请查看logs目录下的日志文件。";
?>