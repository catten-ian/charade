<?php
// 获取room_id参数
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

if ($room_id > 0) {
    // 定义C++程序路径
    $cpp_program = 'E:\\software\\wampsever\\www\\charade\\reset_room.exe';
    
    // 检查程序文件是否存在
    if (file_exists($cpp_program)) {
        // 执行C++程序并传递room_id参数
        $command = "$cpp_program $room_id";
        
        // 使用proc_open执行命令，获取输出
        $descriptorspec = array(
            0 => array("pipe", "r"),  // 标准输入
            1 => array("pipe", "w"),  // 标准输出
            2 => array("pipe", "w")   // 标准错误
        );
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (is_resource($process)) {
            // 读取输出和错误
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            
            // 关闭管道
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            // 获取退出码
            $return_value = proc_close($process);
            
            if ($return_value === 0) {
                echo "成功重置房间状态和增加轮数: $output";
                http_response_code(200);
            } else {
                echo "C++程序执行失败: $error";
                http_response_code(500);
            }
        } else {
            echo "无法启动C++程序";
            http_response_code(500);
        }
    } else {
        echo "C++程序文件不存在";
        http_response_code(500);
    }
} else {
    echo "无效的room_id";
    http_response_code(400);
}
?>