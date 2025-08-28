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
        // 优先使用user_id作为主要标识，username保留作为辅助显示
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $room = $_POST['room'];
        $rival_id = $_POST['rival_id'];
        $rival = $_POST['rival'];
        $first_user_id = $_POST['first_user_id'];
        
        // 保存会话变量 - 优先使用user_id作为主要标识
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username; // 保留作为辅助显示
        $_SESSION['room'] = $room;
        $_SESSION['rival_id'] = $rival_id;
        $_SESSION['rival'] = $rival; // 保留作为辅助显示
        $_SESSION['first_user_id'] = $first_user_id;
    } else {
        // 如果不是POST请求，从会话中获取数据，同时优先从URL获取room和room_id
        // 优先使用user_id作为主要标识，username保留作为辅助显示
        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'];
        // 优先从URL获取room和room_id，如果没有则使用SESSION中的值
        $room = isset($_GET['room']) ? $_GET['room'] : (isset($_SESSION['room']) ? $_SESSION['room'] : '');
        $room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : (isset($_SESSION['room_id']) ? (int)$_SESSION['room_id'] : 0);
        $rival_id = $_SESSION['rival_id'];
        $rival = $_SESSION['rival'];
        $first_user_id = $_SESSION['first_user_id'];
        
        // 更新SESSION中的room和room_id
        $_SESSION['room'] = $room;
        $_SESSION['room_id'] = $room_id;
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
    
    <!-- 引入心跳活动检测器 -->
    <script src="./activity-detector.js"></script>
</head>
<body bgcolor="#1270F8" style="overflow:hidden;">
        <script>
            // 从PHP获取变量值
            var user_id = <?php echo $user_id; ?>;
            var username = '<?php echo $username; ?>';
            var room = '<?php echo $room; ?>';
            var room_id = <?php echo $room_id; ?>;
            var rival_id = <?php echo $rival_id; ?>;
            var rival = '<?php echo $rival; ?>';
            var first_user_id = <?php echo $first_user_id; ?>;
        </script>

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
            id="againBtn" 
            src="./end4.svg" 
            style="position:absolute;left: 9.1vw;bottom: 19vh;overflow:hidden;height: 29.5vh;z-index:0;opacity: 1;cursor: pointer;" 
            onmousedown="this.src = './end5.svg'"
            onmouseup="this.src = './end4.svg'; handleClick()"
            onmouseleave="this.src = './end4.svg'" 
            ontouchstart="this.src = './end5.svg'" 
            ontouchend="this.src = './end4.svg'; handleClick()" 
        >

        <script>
            // 优先使用room_id作为本地存储的键名的一部分，避免房间名重复问题
            const CLICK_STATUS_KEY = 'game_again_clicked_' + (room_id ? room_id : room);
            
            // 标记用户是否已经点击
            let hasClicked = false;
            
            // 监听本地存储变化，用于检测对手是否点击
            window.addEventListener('storage', function(e) {
                if (e.key === CLICK_STATUS_KEY && e.newValue === 'clicked') {
                    console.log('检测到对手已点击，即将进入新游戏');
                    startNewGame();
                }
            });
            
            // 点击事件处理函数
            function handleClick() {
                console.log('再次游戏按钮被点击了');
                
                if (hasClicked) {
                    return; // 防止重复点击
                }
                
                hasClicked = true;
                
                // 在本地存储中标记为已点击
                localStorage.setItem(CLICK_STATUS_KEY, 'clicked');
                
                // 创建表单并提交到新游戏房间
                startNewGame();
            }
            
            // 开始新游戏
            function startNewGame() {
                // 重置轮次计数
                localStorage.removeItem('roundCount');
                
                // 创建表单并提交到房间页面
                var form = document.createElement('form');
                form.method = 'post';
                form.action = 'exampleroom2.php';
                
                // 添加表单字段 - 优先使用user_id作为主要标识
                var fields = [
                    {name: 'user_id', value: user_id},
                    {name: 'username', value: username},
                    {name: 'room', value: room},
                    {name: 'room_id', value: room_id},
                    {name: 'rival_id', value: rival_id},
                    {name: 'rival', value: rival},
                    {name: 'first_user_id', value: first_user_id},
                    {name: 'new_game', value: 'true'}
                ];
                
                // 创建并添加所有隐藏字段
                fields.forEach(function(field) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = field.name;
                    input.value = field.value;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
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
        
        // 检查是否已经有用户点击了再次游戏按钮
        if (localStorage.getItem(CLICK_STATUS_KEY) === 'clicked') {
            console.log('检测到已有玩家点击了再次游戏，开始倒计时');
        }
        
        function checkUserCount() {
            console.log("Checking user count");
            timeCur = timeObj.getTime();
            Count1 = Count1 + 1;
            
            // 检查是否已经点击过，并且等待了60秒对手仍未点击
            if (hasClicked && Count1 >= 60) {
                Count1 = -1000;
                clearInterval(timer1);
                console.log("对手60秒内未点击，自动进入新游戏");
                startNewGame();
            }
        }
    </script>
</body>
</html>
