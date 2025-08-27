<?php
    // Start the session
    session_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 预加载关键图片，减少加载延迟 -->
    <link rel="preload" href="./loading.png" as="image" type="image/png">
    <link rel="preload" href="./example9.png" as="image" type="image/png">
    
    <style>
        /* 字体声明保持不变 */
        @font-face {
            font-family: 'JiangXiZuoHei';
            src: url('./title.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'SourceHanSans-Regular';
            src: url('./SourceHanSans-Regular.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }
    </style>
    <link rel="stylesheet" href="styles2.css">
    <title>Waiting</title>
</head>
<body bgcolor="#1270F8" style="overflow:hidden;" monica-id="ofpnmcalabcbjgholdjcjblkibolbppb" monica-version="7.9.7">

    <!-- 背景图 -->
    <!-- <img src="./example9.png" style="position:absolute; left:0; top:0; z-index:-2; filter:brightness(50%); width:100vw; height:100vh; object-fit:cover;"> -->
    
    <!-- 装饰图片 -->
    <img src="./room2.png" style="position:absolute; left:8.3vw; top:0.7vw; width:8vw; z-index:1;">
    <img src="./room4.png" style="position:absolute; left:86.8vw; bottom:72vh; width:11vw; z-index:1;">
    <img src="./picture5.png" style="position:absolute; right:27.2vw; bottom:80vh; width:6vw; z-index:0; opacity:1;">
    <img src="./astronaut.svg" style="position:absolute; left:-28.9vw; bottom:-29vh; height:115vh; z-index:0; opacity:1;">
    
    <!-- 进度条与图片容器（合并重复元素，避免冗余） -->
    <div class="progress-wrapper">
        <!-- 未走过的进度条（背景） -->
        <div class="progress-bg"></div>
        <!-- 走过的进度条（填充） -->
        <div class="progress-fill"></div>
        <!-- 随进度移动的图片（放在进度条容器内，避免层级冲突） -->
        <img src="./loading.png" class="loading-image" alt="加载中">
    </div>

    <style>
        /* 进度条容器（整合进度条和图片，减少层级） */
        .progress-wrapper {
            position: absolute;
            left: -15vw; /* 向左移动更多 */
            bottom: -3vh; /* 略微上移配合高度压缩 */
            height: 23vh; /* 高度减半 */
            width: 120vw; /* 进度条总长度（与图片移动范围一致） */
            overflow: hidden;
            z-index: 1; /* 合理层级，避免被遮挡 */
        }

        /* 未走过的进度条（背景色#AF8D6E） */
        .progress-bg {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 35%; /* 保持比例，因容器高度减半，实际高度也减半 */
            background-color: #AF8D6E;
            border-radius: 20px; /* 圆角矩形 */
        }

        /* 走过的进度条（填充色#F16739） */
        .progress-fill {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 35%; /* 保持比例，因容器高度减半，实际高度也减半 */
            width: 0; /* 初始进度0 */
            background-color: #F16739;
            border-radius: 20px;
            /* 进度动画：5秒从左到右，结束后保持状态 */
            animation: progressFill 10s linear forwards;
        }

        /* 随进度移动的图片 */
        .loading-image {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 100%;
            min-width: 50px;
            object-fit: contain;
            z-index: 2; /* 显示在进度条上方 */
            /* 优化动画：用transform代替left（减少重排，提升性能） */
            transform: translateX(0);
            overflow: hidden;
            animation: moveWithProgress 5s linear forwards;
        }

        /* 进度条填充动画 */
        @keyframes progressFill {
            0% { width: -5%; }
            100% { width: 100%; } /* 填满整个进度条 */
        }

        /* 图片移动动画（终点与进度条末端对齐） */
        @keyframes moveWithProgress {
            0% { transform: translateX(-18vw); }
            100% { transform: translateX(95vw); } /* 移动距离=进度条宽度，停在终点 */
        }
    </style>

    <!-- 文字区域 -->
    <div style="
        position:absolute;
        width:80vw;
        height:20vh;
        text-align:right;
        right:1vw;
        top:32vh;
        font-size:12vw;
        font-family:'SourceHanSans-Regular';
        font-weight:bold;
        color:white;
        z-index:1;
        /* letter-spacing:-0.35vw; */
    ">
        WAITING
    </div>

    <script>
        var timeObj = new Date();
        var startTimeInMs = timeObj.getTime();
        var Count1 = 0;
        var window_width = window.innerWidth;
        timer1 = setInterval(checkUserCount, 1000);    
        <?php 
            $username = $_SESSION['username'];
            $user_id = $_SESSION['user_id'];
            $room = $_SESSION['room'];
            // 确保在SESSION中存在room_id，优先从URL获取
            if (isset($_GET['room_id']) && !empty($_GET['room_id'])) {
                $_SESSION['room_id'] = $_GET['room_id'];
            }
            $room_id = isset($_SESSION['room_id']) ? $_SESSION['room_id'] : '';
            // 获取房间中的第一个用户信息
            $first_user_id = '';
            $first_user_name = '';
            if (isset($_SESSION['room']['members']) && !empty($_SESSION['room']['members'])) {
                $first_user_id = $_SESSION['room']['members'][0]['id'];
                $first_user_name = $_SESSION['room']['members'][0]['name'];
            }
            print("var user_id=$user_id;\n");
            print("var username='$username'; // 保留作为辅助显示\n");
            print("var room = '$room';\n");
            // 确保在JavaScript变量中包含room_id
            print("var room_id = '$room_id';\n");
            print("var first_user_id = '$first_user_id';\n");
            print("var first_user_name = '$first_user_name';\n");
        ?>
        function checkUserCount() {
            console.log("Checking user count");
            timeCur = timeObj.getTime();
            Count1 = Count1 + 1;
            
            if (Count1 >= 5) {
                // 5秒后自动跳转到guess.php
                Count1 = -1000;
                clearInterval(timer1);
                console.log("Time up! Redirecting to guess.php");
                
                // 跳转到guess.php页面，同时传递room和room_id
                const url = new URL('guess.php', window.location.origin);
                url.searchParams.append('room', room);
                url.searchParams.append('room_id', room_id);
                window.location.href = url.toString();
            }
        }
    </script>
</body>
</html>