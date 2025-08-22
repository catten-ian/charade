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
        @font-face {
            font-family: 'SourceHanSans-Medium';
            src: url('./SourceHanSans-Medium.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'SourceHanSans-Heavy';
            src: url('./SourceHanSans-Heavy.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        /* 设置所有猜测结果容器初始为隐藏 */
        .guess-container {
            display: none;
        }
    </style>
    <title>Guess What It is</title>

    
</head>
<body bgcolor="#1270F8" style="overflow:hidden;">

        <!-- <img src="./example6.png" style="left:0px;top:0px;z-index:-2;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;"> -->
        <!-- 四个角落的图片 -->
        <img src="./guess2.svg"  style="position:absolute;left:2.5vw;top:-6.8vh;overflow:hidden;width:38%; z-index:1;filter: invert(100%);" /> <!-- topleft-->
        <img src="./room2.png"  style="position:absolute;left:91vw;top:3vh;width:8vw;z-index:1" /> <!-- topright -->
        <img src="./room4.png"  style="position:absolute;left:-8vw;bottom:-19vh;overflow:hidden;width:21vw;z-index:1;transform: rotate(268deg);" /> <!-- downleft -->
        <img src="./describe2.svg"  style="position:absolute;right:0vw;bottom:2vh;overflow:hidden; width:28vw;z-index:0;opacity: 1;" /> <!-- downright -->
        <img id="companion" src="./guess1.svg"  style="position:absolute;right:-18vw;bottom:-3vh;overflow:hidden; width:100vw;z-index:0;opacity: 1;" /> <!-- downright -->
        <img src="./guess4.svg"  style="position:absolute;left:10.7vw;top:35.7vh;overflow:hidden;width:3.5vw;z-index:4;" /> 
        <img src="./guess4.svg"  style="position:absolute;left:15.0vw;top:35.7vh;overflow:hidden;width:3.5vw;z-index:4;" /> 
        <img src="./guess4.svg"  style="position:absolute;left:19.3vw;top:35.7vh;overflow:hidden;width:3.5vw;z-index:4;" /> 

        <!-- <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);z-index:5;font-family: 'SourceHanSans-Heavy', sans-serif;font-size:44px;color:white;"> -->
        <div id="guess1" class="guess-container" style="position:absolute;left:8.8vw;top:31.5vh;width:30vw;height:20vh;z-index:0;justify-content:center;align-items:center;">
            <img class="guess-bg" src="./guess3.svg" style="position:absolute;width:33.5vw;"/> 
            <p class="guess-text" style="position:absolute;width:23vw;height:20vh;text-align:left;left:8.2vw;top:8.5vh;line-height:5vh;font-family: 'SourceHanSans-Medium', sans-serif;white-space:nowrap;font-size:2.5vw;color:white;z-index:4;letter-spacing: 0.6vw;"></p>
        </div>
        <div id="guess2" class="guess-container" style="position:absolute;left:8.8vw;top:46.7vh;width:30vw;height:20vh;z-index:0;justify-content:center;align-items:center;">
            <img class="guess-bg" src="./guess3.svg" style="position:absolute;width:33.5vw;"/> 
            <p class="guess-text" style="position:absolute;width:23vw;height:20vh;text-align:left;left:8.2vw;top:8.5vh;line-height:5vh;font-family: 'SourceHanSans-Medium', sans-serif;white-space:nowrap;font-size:2.5vw;color:white;z-index:4;letter-spacing: 0.6vw;"></p>
        </div>
        <div id="guess3" class="guess-container" style="position:absolute;left:8.8vw;top:62vh;width:30vw;height:20vh;z-index:0;justify-content:center;align-items:center;">
            <img class="guess-bg" src="./guess3.svg" style="position:absolute;width:33.5vw;"/> 
            <p class="guess-text" style="position:absolute;width:23vw;height:20vh;text-align:left;left:8.2vw;top:8.5vh;line-height:5vh;font-family: 'SourceHanSans-Medium', sans-serif;white-space:nowrap;font-size:2.5vw;color:white;z-index:4;letter-spacing: 0.6vw;"></p>
        </div>
        
        <input type="text" id="user-input" placeholder="请输入你的猜测" style="outline:none;position:absolute;border:none;background-color:transparent;left:9.8vw;bottom:60.8vh;width: 600px;height:100px;z-index:5;font-family: 'SourceHanSans-Heavy', sans-serif;font-size:44px;" />
        <script>
        const inputElement = document.getElementById('user-input');
            
            // 设置初始半透明颜色
            inputElement.style.color = 'rgba(255, 255, 255, 1)';
            
            // 监听输入事件
            inputElement.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    // 有内容时设置为不透明白色
                    this.style.color = 'rgba(255, 255, 255, 1)';
                } else {
                    // 无内容时恢复半透明
                    this.style.color = 'rgba(255, 255, 255, 1)';
                }
            });
            
            // 处理焦点和失去焦点状态
            // inputElement.addEventListener('focus', function() {
            //      获得焦点时取消边框高亮
            //     this.style.outline = 'none';
            // });
        </script>
        

    <script>
        // 接收POST数据并设置会话变量
        <?php
            // 接收从waiting.php传递的表单数据
            if (isset($_POST['username'])) {
                $_SESSION['username'] = $_POST['username'];
            }
            if (isset($_POST['user_id'])) {
                $_SESSION['user_id'] = $_POST['user_id'];
            }
            if (isset($_POST['room'])) {
                $_SESSION['room'] = $_POST['room'];
            }
            if (isset($_POST['rival'])) {
                $_SESSION['rival'] = $_POST['rival'];
            }
            if (isset($_POST['rival_id'])) {
                $_SESSION['rival_id'] = $_POST['rival_id'];
            }
            if (isset($_POST['first_user_id'])) {
                $_SESSION['first_user_id'] = $_POST['first_user_id'];
            }
            if (isset($_POST['role'])) {
                $_SESSION['role'] = $_POST['role'];
            }
            
            $username = $_SESSION['username'];
            $user_id = $_SESSION['user_id'];
            $room = $_SESSION['room'];
            $rival = $_SESSION['rival'];
            $rival_id = $_SESSION['rival_id'];
            $first_user_id = $_SESSION['first_user_id'];
            // 从会话中获取猜测相关数据
            $guess_count = isset($_SESSION['guess_count']) ? $_SESSION['guess_count'] : 0;
            $guess_history = isset($_SESSION['guess_history']) ? json_encode($_SESSION['guess_history']) : '[]';
            $correct_word = isset($_SESSION['correct_word']) ? $_SESSION['correct_word'] : '';
            $current_guess = isset($_SESSION['current_guess']) ? $_SESSION['current_guess'] : '';
            
            print("var username='$username';\n");
            print("var user_id=$user_id;\n");
            print("var room = '$room';\n");
            print("var rival = '$rival';\n");
            print("var rival_id = $rival_id;\n");
            print("var first_user_id = $first_user_id;\n");
            print("var guess_count = $guess_count;\n");
            print("var guess_history = $guess_history;\n");
            print("var correct_word = '$correct_word';\n");
            print("var current_guess = '$current_guess';\n");
        ?>
        
        var timeObj = new Date();
        var startTimeInMs = timeObj.getTime();
        var Count1 = 0;
        var window_width = window.innerWidth;
        var guessTimeout = 180; // 猜测时间限制（秒）- 3分钟
        var companionImg = document.getElementById('companion');
        var inputElement = document.getElementById('user-input');
        var isGuessing = false; // 标记是否正在进行猜测
        
        // 设置初始半透明颜色
        inputElement.style.color = 'rgba(255, 255, 255, 1)';
        
        // 监听输入事件
        inputElement.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                // 有内容时设置为不透明白色
                this.style.color = 'rgba(255, 255, 255, 1)';
            } else {
                // 无内容时恢复半透明
                this.style.color = 'rgba(255, 255, 255, 1)';
            }
        });
        
        // 初始化页面，显示已有的猜测历史
        function initPage() {
            // 如果有猜测历史，显示它们
            if (guess_history && guess_history.length > 0) {
                for (var i = 0; i < guess_history.length; i++) {
                    displayGuessResult(i + 1, guess_history[i], guess_history[i] === correct_word);
                }
            }
            
            // 监听输入事件，实现用户需求中的功能
            inputElement.addEventListener('input', function() {
                // 当输入框开始接收到内容时，处理猜测逻辑
                if (this.value.trim() !== '' && !isGuessing && guess_count > 0) {
                    // 将前一个div的内容和样式移到后一个div上
                    for (var i = guess_count; i > 1; i--) {
                        var prevGuessElement = document.getElementById('guess' + (i - 1));
                        var currGuessElement = document.getElementById('guess' + i);
                        
                        if (prevGuessElement && currGuessElement) {
                            // 复制前一个div的样式和内容
                            var prevOpacity = prevGuessElement.style.opacity || '1';
                            var prevGuessText = prevGuessElement.querySelector('.guess-text').textContent;
                            var prevBgSrc = prevGuessElement.querySelector('.guess-bg').src;
                            
                            // 更新当前div
                            currGuessElement.style.display = 'flex';
                            currGuessElement.style.opacity = prevOpacity;
                            currGuessElement.querySelector('.guess-text').textContent = prevGuessText;
                            currGuessElement.querySelector('.guess-bg').src = prevBgSrc;
                        }
                    }
                    
                    // 将companion中的图片换回guess1.svg
                    if (companionImg) {
                        companionImg.src = './guess1.svg';
                    }
                }
            });
        }
        
        // 显示猜测结果
        function displayGuessResult(index, guess, isCorrect) {
            var guessElement = document.getElementById('guess' + index);
            if (guessElement) {
                // 显示猜测结果容器
                guessElement.style.display = 'flex';
                
                // 更新猜测文本
                var guessText = guessElement.querySelector('.guess-text');
                if (guessText) {
                    guessText.textContent = guess;
                }
                
                // 更新背景图片和透明度
                var guessBg = guessElement.querySelector('.guess-bg');
                if (guessBg) {
                    if (isCorrect) {
                        // 猜对了
                        guessElement.style.opacity = '1';
                        guessBg.src = './guess5.svg';
                        // 更换companion图片
                        if (companionImg) {
                            companionImg.src = './guess6.svg';
                        }
                        
                        // 发送消息给相关程序说明该用户猜对了
                        sendGameStatusUpdate('correct_guess');
                    } else {
                        // 猜错了
                        guessElement.style.opacity = '0.35';
                        guessBg.src = './guess3.svg';
                        // 更换companion图片
                        if (companionImg) {
                            companionImg.src = './guess7.svg';
                        }
                        
                        // 如果3次全猜错，发送消息给相关程序
                        if (index === 3) {
                            sendGameStatusUpdate('all_wrong');
                        }
                    }
                }
            }
        }
        
        // 发送游戏状态更新消息
        function sendGameStatusUpdate(status) {
            // 创建AJAX请求发送游戏状态更新
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_game_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            // 构建请求参数
            var params = 'room=' + encodeURIComponent(room) +
                         '&user_id=' + encodeURIComponent(user_id) +
                         '&status=' + encodeURIComponent(status);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        console.log('游戏状态更新成功:', status);
                    } else {
                        console.error('游戏状态更新失败');
                    }
                }
            };
            
            xhr.send(params);
        }
        
        // 处理用户输入并提交猜测
        document.getElementById('user-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitGuess();
            }
        });
        
        function submitGuess() {
            var userGuess = document.getElementById('user-input').value.trim();
            if (userGuess && guess_count < 3) {
                isGuessing = true;
                console.log('用户猜测:', userGuess);
                
                // 创建表单并提交到验证页面
                var form = document.createElement('form');
                form.method = 'post';
                form.action = 'check_answer.php';
                
                // 添加表单字段
                var fields = [
                    {name: 'username', value: username},
                    {name: 'user_id', value: user_id},
                    {name: 'room', value: room},
                    {name: 'rival', value: rival},
                    {name: 'rival_id', value: rival_id},
                    {name: 'first_user_id', value: first_user_id},
                    {name: 'role', value: 'guesser'},
                    {name: 'guess', value: userGuess}
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
        
        function checkUserCount() {
            Count1 = Count1 + 1;
            
            // 检查是否超时
            if (Count1 >= guessTimeout) {
                Count1 = -1000;
                clearInterval(timer1);
                console.log("猜测时间到!");
                
                // 超时处理，跳转到错误页面
                window.location.href = 'wrong.php';
            }
        }
        
        // 页面加载完成后初始化
        window.onload = function() {
            initPage();
            timer1 = setInterval(checkUserCount, 1000);
        };
    </script>
</body>
</html>
