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
    if (!$conn) {
        die('数据库连接失败: ' . mysqli_connect_error());
    }

    // 获取获胜者名称逻辑
    if (!isset($_SESSION['room']['winner_name'])) {
        // 获取room_id
        $room_id = isset($_SESSION['room']["id"]) ? $_SESSION['room']["id"] : 0;
        $room_name = isset($_SESSION['room']["name"]) ? $_SESSION['room']["name"] : '';
        
        if ($room_id > 0) {
            // 通过room_id查询winner_id
            $stmt = mysqli_prepare($conn, "SELECT winner_id FROM tb_room WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $room_id);
        } else if (!empty($room_name)) {
            // 通过room名称查询winner_id
            $stmt = mysqli_prepare($conn, "SELECT winner_id FROM tb_room WHERE name = ?");
            mysqli_stmt_bind_param($stmt, 's', $room_name);
        }
        
        if (isset($stmt)) {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $winner_id);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
            
            if (!empty($winner_id)) {
                // 保存winner_id到session
                $_SESSION['room']['winner'] = $winner_id;
                
                // 通过winner_id查询用户名
                $stmt_user = mysqli_prepare($conn, "SELECT username FROM tb_user WHERE id = ?");
                mysqli_stmt_bind_param($stmt_user, 'i', $winner_id);
                mysqli_stmt_execute($stmt_user);
                mysqli_stmt_bind_result($stmt_user, $winner_name);
                mysqli_stmt_fetch($stmt_user);
                mysqli_stmt_close($stmt_user);
                
                // 保存winner_name到session
                $_SESSION['room']['winner_name'] = $winner_name;
            }
        }
    }
    
    // 关闭数据库连接
    mysqli_close($conn);

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
    <title>Round End</title>
</head>
<body bgcolor="#1270F8" style="overflow:hidden;">

        <!-- <img src="./example8.png" style="left:0px;top:0px;z-index:-2;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;"> -->
        <!-- 四个角落的图片 -->
        <img src="./right1.svg"  style="position:absolute;left:2.5vw;top:-6.8vh;overflow:hidden;width:38%; z-index:1;filter: invert(100%);" /> <!-- topleft-->
        <img src="./room2.png"  style="position:absolute;left:91vw;top:3vh;width:8vw;z-index:1" /> <!-- topright -->
        <img src="./room4.png"  style="position:absolute;left:-8vw;bottom:-19vh;overflow:hidden;width:21vw;z-index:1;transform: rotate(268deg);" /> <!-- downleft -->
        <img src="./describe2.svg"  style="position:absolute;right:0vw;bottom:2vh;overflow:hidden; width:28vw;z-index:0;opacity: 1;" /> <!-- downright -->
        <img src="./right2.svg"  style="position:absolute;left:0vw;bottom:-8vh;overflow:hidden;height:100vh;z-index:0;opacity: 1;" /> 
        <img src="./avatarexample.png" style="position:absolute;left: 6.5vw;bottom: 17vh;overflow:hidden;height: 57vh;z-index:0;opacity: 1;"> <!-- 头像 -->

        <!-- <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);z-index:5;font-family: 'SourceHanSans-Heavy', sans-serif;font-size:44px;color:white;"> -->
        <div id="name" style="
            position:absolute;
            width:80vw;
            height:20vh;
            text-align:right;
            right: 80vw;
            top: 2.6vh;
            font-size: 5vw;
            font-family:'SourceHanSans-Heavy';
            font-weight:bold;
            color:white;
            z-index: 3;
            letter-spacing:-0.35vw;
            text-shadow: 0.3vw 0.3vh #76ACFB
        ">
            <?php echo isset($_SESSION['room']['winner_name']) ? $_SESSION['room']['winner_name'] : '获胜者'; ?>
        </div>
        <div id="word" style="
            position:absolute;
            width: 80vw;
            height:20vh;
            text-align: right;
            right: 23vw;
            top: 40vh;
            font-size: 10vw;
            font-family: 'JiangXiZuoHei';
            font-weight: bold;
            color: black;
            -webkit-text-stroke: 2vw #f5e33f; /* 黄色描边 */
            z-index:1;
        ">
            <?php echo isset($_SESSION['selected_word']) ? $_SESSION['selected_word'] : '词语'; ?>
        </div>
        <div id="word2" style="
            position:absolute;
            width: 80vw;
            height:20vh;
            text-align: right;
            right: 23vw;
            top: 40vh;
            font-size: 10vw;
            font-family: 'JiangXiZuoHei';
            font-weight: bold;
            color: black;
            z-index:4;
        ">
            <?php echo isset($_SESSION['selected_word']) ? $_SESSION['selected_word'] : '词语'; ?>
        </div>
        

    <script>
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth;
        timer1=setInterval(checkUserCount,1000);    
        <?php 
            $user_id = $_SESSION['user_id'];
            $username=$_SESSION['username'];
            $room = $_SESSION['room']['name'];
            // 确保room_id已设置
            $room_id = isset($_SESSION['room']["id"]) ? $_SESSION['room']["id"] : '';
            
            print("var user_id=$user_id;\n");
            print("var username='$username'; // 保留作为辅助显示\n");
            print("var room = '$room';\n");
            print("var room_id = '$room_id';\n");
        ?>
        function checkUserCount() {
            console.log("Checking user count");
            timeCur = timeObj.getTime();
            Count1 = Count1 + 1;
            
            if (Count1 >= 15) { // 显示15秒后跳转到休息页面
                Count1 = -1000;
                clearInterval(timer1);
                console.log("跳转至休息页面");
                
                // 跳转到rest.php，传递room和room_id参数
                window.location.href = 'rest.php?room=' + encodeURIComponent(room) + '&room_id=' + encodeURIComponent(room_id);
            }
        }
    </script>
</body>
</html>
