<?php
    // 引入日志文件
    require_once 'log.php';
    
    // 关闭错误报告（生产环境）
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    
    // 定义需要记录日志的用户ID
    $log_user_ids = [8, 14];
    
    // 检查是否应该记录日志的函数
    function shouldLog($user_id) {
        global $log_user_ids;
        return in_array($user_id, $log_user_ids);
    }
    
    // Start the session
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charades!</title>
    <link rel="stylesheet" href="styles2.css">
    <style>
        /* 定义弧线动画关键帧 */
        @keyframes arcMove {
            0% {
                /* 起点：当前位置（不偏移） */
                transform: translate(0, 0);
            }
            40% {
                /* 中间点：向左下方偏移，略向上凸形成弧线 */
                transform: translate(-80vw, -10vh); /* 调整数值可改变弧线曲率 */
            }
            100% {
                /* 终点：左下角超出屏幕 */
                transform: translate(-190vw, 50vh); /* 向左下方足够偏移，确保超出屏幕 */
            }
        }
    </style>
</head>

<body bgcolor="#F5E33F" style="overflow:hidden;">
    <!-- <img src="./example11.png" style="left:0px;top:0px;z-index:-2;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;"> -->
    <!-- 四个角落的图片 -->
    <img src="./start2.svg"  style="position:absolute;left:2.5vw;top:-6.8vh;overflow:hidden;width:38%; z-index:1;" /> <!-- topleft-->
    <img src="./start4.svg" style="position:absolute;right: 0.5vw;top: -22.4vh;width: 56vw;overflow:hidden;z-index:1">
    <img src="./start3.png" style="position:absolute;left: -11vw;bottom: -17vh;overflow:hidden;width:21vw;z-index:1;/* transform: rotate(0deg); */"><!-- downleft -->
    <img src="./start5.svg"  style="position:absolute;right:0vw;bottom:0vh;overflow:hidden; width:28vw;z-index:1;opacity: 1;" /> <!-- downright -->
    <img src="./start1.svg" height="100%" style="position:fixed;z-index:-1;">
    <!-- 修改movie元素的style，添加动画 -->
    <div id="movie" style="position:absolute;left: -23vw;top: -232.5vh;overflow:hidden;width:500vw;height:500vh;z-index:1;opacity: 1;
        /* 动画属性 */
        animation: arcMove 1.5s forwards;animation-timing-function: cubic-bezier(0.3, 0.1, 0.7, 0.8);">
        <!-- 子元素改用百分比定位（相对父元素div#movie） -->
        <img src="./start7.png" style="position:absolute;
            left: 18.8%; /* 原94vw ÷ 父元素宽度500vw × 100% = 18.8% */
            top: 47.6%; /* 原238vh ÷ 父元素高度500vh × 100% = 47.6% */
            width: 9%; /* 原45vw ÷ 父元素宽度500vw × 100% = 9% */
            overflow:hidden;z-index: 1;opacity: 1;">
        
        <img src="./start6.svg" style="position:absolute;
            left: 0%; /* 原0vw ÷ 500vw = 0% */
            top: 0%; /* 原0vh ÷ 500vh = 0% */
            width: 96.2%; /* 原481vw ÷ 500vw × 100% = 96.2% */
            overflow:hidden;z-index:0;opacity: 1;">
    </div>

    <!-- <img id="movie1"src="./start6.svg" style="transform: translateX(0);transition: transform 0.1s linear 2s;"> -->

    <script>
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth; 
        
        // 从sessionStorage获取角色信息
        var roleFromStorage = sessionStorage.getItem('role');
        var roomIdFromStorage = sessionStorage.getItem('room_id');
        
        console.log('[LOG] 从sessionStorage读取的角色信息:', { roleFromStorage: roleFromStorage, roomIdFromStorage: roomIdFromStorage });
        // 如果从sessionStorage获取到角色信息，通过AJAX保存到服务器端SESSION
        if (roleFromStorage) {
            console.log('[LOG] 准备将sessionStorage中的角色信息保存到服务器');
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_room_id.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            // 准备请求参数
            let params = `role=${encodeURIComponent(roleFromStorage)}`;
            if (roomIdFromStorage) {
                params += `&room_id=${encodeURIComponent(roomIdFromStorage)}`;
            }
            
            console.log('[LOG] 发送保存角色信息的AJAX请求:', params);
            xhr.send(params);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        console.log('[LOG] 角色信息保存成功:', xhr.responseText);
                    } else {
                        console.log('[LOG] 角色信息保存失败，状态码:', xhr.status);
                    }
                }
            };
        }
        
        <?php 
            
            $username=$_SESSION['username'];
            $user_id = $_SESSION['user_id'];
            $room = $_SESSION['room']['name'];
            // 确保在SESSION中存在room_id
            $room_id = isset($_SESSION['room']['id']) ? $_SESSION['room']['id'] : '';
            $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
            
            // 记录日志 - 只记录特定用户ID
            if (shouldLog($user_id)) {
                Logger::info("用户进入start页面", ["user_id" => $user_id, "username" => $username, "room" => $room, "room_id" => $room_id, "role" => $role]);
            }
            
            // 记录POST数据（如果有）- 只记录特定用户ID
            if (shouldLog($user_id) && !empty($_POST)) {
                Logger::info("接收到POST数据", ["user_id" => $user_id, "post_data" => json_encode($_POST)]);
            }
            
            print("var user_id=$user_id;\n");
            print("var username='$username'; // 保留作为辅助显示\n");
            print("var room = '$room';\n");
            // 确保在JavaScript变量中包含room_id
            print("var room_id = '$room_id';\n");
            // 如果从PHP没有获取到角色，使用sessionStorage中的角色
            print("var role = '$role' || roleFromStorage || '';\n");
        ?>
        
        // 页面加载时将用户状态更新为4
        function updateUserStatusTo4() {
            console.log('[LOG] 页面加载，准备更新用户状态为4');
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/charade/heartbeat.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            const params = `user_id=${encodeURIComponent(user_id)}&username=${encodeURIComponent(username)}&is_online=1&is_active=1&page_type=start`;
            console.log('[LOG] 发送用户状态更新请求:', params);
            xhr.send(params);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        console.log('[LOG] 用户状态更新成功:', xhr.responseText);
                    } else {
                        console.log('[LOG] 用户状态更新失败，状态码:', xhr.status);
                    }
                }
            };
        }
        
        // 页面关闭时将用户状态更新为5
        // window.addEventListener('beforeunload', function() {
        //     console.log('[LOG] 页面即将关闭，准备更新用户状态为5');
        //     const xhr = new XMLHttpRequest();
        //     xhr.open('POST', '/charade/heartbeat.php', false); // 同步请求
        //     xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        //     const params = `user_id=${encodeURIComponent(user_id)}&username=${encodeURIComponent(username)}&is_online=0&page_type=start`;
        //     console.log('[LOG] 发送页面关闭时的用户状态更新请求:', params);
        //     xhr.send(params);
        // });
        
        function checkUserCount()
        {
            timeCur=timeObj.getTime();  
            Count1=Count1+1;            
            console.log('[LOG] 计时器检查，当前计数:', Count1);
            
            if(Count1>=5)
            {
                /* stop timer */
                Count1=-1000;
                clearInterval(timer1);                
                console.log('[LOG] 时间到！停止计时器');
                // 根据角色跳转，数据已通过SESSION传递
                console.log('[LOG] 当前用户角色:', role, '，准备跳转到对应页面');
                if(role === 'describer')
                {
                    console.log('[LOG] 描述者跳转到choose页面');
                    window.location.href = 'choose.php';
                }
                else
                {
                    console.log('[LOG] 猜测者跳转到waiting页面');
                    window.location.href = 'waiting.php';
                }
            }   
        }
        
        // 启动计时器
        console.log('[LOG] 启动页面计时器，间隔1秒');
        timer1=setInterval(checkUserCount,1000);
        
        // 页面加载时更新用户状态为4
        window.onload = updateUserStatusTo4;
        
        console.log('[LOG] start页面初始化完成，用户信息:', { user_id: user_id, username: username, room: room, room_id: room_id, role: role });
    </script>
    <script src="./activity-detector.js"></script>
</body>
</html>
