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
        <img src="./guess1.svg"  style="position:absolute;right:-18vw;bottom:-3vh;overflow:hidden; width:100vw;z-index:0;opacity: 1;" /> <!-- downright -->
        <img src="./guess4.svg"  style="position:absolute;left:10.7vw;top:35.7vh;overflow:hidden;width:3.5vw;z-index:4;" /> 
        <img src="./guess4.svg"  style="position:absolute;left:15.0vw;top:35.7vh;overflow:hidden;width:3.5vw;z-index:4;" /> 
        <img src="./guess4.svg"  style="position:absolute;left:19.3vw;top:35.7vh;overflow:hidden;width:3.5vw;z-index:4;" /> 

        <!-- <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);z-index:5;font-family: 'SourceHanSans-Heavy', sans-serif;font-size:44px;color:white;"> -->
        <div style="position:absolute;left:8.8vw;top:31.5vh;width:30vw;height:20vh;z-index:0;justify-content:center;align-items:center;">
            <img src="./guess5.svg" style="position:absolute;width:33.5vw;"/> 
            <p style="position:absolute;width:23vw;height:20vh;text-align:left;left:8.2vw;top:8.5vh;line-height:5vh;font-family: 'SourceHanSans-Medium', sans-serif;white-space:nowrap;font-size:2.5vw;color:white;z-index:4;letter-spacing: 0.6vw;">圣诞鹿</p>
        </div>
        <div style="position:absolute;left:8.8vw;top:46.7vh;width:30vw;height:20vh;z-index:0;justify-content:center;align-items:center;">
            <img src="./guess3.svg" style="position:absolute;width:33.5vw;"/> 
            <p style="position:absolute;width:23vw;height:20vh;text-align:left;left:8.2vw;top:8.5vh;line-height:5vh;font-family: 'SourceHanSans-Medium', sans-serif;white-space:nowrap;font-size:2.5vw;color:white;z-index:4;letter-spacing: 0.6vw;">圣诞鹿</p>
        </div>
        <div style="position:absolute;left:8.8vw;top:62vh;width:30vw;height:20vh;z-index:0;justify-content:center;align-items:center;">
            <img src="./guess3.svg" style="position:absolute;width:33.5vw;"/> 
            <p style="position:absolute;width:23vw;height:20vh;text-align:left;left:8.2vw;top:8.5vh;line-height:5vh;font-family: 'SourceHanSans-Medium', sans-serif;white-space:nowrap;font-size:2.5vw;color:white;z-index:4;letter-spacing: 0.6vw;">圣诞鹿</p>
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
            print("var username='$username';\n");
            print("var user_id=$user_id;\n");
            print("var room = '$room';\n");
            print("var rival = '$rival';\n");
            print("var rival_id = $rival_id;\n");
            print("var first_user_id = $first_user_id;\n");
        ?>
        
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth;
        var guessTimeout = 60; // 猜测时间限制（秒）
        
        timer1=setInterval(checkUserCount,1000);    
        
        // 处理用户输入并提交猜测
        document.getElementById('user-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitGuess();
            }
        });
        
        function submitGuess() {
            var userGuess = document.getElementById('user-input').value.trim();
            if (userGuess) {
                console.log('用户猜测:', userGuess);
                
                // 创建表单并提交到验证页面
                var form = document.createElement('form');
                form.method = 'post';
                form.action = 'check_answer.php'; // 假设存在这个验证答案的文件
                
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
                
                // 超时处理，可以跳转到错误页面或重新开始
                window.location.href = 'wrong.php';
            }
        }
    </script>
</body>
</html>
