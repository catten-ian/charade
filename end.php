<?php
    // Start the session
    session_start();
    
    // 包含数据库配置
    include '../config.inc';
    
    // 连接数据库
    $conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
    if (mysqli_connect_errno()) {
        echo '数据库连接失败: ' . mysqli_connect_error();
        exit();
    }
    
    // 设置字符集
    mysqli_set_charset($conn, 'utf8');
    
    // 接收来自游戏页面的表单数据
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $user_id = $_POST['user_id'];
        $room = $_POST['room'];
        $rival = $_POST['rival'];
        $rival_id = $_POST['rival_id'];
        $first_user_id = $_POST['first_user_id'];
        
        // 保存会话变量
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['room'] = $room;
        $_SESSION['rival'] = $rival;
        $_SESSION['rival_id'] = $rival_id;
        $_SESSION['first_user_id'] = $first_user_id;
    } else {
        // 如果不是POST请求，从会话中获取数据
        $username = $_SESSION['username'];
        $user_id = $_SESSION['user_id'];
        $room = $_SESSION['room'];
        $rival = $_SESSION['rival'];
        $rival_id = $_SESSION['rival_id'];
        $first_user_id = $_SESSION['first_user_id'];
    }
    
    // 获取用户分数
    $stmt = mysqli_prepare($conn, "SELECT score FROM tb_user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_score = 0;
    if ($row = mysqli_fetch_assoc($result)) {
        $user_score = $row['score'];
    }
    
    // 获取对手分数
    $stmt = mysqli_prepare($conn, "SELECT score FROM tb_user WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $rival_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rival_score = 0;
    if ($row = mysqli_fetch_assoc($result)) {
        $rival_score = $row['score'];
    }
    
    // 关闭数据库连接
    mysqli_close($conn);
    
    // 重置用户状态（可选）
    // 这里可以根据需求决定是否重置用户状态
    // 例如：$_SESSION['type'] = 1; // 设置为在线状态
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* 正确的字体声明方式 - 放在样式表顶层 */
        @font-face {
            font-family: 'JiangXiZuoHei';
            src: url('./title.ttf') format('truetype');
            /* 可选：添加其他格式以提高兼容性 */
            /* src: url('./title.woff') format('woff'),
                 url('./title.eot') format('embedded-opentype'); */
            font-weight: normal;
            font-style: normal;
            /* 确保字体加载失败时有备选方案 */
            font-display: swap;
        }

        @font-face {
            font-family: 'SourceHanSans-Heavy';
            src: url('./SourceHanSans-Heavy.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'SourceHanSans-Normal';
            src: url('./SourceHanSans-Normal.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'SourceHanSans-Medium';
            src: url('./SourceHanSans-Medium.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            margin: 0;
            padding: 0;
            /* 可以在这里设置默认字体 */
            font-family: sans-serif;
        }
    </style>
    <title>Game End</title>
</head>
<body bgcolor="#1270F8" style="overflow:hidden;">

        <!-- <img src="./example10.png" style="left:0px;top:0px;z-index:-2;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;"> -->
        <!-- 四个角落的图片 -->
        <img src="./end1.svg"  style="position:absolute;left:2.5vw;top:-6.8vh;overflow:hidden;width:38%; z-index:1;filter: invert(100%);" /> <!-- topleft-->
        <img src="./room2.png"  style="position:absolute;left:91vw;top:3vh;width:8vw;z-index:1" /> <!-- topright -->
        <img src="./room4.png"  style="position:absolute;left:-8vw;bottom:-19vh;overflow:hidden;width:21vw;z-index:1;transform: rotate(268deg);" /> <!-- downleft -->
        <img src="./describe2.svg"  style="position:absolute;right:0vw;bottom:2vh;overflow:hidden; width:28vw;z-index:0;opacity: 1;" /> <!-- downright -->
        <img src="./end2.svg" style="position:absolute;left:0vw;bottom:0vh;overflow:hidden;height:100%;z-index:0;opacity: 1;">
        <img src="./end3.svg" style="position:absolute;left:0vw;bottom:0vh;overflow:hidden;height:100%;z-index:0;opacity: 1;">
        <!-- 将原end4.svg的img标签修改为以下内容 -->
        <img 
            src="./end4.svg" 
            style="position:absolute;left: 9.1vw;bottom: 19vh;overflow:hidden;height: 29.5vh;z-index:0;opacity: 1;cursor: pointer;" 
            onmousedown="this.src = './end5.svg'"
            onmouseup="this.src = './end4.svg'; handleClick()"
            onmouseleave="this.src = './end4.svg'" 
            ontouchstart="this.src = './end5.svg'" 
            ontouchend="this.src = './end4.svg'; handleClick()" 
        >

        <script>
            // 其他原有脚本...
            
            // 点击事件处理函数
            function handleClick() {
                console.log('返回首页按钮被点击了');
                // 跳转到首页或游戏开始页面
                window.location.href = 'index.php';
            }
        </script>
        <img src="./end6.svg" style="position:absolute;left: 10.2vw;bottom: 57vh;overflow:hidden;height: 9.3vh;z-index:0;opacity: 1;">
        <img src="./avatarexample.png" style="position:absolute;left: 45.5vw;bottom: 24vh;overflow:hidden;height: 66vh;z-index:0;opacity: 1;">

        <!-- <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);z-index:5;font-family: 'SourceHanSans-Heavy', sans-serif;font-size:44px;color:white;"> -->
        <div style="
            position:absolute;
            width:80vw;
            height: 20vh;
            text-align:right;
            right: 22.8vw;
            top: 73.6vh;
            font-size: 5.5vw;
            font-family:'SourceHanSans-Normal';
            font-weight:normal;
            color:white;
            z-index: 3;
            /* letter-spacing:-0.35vw; */
            text-shadow: 0.3vw 0.3vh #76ACFB
        ">
            <?php echo $user_score; ?>
        </div>
        <div style="
            position:absolute;
            width: 80vw;
            height:20vh;
            text-align: right;
            right: 74vw;
            top: 24vh;
            font-size: 4.9vw;
            font-family: 'SourceHanSans-Normal';
            font-weight: normal;
            color: white;
            z-index:1;
            letter-spacing: -0.35vw;
        ">
            <?php echo $rival; ?>
        </div>
        <div style="
            position:absolute;
            width: 80vw;
            height:20vh;
            text-align: left;
            left: 10vw;
            top: 40.3vh;
            font-size: 2.5vw;
            font-family: 'SourceHanSans-Normal';
            font-weight: normal;
            color: white;
            z-index:1;
            letter-spacing: -0.35vw;
            line-height: 2;
        ">
            共猜对了<?php echo $user_score; ?>个词语<br>对手猜对了<?php echo $rival_score; ?>个词语
        </div>
        

    <script>
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth;
        timer1=setInterval(checkUserCount,1000);    
        <?php 
            $username=$_SESSION['username'];
            $user_id = $_SESSION['user_id'];
            $room = $_SESSION['room'];
            $rival = $_SESSION['rival'];
            $rival_id = $_SESSION['rival_id'];
            $first_user_id = $_SESSION['first_user_id'];
            print("var username='$username';\n");
            print("var user_id=$user_id;\n");
            print("var room = '$room';\n");
            print("var rival = '$rival';\n");
            print("var rival_id = $rival_id;\n");
            print("var first_user_id = $first_user_id;\n");
        ?>
        function checkUserCount() {
            console.log("Checking user count");
            timeCur = timeObj.getTime();
            Count1 = Count1 + 1;
            
            // 这里可以添加逻辑来自动返回首页或执行其他操作
            // 例如，10秒后自动返回首页
            if (Count1 >= 10) {
                Count1 = -1000;
                clearInterval(timer1);
                console.log("自动返回首页");
                window.location.href = 'index.php';
            }
        }
    </script>
</body>
</html>
