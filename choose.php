<?php
    // 引入日志文件
    include "log.php";
    
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

    include "../config.inc";

    $conn=mysqli_connect("localhost",$db_user,$db_password,$db_name, $db_port);
    if(mysqli_connect_errno())
    {
        echo "connect db failed:".mysqli_connect_error();
    }

    $username=$_SESSION['username'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
    mysqli_set_charset($conn,"utf8");
    
    // 从SESSION中获取room和room_id
    $room = $_SESSION['room']['name'];
    $room_id = isset($_SESSION['room']['id']) ? (int)$_SESSION['room']['id'] : 0;
    
    // 记录日志 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("用户进入choose页面", ["user_id" => $user_id, "username" => $username, "room" => $room, "room_id" => $room_id]);
    }
    
    // 记录POST数据（如果有）- 只记录特定用户ID
    if (shouldLog($user_id) && !empty($_POST)) {
        Logger::info("接收到POST数据", ["user_id" => $user_id, "post_data" => json_encode($_POST)]);
    }
    
    $ret=mysqli_query($conn,'SELECT COUNT(*) as count FROM tb_words');
    $row=mysqli_fetch_array($ret);
    $count = $row[0];

    function getRandomInt($max) 
    {
        return rand(0, $max - 1);
    }

    // 生成4个不同的随机索引数组
    $indexes = [];
    while (count($indexes) < 4) {
        $randomIndex = getRandomInt($count);
        if (!in_array($randomIndex, $indexes)) {
            $indexes[] = $randomIndex;
        }
    }

    // 获取4个不同的随机单词数组
    $words = [];
    for ($i = 0; $i < 4; $i++) {
        $sql = "SELECT word FROM tb_words LIMIT $indexes[$i], 1";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($result);
        $words[] = $row[0];
    }

    mysqli_close($conn);
    
    // 记录选择的单词 - 只记录特定用户ID
    if (shouldLog($user_id)) {
        Logger::info("随机选择的单词", ["user_id" => $user_id, "words" => $words, "index" => $indexes]);
    }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @font-face {
            font-family: 'JiangXiZuoHei';
            src: url('./title.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'JiangXiZuoHei', sans-serif;
        }
        .timer-container {
            position: absolute;
            top: 5vh;
            left: 50%;
            transform: translateX(-50%);
            font-size: 5vw;
            color: white;
            font-family: 'JiangXiZuoHei', sans-serif;
            z-index: 10;
        }
        .selected-word {
            border: 3px solid yellow;
        }
        .dimmed {
            opacity: 0.35;
        }
    </style>
    <title>Choose A Word</title>
    <script src="./activity-detector.js"></script>
</head>
<body bgcolor="#1270F8" style="overflow:hidden;">
    <div class="timer-container">
        <span id="timer">10</span>秒
    </div>
    
    <form id="wordForm" action="describe.php" method="get">
    <!-- 通过SESSION传递数据，不再使用POST隐藏字段 -->
</form>

    <!-- <img src="./example4.png" style="left:0px;top:0px;z-index:-1;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;"> -->
    <!-- 四个角落的图片 -->
    <img src="./Choose3.svg"  style="position:absolute;left:62vw;top:0vh;overflow:hidden; width:38%;height:39%;z-index:0;opacity: 1;" /> <!-- topright -->
    <img src="./Choose4.svg"  style="position:absolute;left:0vw;top:56vh;overflow:hidden; width:46%;z-index:0;opacity: 1;" /> <!-- downleft -->
    <img src="./Choose2.svg"  style="position:absolute;left:2.5vw;top:-6.8vh;overflow:hidden;width:38%; z-index:0;filter: invert(100%);" /><!-- topleft-->
    <img src="./Choose1.svg"  style="position:absolute;left:71.8vw;top:70.8vh;width:28%;overflow:hidden;z-index:0;opacity: 1;" />  <!-- downright -->
    
    <div id="wordDiv1" onclick="selectWord('<?php echo $words[0]; ?>', 1)" style="position:absolute;left:23vw;top:25vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $words[0]; ?></p>
    </div>

    <div id="wordDiv2" onclick="selectWord('<?php echo $words[1]; ?>', 2)" style="position:absolute;left:56vw;top:25vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $words[1]; ?></p>
    </div>

    <div id="wordDiv3" onclick="selectWord('<?php echo $words[2]; ?>', 3)" style="position:absolute;left:23vw;top:54vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $words[2]; ?></p>
    </div>

    <div id="wordDiv4" onclick="selectWord('<?php echo $words[3]; ?>', 4)" style="position:absolute;left:56vw;top:54vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $words[3]; ?></p>
    </div>
    <script>
        // 全局变量 - 优先使用user_id作为主要标识
        var user_id = "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>";
        var username = "<?php echo $username; ?>"; // 保留作为辅助显示
        var timer = 10;
        var timerInterval;
        var selectedWord = "<?php echo $words[0]; ?>"; // 默认选择第一个单词
        var isSelectionComplete = false;
        var room = "<?php echo $room; ?>";
        // 确保在JavaScript变量中包含room_id
        var room_id = "<?php echo $room_id; ?>";
        // 检查是否应该记录日志的标志
        var shouldLog = <?php echo shouldLog($user_id) ? 'true' : 'false'; ?>;
        
        // 页面加载日志
        if (shouldLog) {
            console.log('[LOG] choose页面加载完成，用户信息:', { user_id: user_id, username: username, room: room, room_id: room_id });
        }
        
        // 开始倒计时
        function startTimer() {
            timerInterval = setInterval(function() {
                timer--;
                document.getElementById('timer').textContent = timer;
                
                if (timer <= 0) {
                    clearInterval(timerInterval);
                    finalizeSelection();
                }
            }, 1000);
        }
        
        // 选择单词的处理函数
        function selectWord(word, index) {
            if (isSelectionComplete) return;
            
            // 设置选中的单词
            selectedWord = word;
            for (var i = 1; i <= 4; i++) {
                if (i !== index) {
                    document.getElementById('wordDiv' + i).style.opacity = '0.35';
                }
            }
            document.getElementById('wordDiv' + index).style.opacity = '1';
            // 高亮显示当前选中的单词
            for (var i = 1; i <= 4; i++) {
                document.getElementById('wordDiv' + i).classList.remove('selected-word');
            }
            document.getElementById('wordDiv' + index).classList.add('selected-word');
            
            // 记录选择的单词 - 客户端控制台日志
            if (shouldLog) {
                console.log('[LOG] 用户选择单词:', { user_id: user_id, word: word, index: index });
            }
            
        }
        
        // 完成选择并检查猜测者状态
        function finalizeSelection() {
            isSelectionComplete = true;
            // 通过AJAX将选中的单词保存到SESSION
            saveSelectedWordToSession(selectedWord);
            // 禁用所有单词选择
            for (var i = 1; i <= 4; i++) {
                document.getElementById('wordDiv' + i).style.pointerEvents = 'none';
                document.getElementById('wordDiv' + i).style.cursor = 'default';
            }
            
            // 记录选择完成日志 - 客户端控制台日志
            if (shouldLog) {
                console.log('[LOG] 单词选择完成，等待猜测者', { user_id: user_id, selected_word: selectedWord });
            }
            
            // 等待猜测者跳转
            waitForGuessersAndSubmit();
        }
        
        // 保存选中的单词到SESSION
        function saveSelectedWordToSession(word, index) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_word_to_session.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (shouldLog) {
                        console.log('[LOG] 单词已保存到SESSION: ' + word);
                    }
                }
            };
            xhr.send('selected_word=' + encodeURIComponent(word));
        }
        
        // 等待猜测者跳转并提交表单
        function waitForGuessersAndSubmit() {
            var waitCount = 0;
            var waitInterval = setInterval(function() {
                waitCount++;
                
                // 检查猜测者状态
                checkGuesserStatus(function(allGuessersReady) {
                    if (allGuessersReady || waitCount >= 10) {
                        clearInterval(waitInterval);
                        
                        // 如果还有猜测者未跳转，处理这些用户
                        if (!allGuessersReady) {
                            handleUnresponsiveGuessers();
                        }
                        
                        // 直接跳转页面，而不是提交表单，避免URL末尾出现问号
                        window.location.href = 'describe.php';
                    }
                });
            }, 1000);
        }
        
        // 检查猜测者状态
        function checkGuesserStatus(callback) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_guesser_status.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    callback(response.allReady);
                }
            };
            xhr.send();
        }
        
        // 处理未响应的猜测者
        function handleUnresponsiveGuessers() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'kick_unresponsive_guessers.php', true);
            xhr.send();
        }
        
        // 页面加载时启动计时器
        window.onload = startTimer;
        
        // 初始化全局变量username（用于activity-detector.js）
        window.username = username;
        window.user_id = "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>";
    </script>
</body>
</html>
    