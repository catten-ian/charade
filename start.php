<?php
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
        
        <?php 
            // 从POST请求获取角色信息
            if (isset($_POST['role'])) {
                $_SESSION['role'] = $_POST['role'];
            }
            
            $username=$_SESSION['username'];
            $user_id = $_SESSION['user_id'];
            $room = $_SESSION['room'];
            $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
            
            print("var user_id=$user_id;\n");
            print("var username='$username'; // 保留作为辅助显示\n");
            print("var room = '$room';\n");
            print("var role = '$role';\n");
        ?>
        
        // 页面加载时将用户状态更新为4
        function updateUserStatusTo4() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/charade/heartbeat.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(`user_id=${encodeURIComponent(user_id)}&username=${encodeURIComponent(username)}&is_online=1&is_active=1&page_type=start`);
        }
        
        // 页面关闭时将用户状态更新为5
        window.addEventListener('beforeunload', function() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/charade/heartbeat.php', false); // 同步请求
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(`user_id=${encodeURIComponent(user_id)}&username=${encodeURIComponent(username)}&is_online=0&page_type=start`);
        });
        
        function checkUserCount()
        {
            timeCur=timeObj.getTime();  
            Count1=Count1+1;            
            if(Count1>=5)
            {
                /* stop timer */
                Count1=-1000;
                clearInterval(timer1);                
                console.log("time up!");
                
                // 根据角色跳转
                if(role === 'describer')
                {
                    window.location.href="choose.php";
                }
                else
                {
                    window.location.href="waiting.php";
                }
            }   
        }
        
        // 启动计时器
        timer1=setInterval(checkUserCount,1000);
        
        // 页面加载时更新用户状态为4
        window.onload = updateUserStatusTo4;
    </script>
    <script src="./activity-detector.js"></script>
</body>
</html>
