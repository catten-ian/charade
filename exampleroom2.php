<?php
    // Start the session
    session_start();
    // 从POST请求获取数据并设置到会话中
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charades!</title>
    <link rel="stylesheet" href="styles2.css">
    <script type="text/javascript">
        function updateArcText(text, txtId, pathId, left_offset, top_offset) {
            const textElement = document.getElementById(txtId);
            const path = document.getElementById(pathId);
            const svg = document.getElementById(pathId.split('_arcPath')[0] + '_arcText');
            
            if (!textElement || !path || !svg) {
                console.error('缺少 DOM 元素:', txtId, pathId);
                return;
            }

            // 强制文本可见样式
            // textElement.style.fill = 'white';          // 与背景对比
            textElement.style.fontSize = '18px';       // 确保大小
            //textElement.style.fontFamily = 'Arial';    // 确保字体
            textElement.textContent = text;

            const divId = "div_" + pathId.split('_arcPath')[0];
            const div_user = document.getElementById(divId);
            if (div_user) {
                // 获取容器位置（确保路径在容器下方）
                const userRect = div_user.getBoundingClientRect();
                //const centerX = userRect.left + userRect.width / 2;
                const centerX = left_offset+60;
                const baseY = userRect.bottom + 20+top_offset; // 路径在头像下方 20px

                // 模拟手动路径逻辑：水平圆弧（类似 M 100 100 A 100 20 0 0 1 300 100）
                const radius = 50; // 水平半径（对应手动路径的 100）
                const startX = left_offset+30;//centerX - radiusX;
                const endX = left_offset+300;
                //const yPos = baseY;  // 路径 Y 坐标（对应手动路径的 100）
                const yPos = 100+top_offset;

                // 设置路径：与手动写死的逻辑对齐
                //path.setAttribute('d', `M ${startX} ${yPos} A ${radius} ${radius} 0 0 1 ${endX} ${yPos}`);
                //path.setAttribute('d', `M 100 100 A 100 20 0 0 1 300 100`);
                if(txtId === 'textPathElement1') {
                    path.setAttribute('d', `M 10 165 A 45 25 0 0 1 120 165`);
                }   
                else {
                    path.setAttribute('d', `M 155 170 A 45 25 0 0 1 265 170`);
                }

                // 文本居中（与手动路径的显示逻辑一致）
                textElement.setAttribute('startOffset', '50%');
                textElement.setAttribute('text-anchor', 'middle');
                textElement.setAttribute('dominant-baseline', 'middle');
            }
        };
    </script>
    <style>
        @font-face {
            font-family: 'SourceHanSans-Medium';
            src: url('./SourceHanSans-Medium.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'SourceHanSans-Normal';
            src: url('./SourceHanSans-Normal.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
    </style>
</head>

<body bgcolor="#1270F8" style="overflow:hidden;">
    <!-- <img src="./example2.png" height="100%" style="z-index:-1;filter:brightness(50%)"> -->
    <img src="./room2.png"  style="position:absolute;left:91vw;top:3vh;width:8vw;z-index:1" /> <!-- topright -->
    <img src="./room4.png"  style="position:absolute;left:-8vw;bottom:-19vh;overflow:hidden;width:21vw;z-index:1;transform: rotate(268deg);" /> <!-- downleft -->
    <img src="./describe2.svg"  style="position:absolute;right:0vw;bottom:2vh;overflow:hidden; width:28vw;z-index:0;opacity: 1;" /> <!-- downright -->
    <img src="./room6.png" style="position:absolute;left: 2.5vw;top: 2vh;overflow:hidden;width: 1.4vw;">
    <img src="./room7.png" style="position:absolute;left: 3.2vw;top: 12.9vh;width: 9.5vw;filter: invert(100%);"> 
    <p style="font-size: calc(2.5vw + 2.5vh);position:absolute;left: 4.8vw;top: -3.2vh;color:white;font-family: 'SourceHanSans-Medium';text-shadow: rgba(255,255,255, 0.4) 0.2vw 0.3vw;">Cipher</p>
    <p style="font-size: calc(1.5vw + 1.5vh);position:absolute;left: 5vw;top: 10vh;color:white;text-shadow: rgba(255, 255, 255, 0.4) 0.2vw 0.2vw;font-family: 'SourceHanSans-Normal';">666666</p>
    <div class="centered">
    </div>
    <script src="./activity-detector.js"></script>
    <div id="users" style="display:flex;justify-content:center;align-items:center;position:absolute;top:0;left:0;right:0;bottom:0;margin:auto;gap:16vw; z-index:20;">
        <div id="div_user1" style="display:flex; z-index:20;">
            <div style="position:relative; display:flex; justify-content:center; align-items:center; z-index:20;">
                <img src="./avatarexample.png" id="div_img1" style="z-index:1; width:17vw; object-fit:contain;transform: translateY(-9vh);" />
                <img src="./room5.svg" style="z-index:0; width:55vw;  object-fit:contain; position:absolute;" />
            </div>
            
            <svg id="user1_arcText" viewBox="0 0 600 200" style="position:absolute; z-index:30; pointer-events:none;overflow:visible;">
                <path id="user1_arcPath" d="M 10 165 A 45 25 0 0 1 120 165" fill="none" stroke="none"></path>
                <text style="font-family: 'SourceHanSans-Normal'; text-anchor: middle; fill: black; dominant-baseline: middle; z-index:30;">
                    <textPath href="#user1_arcPath" id="textPathElement1" startOffset="50%" text-anchor="middle" dominant-baseline="middle" style="font-size: 18px;">username1</textPath>
                </text>
            </svg>

            <script>
                <?php echo "var text_out1 = '".$_SESSION['username']."';"; ?>
                window.addEventListener('load', () => updateArcText(text_out1, 'textPathElement1', 'user1_arcPath',220,50));
                window.addEventListener('resize', () => {
                    const text = document.getElementById('textPathElement1')?.textContent || text_out1;
                    updateArcText(text, 'textPathElement1', 'user1_arcPath',220,50);
                });
            </script>
        </div>
        <div id="div_user2" style="display:flex; z-index:20;">
            <div style="position:relative; display:flex; justify-content:center; align-items:center; z-index:20;">
                <img src="./avatarexample.png" id="div_img2" style="z-index:1; width:17vw; object-fit:contain;transform: translateY(-9vh);" />
                <img src="./room5.svg" style="z-index:0; width:55vw;  object-fit:contain; position:absolute;" />
            </div>
            
            <svg id="user2_arcText" viewBox="0 0 350 400" style="position:absolute;transform:translateX(-40%); z-index:30; pointer-events:none;overflow:visible;">
                <path id="user2_arcPath" d="M 155 170 A 45 25 0 0 1 265 170" fill="none" stroke="none"></path>
                <text style="font-family: 'SourceHanSans-Normal'; text-anchor: middle; fill: black; dominant-baseline: middle; z-index:30;">
                    <textPath href="#user2_arcPath" id="textPathElement2" startOffset="50%" text-anchor="middle" dominant-baseline="middle" style="font-size: 18px;">username1</textPath>
                </text>
            </svg>

            <script>
                <?php echo "var text_out2 = '" . $_SESSION['rival'] . "';"; ?>
                window.addEventListener('load', () => updateArcText(text_out2, 'textPathElement2', 'user2_arcPath',220,50));
                window.addEventListener('resize', () => {
                    const text = document.getElementById('textPathElement2')?.textContent || text_out2;
                    updateArcText(text, 'textPathElement2', 'user2_arcPath',220,50);
                });
            </script>
        </div>
        <!-- <div id="div_user2" style="display:flex; z-index:20;">
            <div style="position:relative; display:flex; justify-content:center; align-items:center; z-index:20;">
                <img src="./avatarexample.png" id="div_img1" style="z-index:1; width:17vw; object-fit:contain;transform: translateY(-9vh);" />
                <img src="./room5.svg" style="z-index:0; width:55vw;  object-fit:contain; position:absolute;" />
            </div>
            
            <svg id="user2_arcText" viewBox="0 0 600 200" style="position:absolute; top=3vh;bottom:6vh;transform:translateX(-40%); z-index:30; pointer-events:none;overflow:visible;">
                <path id="user2_arcPath" d="" fill="none" stroke="none" />
                <text style="font-family: sans-serif; text-anchor: middle; fill: white; dominant-baseline: middle; z-index:30;overflow:visible;">
                    <textPath href="#user2_arcPath" id="textPathElement2"></textPath>
                </text>
            </svg>

            <script>
                <?php echo "var text_out2 = '".$_SESSION['rival']."';"; ?>
                window.addEventListener('load', () => updateArcText(text_out2, 'textPathElement2', 'user2_arcPath',200,-30));
                window.addEventListener('resize', () => {
                    const text = document.getElementById('textPathElement2')?.textContent || text_out2;
                    updateArcText(text, 'textPathElement2', 'user2_arcPath', 200,-30);
                });
            </script>
        </div> -->
    </div>

    <script >
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth;
        var role = ''; // 角色：'describer' 或 'guesser'
        var gameUrl = ''; // 游戏URL
        <?php 
            $username=$_SESSION['username'];
            $user_id = $_SESSION['user_id'];
            $room = $_SESSION['room'];
            $rival = $_SESSION['rival'];
            $rival_id = $_SESSION['rival_id'];
            $first_user_id =$_SESSION['first_user_id'];
            print("var username='$username';\n");
            print("var user_id=$user_id;\n");
            print("var room = '$room';\n");
            print("var rival = '$rival';\n");
            print("var rival_id = $rival_id;\n");
            print("var first_user_id = $first_user_id;\n");
        ?>
        
        // 等待5秒后自动开始游戏
        function checkUserCount()
        {
            console.log("time called");            
            timeCur=timeObj.getTime();  
            Count1=Count1+1;           
            if(Count1>=5)
            {
                Count1=-1000;
                clearInterval(timer1);                
                console.log("time up!");
                
                // 随机分配角色
                assignRoles();
                // 创建表单并提交到相应的游戏页面
                submitGameForm();
            }          
        }
        
        // 随机分配describer和guesser角色
        function assignRoles() {
            // 检查当前用户是否是房间中的第一个用户
            if (user_id == first_user_id) {
                // 第一个用户有50%的概率成为describer
                role = Math.random() < 0.5 ? 'describer' : 'guesser';
            } else {
                // 第二个用户的角色与第一个用户相反
                // 这里通过发送请求获取第一个用户的角色
                // 由于无法直接获取，我们通过房间名和用户ID构建一个一致的随机结果
                // 确保两个用户在同一房间中总是获得互补的角色
                const seed = room + user_id + first_user_id;
                const firstUserIsDescriber = seed.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) % 2 === 0;
                role = firstUserIsDescriber ? 'guesser' : 'describer';
            }
            
            // 所有角色都先跳转到start.php
            gameUrl = 'start.php'; // 先跳转到start.php
            console.log('角色分配:', role);
            
            console.log('分配的角色:', role, '游戏URL:', gameUrl);
        }
        
        // 创建表单并提交到游戏页面
        function submitGameForm() {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = gameUrl;
            console.log('跳转至:', gameUrl);
            
            // 添加表单字段
            var fields = [
                {name: 'username', value: username},
                {name: 'user_id', value: user_id},
                {name: 'room', value: room},
                {name: 'rival', value: rival},
                {name: 'rival_id', value: rival_id},
                {name: 'first_user_id', value: first_user_id},
                {name: 'role', value: role}
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
        
        // 启动计时器
        var timer1 = setInterval(checkUserCount, 1000);
    </script>  
</body>
</html>