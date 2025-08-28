<?php
    // Start the session
    session_start();
    
    // 包含数据库配置
    include '../config.inc';
    
    // 包含日志功能
    include 'log.php';
        
    // 定义应该记录日志的用户ID列表
    $log_user_ids = [8, 14];
    
    // 检查是否应该记录日志
    function shouldLog() {
        global $log_user_ids, $_SESSION;
        return isset($_SESSION['user_id']) && in_array($_SESSION['user_id'], $log_user_ids);
    }
    
    // 连接数据库
    $conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
    
    // 检查连接
    if ($conn->connect_error) {
        die("连接失败: " . $conn->connect_error);
    }
    
    // 从SESSION获取room_id
    $room_id = isset($_SESSION['room_id']) ? (int)$_SESSION['room_id'] : 0;
    
    // 从数据库获取当前轮数
    $current_round = 0;
    if ($room_id > 0) {
        $sql = "SELECT round FROM tb_room WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $stmt->bind_result($current_round);
        $stmt->fetch();
        $stmt->close();
    }
    
    // 关闭数据库连接
    $conn->close();
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
    <title>Rest Time</title>
</head>
<body bgcolor="#1270F8" style="overflow:hidden;" monica-id="ofpnmcalabcbjgholdjcjblkibolbppb" monica-version="7.9.7">

    <!-- 背景图 -->
    <!-- <img src="./example9.png" style="position:absolute; left:0; top:0; z-index:-2; filter:brightness(50%); width:100vw; height:100vh; object-fit:cover;"> -->
    
    <!-- 装饰图片 -->
    <img src="./room2.png" style="position:absolute; left:8.3vw; top:0.7vw; width:8vw; z-index:1;">
    <img src="./room4.png" style="position:absolute; left:86.8vw; bottom:72vh; width:11vw; z-index:1;">
    <img src="./star.png" style="position:absolute; right:27.2vw; bottom:80vh; width:6vw; z-index:0; opacity:1;">
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
            animation: progressFill 5s linear forwards;
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
        REST TIME
    </div>

    <script>
        var timeObj = new Date();
        var startTimeInMs = timeObj.getTime();
        var Count1 = 0;
        var window_width = window.innerWidth;
        timer1 = setInterval(checkUserCount, 1000);    
        <?php 
            // 从SESSION中获取数据
            $user_id = $_SESSION['user_id'];
            $username = $_SESSION['username'];
            $room = isset($_SESSION['room']) ? $_SESSION['room'] : '';
            $room_id = isset($_SESSION['room_id']) ? (int)$_SESSION['room_id'] : 0;
            $role = $_SESSION['role'];
            
            // 从PHP传递当前轮数到JavaScript
            print("var current_round = $current_round;\n");
            
            // 优先声明user_id，username保留作为辅助显示
            print("var user_id=$user_id;\n");
            print("var username='$username'; // 保留作为辅助显示\n");
            print("var room = '$room';\n");
            print("var room_id = $room_id;\n");
            print("var role = '$role';\n");
            // 用于角色计算的变量
            print("var isDescriber = (role == 'describer');\n");
        ?>
        function checkUserCount() {
            console.log("Checking user count");
            timeCur = timeObj.getTime();
            Count1 = Count1 + 1;
            
            if (Count1 >= 5) { // 显示5秒后跳转到下一轮游戏
                Count1 = -1000;
                clearInterval(timer1);
                console.log("跳转至下一轮游戏");
                
                // 使用从数据库获取的轮次计数
                var roundCount = current_round;
                
                // 检查是否达到4轮
                if (roundCount >= 4) {
                    console.log("已完成4轮游戏，跳转到结束页面");
                    const endUrlParams = new URLSearchParams();
                    endUrlParams.append('room', room);
                    endUrlParams.append('room_id', room_id);
                    window.location.href = 'end.php?' + endUrlParams.toString();
                    return;
                }
                
                // 不在这里更新轮次计数，由C++程序处理
                
                // 角色交换逻辑：根据用户ID和first_user_id决定当前角色
                var isDescriber = (role == 'describer');
                var targetPage = '';
                
                // 如果是偶数轮（从1开始计数），角色保持不变；如果是奇数轮，角色交换
                if (roundCount % 2 == 0) {
                    // 偶数轮，角色交换
                    targetPage = isDescriber ? 'waiting.php' : 'choose.php';
                } else {
                    // 奇数轮，角色保持不变
                    targetPage = isDescriber ? 'choose.php' : 'waiting.php';
                }
                
                console.log("轮次: " + roundCount + "，目标页面: " + targetPage);
                
                // 计算新角色
                var newRole = '';
                if (roundCount % 2 == 0) {
                    // 偶数轮，角色交换
                    newRole = isDescriber ? 'guesser' : 'describer';
                } else {
                    // 奇数轮，角色保持不变
                    newRole = isDescriber ? 'describer' : 'guesser';
                }
                
                // 不在这里保存轮次计数，由C++程序处理
                localStorage.setItem('currentRole', newRole);
                
                // 将新角色保存到tb_user表中
                console.log("保存新角色到数据库: " + newRole);
                var roleXhr = new XMLHttpRequest();
                roleXhr.open('GET', 'update_user_role.php?user_id=' + user_id + '&new_role=' + newRole, false); // 同步请求
                roleXhr.send();
                
                // 可以根据需要处理响应
                if (roleXhr.status === 200) {
                    console.log("角色更新成功: " + roleXhr.responseText);
                } else {
                    console.error("角色更新失败");
                }
                
                // 如果未完成4轮游戏，调用C++程序重置房间状态和增加轮数
                if (roundCount < 4) {
                    console.log("调用C++程序重置房间状态和增加轮数");
                    
                    // 发送AJAX请求调用C++程序
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', 'reset_room.php?room_id=' + room_id, false); // 同步请求，确保在跳转前完成
                    xhr.send();
                    
                    // 可以根据需要处理响应
                    if (xhr.status === 200) {
                        console.log("C++程序调用成功: " + xhr.responseText);
                    } else {
                        console.error("C++程序调用失败");
                    }
                }
                
                // 跳转到目标页面
                window.location.href = targetPage;
            }
        }
    </script>
</body>
</html>