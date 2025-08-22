<?php
    // Start the session
    session_start();
    
    // 接收来自choose.php的表单数据
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $user_id = $_POST['user_id'];
        $room = $_POST['room'];
        $rival = $_POST['rival'];
        $rival_id = $_POST['rival_id'];
        $first_user_id = $_POST['first_user_id'];
        $role = $_POST['role'];
        $selected_word = $_POST['selected_word'];
        
        // 保存会话变量
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['room'] = $room;
        $_SESSION['rival'] = $rival;
        $_SESSION['rival_id'] = $rival_id;
        $_SESSION['first_user_id'] = $first_user_id;
        $_SESSION['role'] = $role;
        $_SESSION['selected_word'] = $selected_word;
        
        // 这里可以将选择的词语保存到数据库中，供guess.php页面获取
        include '../config.inc';
        $conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
        if (mysqli_connect_errno()) {
            echo '数据库连接失败: ' . mysqli_connect_error();
            exit();
        }
        mysqli_set_charset($conn, 'utf8');
        
        // 更新房间表，设置当前回合的词语
        $stmt = mysqli_prepare($conn, "UPDATE tb_room SET current_word = ? WHERE name = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $selected_word, $room);
        mysqli_stmt_execute($stmt);
        
        // 获取所选词汇在tb_words表中的id并更新房间的word_id
        $stmt = mysqli_prepare($conn, "SELECT id FROM tb_words WHERE word = ?");
        mysqli_stmt_bind_param($stmt, 's', $selected_word);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $word_id = $row['id'];
            $stmt = mysqli_prepare($conn, "UPDATE tb_room SET word_id = ? WHERE name = ?");
            mysqli_stmt_bind_param($stmt, 'is', $word_id, $room);
            mysqli_stmt_execute($stmt);
        }
        
        mysqli_close($conn);
    } else {
        // 如果不是POST请求，从会话中获取数据
        $username = $_SESSION['username'];
        $user_id = $_SESSION['user_id'];
        $room = $_SESSION['room'];
        $rival = $_SESSION['rival'];
        $rival_id = $_SESSION['rival_id'];
        $first_user_id = $_SESSION['first_user_id'];
        $role = $_SESSION['role'];
        $selected_word = $_SESSION['selected_word'];
    }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @font-face {
            font-family: 'SourceHanSans-Heavy';
            src: url('./SourceHanSans-Heavy.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        .fixed-width {
            position: absolute;
            width: 300vw; /* 固定宽度 */
            height: 100vh; /* 固定高度 */
            border: 1px solid rgba(204, 204, 204, 0); /* 透明边框 */
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 10vw; /* 左右边距，可调节 */
            box-sizing: border-box;
            z-index: 5; /* 设置z-index为5 */
        }
        #text-container {
            white-space: nowrap; /* 强制文本在一行显示 */
            text-align: justify;
            text-align-last: justify;
            width: 100%;
            font-size: 2em; /* 增大字体大小 */
        }

        #text-container:after {
            content: '';
            display: inline-block;
            width: 100%;
        }
    </style>
    <title>Describe The Word</title>

    
</head>
<body bgcolor="#1270F8" style="overflow:hidden;">

    <img src="./example5.png" style="left:0px;top:0px;z-index:-2;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;">
    <!-- 四个角落的图片 -->
    <img src="./describe1.svg"  style="position:absolute;left:2.5vw;top:-6.8vh;overflow:hidden;width:38%; z-index:1;filter: invert(100%);" /> <!-- topleft-->
    <img src="./room2.png"  style="position:absolute;left:91vw;top:3vh;width:8vw;z-index:1" /> <!-- topright -->
        <img src="./room4.png"  style="position:absolute;left:-8vw;bottom:-19vh;overflow:hidden;width:21vw;z-index:1;transform: rotate(268deg);" /> <!-- downleft -->
    <img src="./describe2.svg"  style="position:absolute;right:0vw;bottom:2vh;overflow:hidden; width:28vw;z-index:0;opacity: 1;" /> <!-- downright -->
    <img src="./charades2.svg"  style="position:absolute;left:-26vw;bottom:-44vh;overflow:hidden; width:100vw;z-index:0;" />
    <img src="./charades2.svg"  style="position:absolute;right:-20vw;top:-42vh;overflow:hidden; width:100vw;z-index:0;" />
    <canvas id="myCanvas" style="position:absolute;width:80vw;height:60vh;left:50%;top:60%;transform: translate(-50%, -50%);z-index:5;"></canvas>

    <script>
        // 设置 Canvas 实际分辨率为显示尺寸的 2 倍（提高清晰度）
        function setCanvasResolution() {
            const dpr = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr); // 缩放上下文以匹配新的分辨率
        }

        function distributeString(ctx, text, centerX, centerY, width) {
            // 获取每个字符的宽度
            const charWidths = [];
            let totalCharWidth = 0;
            for (let i = 0; i < text.length; i++) {
                const charWidth = ctx.measureText(text[i]).width;
                charWidths.push(charWidth);
                totalCharWidth += charWidth;
            }

            // 计算总间距和每个间距的大小
            const totalSpacing = width - totalCharWidth;
            const spacing = totalSpacing / (text.length - 1);

            // 计算字符串的起始位置（左边缘）
            const startX = centerX - width / 2;

            // 依次绘制每个字符
            let currentX = startX;
            for (let i = 0; i < text.length; i++) {
                ctx.fillText(text[i], currentX, centerY);
                // 移动到下一个字符位置，加上当前字符宽度和间距
                currentX += charWidths[i] + spacing;
            }
        }

        // 游戏变量
        var timeObj = new Date();
        var startTimeInMs = timeObj.getTime();
        var Count1 = 0;
        var window_width = window.innerWidth;
        var username = '<?php echo $username; ?>';
        var user_id = <?php echo $user_id; ?>;
        var room = '<?php echo $room; ?>';
        var rival = '<?php echo $rival; ?>';
        var rival_id = <?php echo $rival_id; ?>;
        var first_user_id = <?php echo $first_user_id; ?>;
        var selected_word = '<?php echo $selected_word; ?>';
        
        // 定时检查
        timer1 = setInterval(checkUserCount, 1000);
        
        // 检查猜测者状态
        function checkGuesserStatus() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_guesser_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        // 这里可以根据实际情况处理猜测者状态
                        console.log('猜测者状态:', response);
                    } catch (e) {
                        console.error('解析响应时出错:', e);
                    }
                }
            };
            xhr.send('room=' + encodeURIComponent(room) + '&user_id=' + user_id);
        }
        
        // 每2秒检查一次猜测者状态
        setInterval(checkGuesserStatus, 2000);

        // 使用示例
        const canvas = document.getElementById('myCanvas');
        const ctx = canvas.getContext('2d');
        setCanvasResolution();
        ctx.font = '280px "SourceHanSans-Heavy"';
        ctx.fillStyle = 'white';
        distributeString(ctx, selected_word, canvas.width / 2, canvas.height / 2, canvas.width); // 显示选择的词语
        
        function checkUserCount() {
            console.log("Checking user count");
            timeCur = timeObj.getTime();
            Count1 = Count1 + 1;
            
            // 3分钟后自动跳转（如果还没有结果）
            if (Count1 >= 180) { // 3分钟 = 180秒
                Count1 = -1000;
                clearInterval(timer1);
                console.log("3分钟时间到，游戏结束");
                
                // 创建表单并提交到错误页面
                var form = document.createElement('form');
                form.method = 'post';
                form.action = 'wrong.php';
                
                // 添加表单字段
                var fields = [
                    {name: 'username', value: username},
                    {name: 'user_id', value: user_id},
                    {name: 'room', value: room},
                    {name: 'rival', value: rival},
                    {name: 'rival_id', value: rival_id},
                    {name: 'first_user_id', value: first_user_id},
                    {name: 'selected_word', value: selected_word}
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
        }
    </script>
</html>
    