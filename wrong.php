<?php
    // Start the session
    session_start();



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
        
        body {
            margin: 0;
            padding: 0;
            /* 可以在这里设置默认字体 */
            font-family: sans-serif;
        }
    </style>
    <title>No One Wins</title>
</head>
<body bgcolor="#1270F8" style="overflow:hidden;">

        <!-- <img src="./example8.png" style="left:0px;top:0px;z-index:-2;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;"> -->
        <!-- 四个角落的图片 -->
        <img src="./wrong1.svg"  style="position:absolute;left:2.5vw;top:-6.8vh;overflow:hidden;width:38%; z-index:1;filter: invert(100%);" /> <!-- topleft-->
        <img src="./room2.png"  style="position:absolute;left:91vw;top:3vh;width:8vw;z-index:1" /> <!-- topright -->
        <img src="./room4.png"  style="position:absolute;left:-8vw;bottom:-19vh;overflow:hidden;width:21vw;z-index:1;transform: rotate(268deg);" /> <!-- downleft -->
        <img src="./describe2.svg"  style="position:absolute;right:0vw;bottom:2vh;overflow:hidden; width:28vw;z-index:0;opacity: 1;" /> <!-- downright -->
        <img src="./wrong2.svg"  style="position:absolute;left:0vw;bottom:-8vh;overflow:hidden;height:100vh;z-index:0;opacity: 1;" /> 

        <!-- <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);z-index:5;font-family: 'SourceHanSans-Heavy', sans-serif;font-size:44px;color:white;"> -->
        <div id="word" style="
            position:absolute;
            width: 80vw;
            height:20vh;
            text-align: right;
            right: 10vw;
            top: 32vh;
            font-size: 10vw;
            font-family: 'JiangXiZuoHei';
            font-weight: bold;
            color: black;
            -webkit-text-stroke: 2vw #f5e33f; /* 黄色描边 */
            z-index:1;
        ">
            圣诞鹿
        </div>
        <div id="word2" style="
            position:absolute;
            width: 80vw;
            height:20vh;
            text-align: right;
            right: 10vw;
            top: 32vh;
            font-size: 10vw;
            font-family: 'JiangXiZuoHei';
            font-weight: bold;
            color: black;
            z-index:4;
        ">
            圣诞鹿
        </div>
        

    <script>
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth;
        timer1=setInterval(checkUserCount,1000);    
        <?php 
            // 优先使用user_id作为主要标识，username保留作为辅助显示
    $user_id = $_SESSION['user_id'];
    $username=$_SESSION['username'];
    $room = $_SESSION['room'];
    $first_user_id = $_SESSION['first_user_id'];
    
    // 优先从房间成员列表中获取第一个用户信息
    $first_user_name = '';
    if (isset($_SESSION['room']['members']) && !empty($_SESSION['room']['members'])) {
        $first_user_id = $_SESSION['room']['members'][0]['id'];
        $first_user_name = $_SESSION['room']['members'][0]['name'];
    }
    
    // 优先声明user_id，username保留作为辅助显示
            print("var user_id=$user_id;\n");
            print("var username='$username'; // 保留作为辅助显示\n");
            print("var room = '$room';\n");
            print("var first_user_id = $first_user_id;\n");
        ?>
        function checkUserCount() {
            console.log("Checking user count");
            timeCur = timeObj.getTime();
            Count1 = Count1 + 1;
            
            if (Count1 >= 15) { // 显示15秒后跳转到休息页面
                Count1 = -1000;
                clearInterval(timer1);
                console.log("跳转至休息页面");
                
                // 直接跳转到rest.php，利用SESSION中存储的信息
                window.location.href = 'rest.php';
            }
        }
    </script>
</body>
</html>
