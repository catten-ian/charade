<?php
/**
 * 日志工具类
 * 提供不同级别的日志记录功能
 */
class Logger {
    // 日志级别
    const DEBUG = 1;
    const INFO = 2;
    const WARNING = 3;
    const ERROR = 4;
    const FATAL = 5;

    // 日志文件路径
    private static $logPath = './logs/';
    private static $logFile = 'app.log';

    /**
     * 设置日志文件路径
     * @param string $path 日志文件路径
     */
    public static function setLogPath($path) {
        self::$logPath = rtrim($path, '/') . '/';
        // 确保目录存在
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
    }

    /**
     * 设置日志文件名
     * @param string $file 日志文件名
     */
    public static function setLogFile($file) {
        self::$logFile = $file;
    }

    /**
     * 记录调试信息
     * @param string $message 日志信息
     * @param array $context 上下文信息
     */
    public static function debug($message, $context = []) {
        self::log(self::DEBUG, 'DEBUG', $message, $context);
    }

    /**
     * 记录一般信息
     * @param string $message 日志信息
     * @param array $context 上下文信息
     */
    public static function info($message, $context = []) {
        self::log(self::INFO, 'INFO', $message, $context);
    }

    /**
     * 记录警告信息
     * @param string $message 日志信息
     * @param array $context 上下文信息
     */
    public static function warning($message, $context = []) {
        self::log(self::WARNING, 'WARNING', $message, $context);
    }

    /**
     * 记录错误信息
     * @param string $message 日志信息
     * @param array $context 上下文信息
     */
    public static function error($message, $context = []) {
        self::log(self::ERROR, 'ERROR', $message, $context);
    }

    /**
     * 记录致命错误
     * @param string $message 日志信息
     * @param array $context 上下文信息
     */
    public static function fatal($message, $context = []) {
        self::log(self::FATAL, 'FATAL', $message, $context);
        // 致命错误可以选择退出程序
        exit(1);
    }

    /**
     * 实际的日志记录方法
     * @param int $level 日志级别
     * @param string $levelName 级别名称
     * @param string $message 日志信息
     * @param array $context 上下文信息
     */
    private static function log($level, $levelName, $message, $context = []) {
        // 确保日志目录存在
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }

        // 构建日志消息
        $timestamp = date('Y-m-d H:i:s');
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous';

        // 处理上下文信息
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        // 构建完整日志行
        $logLine = "[$timestamp] [$levelName] [$ip] [$username] $message$contextStr\n";

        // 写入日志文件
        $logFilePath = self::$logPath . self::$logFile;
        file_put_contents($logFilePath, $logLine, FILE_APPEND);
    }
}
?>