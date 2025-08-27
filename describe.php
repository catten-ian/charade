<?php
    // Start the session
    session_start();
    
    // 引入日志文件
    include "log.php";
    
    // 定义应该记录日志的用户ID列表
    $log_user_ids = [8, 14];
    
    // 检查用户是否应该记录日志的函数
    function shouldLog($user_id) {
        global $log_user_ids;
        return in_array($user_id, $log_user_ids);
    }
    
    // 从SESSION中获取数据 - 优先使用user_id作为主要标识
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $room = $_SESSION['room']['name'];
    $room_id = isset($_SESSION['room_id']) ? (int)$_SESSION['room_id'] : 0;
    $role = $_SESSION['role'];
    
    // 记录用户进入页面的日志
    if (shouldLog($user_id)) {
        Logger::info('用户进入描述页面', [
            'user_id' => $user_id,
            'username' => $username,
            'room' => $room,
            'room_id' => $room_id
        ]);
    }
    
    // 直接从SESSION中获取selected_word
    $selected_word = isset($_SESSION['selected_word']) ? $_SESSION['selected_word'] : '';
    
    // 记录获取到的单词信息
    if (shouldLog($user_id) && !empty($selected_word)) {
        Logger::info('从SESSION获取到选中的单词', [
            'user_id' => $user_id,
            'selected_word' => $selected_word
        ]);
    }
    
    // 如果SESSION中没有selected_word，尝试从数据库中获取
    if (empty($selected_word)) {
        include '../config.inc';
        $conn = mysqli_connect('localhost', $db_user, $db_password, $db_name, $db_port);
        if (!mysqli_connect_errno()) {
            mysqli_set_charset($conn, 'utf8');
            
            // 优先使用room_id进行查询
            if ($room_id > 0) {
                $stmt = mysqli_prepare($conn, "SELECT current_word FROM tb_room WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $room_id);
            } else {
                $stmt = mysqli_prepare($conn, "SELECT current_word FROM tb_room WHERE name = ?");
                mysqli_stmt_bind_param($stmt, 's', $room);
            }
            
            if (isset($stmt)) {
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $selected_word = $row['current_word'];
                    // 保存到SESSION中
                    $_SESSION['selected_word'] = $selected_word;
                    
                    // 记录从数据库获取并保存到SESSION的单词
                    if (shouldLog($user_id)) {
                        Logger::info('从数据库获取单词并保存到SESSION', [
                            'user_id' => $user_id,
                            'selected_word' => $selected_word,
                            'room_id' => $room_id,
                            'room' => $room
                        ]);
                    }
                }
            }
            
            mysqli_close($conn);
        }
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

    <!-- <img src="./example5.png" style="left:0px;top:0px;z-index:-2;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;"> -->
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
            const spacing = totalSpacing / (text.length + 1);

            // 计算字符串的起始位置（左边缘）
            const startX = centerX - width / 2;

            // 依次绘制每个字符
            let currentX = startX+spacing;
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
        var user_id = <?php echo $user_id; ?>;
        var username = '<?php echo $username; ?>'; // 保留作为辅助显示
        var room = '<?php echo $room; ?>';
        var room_id = <?php echo $room_id; ?>;
        var selected_word = '<?php echo $selected_word; ?>';
        
        // 是否应该记录日志（从PHP变量传递）
        var shouldLog = <?php echo shouldLog($user_id) ? 'true' : 'false'; ?>;
        
        // 记录页面加载完成和游戏变量初始化
        if (shouldLog) {
            console.log('[LOG] 描述页面加载完成，游戏变量初始化', {
                user_id: user_id,
                username: username,
                room: room,
                room_id: room_id,
                selected_word: selected_word
            });
        }
        
        // 定时检查
        timer1 = setInterval(checkUserCount, 1000);
        
        // 检查猜测者状态
        function checkGuesserStatus() {
            if (shouldLog) {
                console.log('[LOG] 检查猜测者状态');
            }
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_guesser_status.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        // 这里可以根据实际情况处理猜测者状态
                        if (shouldLog) {
                            console.log('[LOG] 猜测者状态:', response);
                             // 记录猜测者状态到服务器
                            var logXhr = new XMLHttpRequest();
                            logXhr.open('POST', 'log_ajax.php', true);
                            logXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            logXhr.send('action=check_guesser_status&user_id=' + user_id + '&room_id=' + room_id + '&room=' + encodeURIComponent(room) + '&status=' + encodeURIComponent(JSON.stringify(response)));
                        }
                    } catch (e) {
                        if (shouldLog) {
                            console.error('[LOG] 解析响应时出错:', e);
                            // 记录错误到服务器
                            var logXhr = new XMLHttpRequest();
                            logXhr.open('POST', 'log_ajax.php', true);
                            logXhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            logXhr.send('action=check_guesser_status_error&user_id=' + user_id + '&room_id=' + room_id + '&error=' + encodeURIComponent(e.message));
                        }
                    }
                }
            };
            xhr.send();
        }
        
        // 每2秒检查一次猜测者状态
        setInterval(checkGuesserStatus, 2000);

        // 绘制Word
        const canvas = document.getElementById('myCanvas');
        const ctx = canvas.getContext('2d');
        setCanvasResolution();
        ctx.font = '280px "SourceHanSans-Heavy"';
        ctx.fillStyle = 'white';
        distributeString(ctx, selected_word, canvas.width / 2, canvas.height / 2, canvas.width); // 显示选择的词语
        
        // 记录单词绘制完成
        if (shouldLog) {
            console.log('[LOG] 单词绘制完成', { selected_word: selected_word });
            // 记录到服务器
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'log_ajax.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('action=word_displayed&user_id=' + user_id + '&selected_word=' + encodeURIComponent(selected_word));
        }
        
        function checkUserCount() {
            if (shouldLog) {
                console.log('[LOG] 检查用户计数，当前计数:', Count1);
            }
            timeCur = timeObj.getTime();
            Count1 = Count1 + 1;
            
            // 每30秒记录一次用户活动
            if (shouldLog && Count1 % 30 === 0) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'log_ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('action=user_activity&user_id=' + user_id + '&room_id=' + room_id + '&elapsed_seconds=' + Count1);
            }
            
            // 3分钟后自动跳转（如果还没有结果）
            if (Count1 >= 180) { // 3分钟 = 180秒
                Count1 = -1000;
                clearInterval(timer1);
                console.log("3分钟时间到，游戏结束");
                
                // 记录游戏超时的日志
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'log_ajax.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('action=game_timeout&user_id=' + user_id + '&room_id=' + room_id + '&room=' + encodeURIComponent(room));
                
                // 直接跳转到wrong.php，利用SESSION中存储的信息
                window.location.href = 'wrong.php';
            }
        }
    </script>
</html>
    