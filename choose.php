<?php
    // Start the session
    session_start();

    include "../config.inc";

    $conn=mysqli_connect("localhost",$db_user,$db_password,$db_name, $db_port);
    if(mysqli_connect_errno())
    {
        echo "connect db failed:".mysqli_connect_error();
    }

    $username=$_SESSION['username'];
    mysqli_set_charset($conn,"utf8");
    
    $ret=mysqli_query($conn,'SELECT COUNT(*) as count FROM tb_words');
    $row=mysqli_fetch_array($ret);
    $count = $row[0];

    function getRandomInt($max) 
    {
        return rand(0, $max - 1);
    }

    $index1 = getRandomInt($count);
    do {
        $index2 = getRandomInt($count);
    } while ($index1 === $index2);

    do {
        $index3 = getRandomInt($count);
    } while ($index1 === $index3 || $index2===$index3);

    do {
        $index4 = getRandomInt($count);
    } while ($index1 === $index4 || $index2 === $index4 || $index3 === $index4);

    $sql = "SELECT word FROM tb_words LIMIT $index1, 1";
    $result = mysqli_query($conn,$sql);
    $row = mysqli_fetch_array($result);
    $word1 = $row[0];
    $sql = "SELECT word FROM tb_words LIMIT $index2, 1";
    $result = mysqli_query($conn,$sql);
    $row = mysqli_fetch_array($result);
    $word2 = $row[0];

    $sql = "SELECT word FROM tb_words LIMIT $index3, 1";
    $result = mysqli_query($conn,$sql);
    $row = mysqli_fetch_array($result);
    $word3 = $row[0];

    $sql = "SELECT word FROM tb_words LIMIT $index4, 1";
    $result = mysqli_query($conn,$sql);
    $row = mysqli_fetch_array($result);
    $word4 = $row[0];

    mysqli_close($conn);
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
    
    <form id="wordForm" action="describe.php" method="post">
    <input type="hidden" name="user_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
    <input type="hidden" id="selectedWord" name="word">
    <input type="hidden" id="selectedWordFinal" name="word_final">
    <input type="hidden" name="username" value="<?php echo $username; ?>"> <!-- 保留作为辅助显示 -->
</form>

    <!-- <img src="./example4.png" style="left:0px;top:0px;z-index:-1;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;"> -->
    <!-- 四个角落的图片 -->
    <img src="./Choose3.svg"  style="position:absolute;left:62vw;top:0vh;overflow:hidden; width:38%;height:39%;z-index:0;opacity: 1;" /> <!-- topright -->
    <img src="./Choose4.svg"  style="position:absolute;left:0vw;top:56vh;overflow:hidden; width:46%;z-index:0;opacity: 1;" /> <!-- downleft -->
    <img src="./Choose2.svg"  style="position:absolute;left:2.5vw;top:-6.8vh;overflow:hidden;width:38%; z-index:0;filter: invert(100%);" /><!-- topleft-->
    <img src="./Choose1.svg"  style="position:absolute;left:71.8vw;top:70.8vh;width:28%;overflow:hidden;z-index:0;opacity: 1;" />  <!-- downright -->
    
    <div id="wordDiv1" onclick="selectWord('<?php echo $word1; ?>', 1)" style="position:absolute;left:23vw;top:25vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $word1; ?></p>
    </div>

    <div id="wordDiv2" onclick="selectWord('<?php echo $word2; ?>', 2)" style="position:absolute;left:56vw;top:25vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $word2; ?></p>
    </div>

    <div id="wordDiv3" onclick="selectWord('<?php echo $word3; ?>', 3)" style="position:absolute;left:23vw;top:54vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $word3; ?></p>
    </div>

    <div id="wordDiv4" onclick="selectWord('<?php echo $word4; ?>', 4)" style="position:absolute;left:56vw;top:54vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $word4; ?></p>
    </div>
    <script>
        // 全局变量 - 优先使用user_id作为主要标识
        var user_id = "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>";
        var username = "<?php echo $username; ?>"; // 保留作为辅助显示
        var timer = 10;
        var timerInterval;
        var selectedWord = "<?php echo $word1; ?>"; // 默认选择word1
        var isSelectionComplete = false;
        var room = "<?php echo isset($_SESSION['room']) ? $_SESSION['room'] : ''; ?>";
        var rivalId = "<?php echo isset($_SESSION['rival_id']) ? $_SESSION['rival_id'] : ''; ?>";
        var hasSelected = false;
        
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
            document.getElementById('selectedWord').value = word;
            document.getElementById('selectedWordFinal').value = word;
            
            // 如果是首次选择，则将其他单词的可见度降低
            if (!hasSelected) {
                hasSelected = true;
                for (var i = 1; i <= 4; i++) {
                    if (i !== index) {
                        document.getElementById('wordDiv' + i).style.opacity = '0.35';
                    }
                }
            }
            
            // 高亮显示当前选中的单词
            for (var i = 1; i <= 4; i++) {
                document.getElementById('wordDiv' + i).classList.remove('selected-word');
            }
            document.getElementById('wordDiv' + index).classList.add('selected-word');
        }
        
        // 完成选择并检查猜测者状态
        function finalizeSelection() {
            isSelectionComplete = true;
            
            // 禁用所有单词选择
            for (var i = 1; i <= 4; i++) {
                document.getElementById('wordDiv' + i).style.pointerEvents = 'none';
                document.getElementById('wordDiv' + i).style.cursor = 'default';
            }
            
            // 等待猜测者跳转
            waitForGuessersAndSubmit();
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
                        
                        // 提交表单
                        document.getElementById('wordForm').submit();
                    }
                });
            }, 1000);
        }
        
        // 检查猜测者状态
        function checkGuesserStatus(callback) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_guesser_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    callback(response.allReady);
                }
            };
            xhr.send('room=' + encodeURIComponent(room) + '&user_id=' + encodeURIComponent(user_id) + '&username=' + encodeURIComponent(username));
        }
        
        // 处理未响应的猜测者
        function handleUnresponsiveGuessers() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'kick_unresponsive_guessers.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('room=' + encodeURIComponent(room) + '&user_id=' + encodeURIComponent(user_id) + '&username=' + encodeURIComponent(username));
        }
        
        // 页面加载时启动计时器
        window.onload = startTimer;
        
        // 初始化全局变量username（用于activity-detector.js）
        window.username = username;
        window.user_id = "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>";
    </script>
</body>
</html>
    