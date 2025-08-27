<?php
    include "log.php";
    // Start the session
    session_start();
    // 关闭错误报告
    error_reporting(0);
    // 定义需要记录日志的用户ID
    $log_user_ids = [8, 14];

    // 日志辅助函数 - 只记录特定用户ID的日志
    function shouldLog($user_id) {
        global $log_user_ids;
        return in_array($user_id, $log_user_ids);
    }
    // 优先从SESSION获取数据，如果不存在则从POST获取并更新SESSION
    // if (isset($_POST['user_id'])) {
    //     $_SESSION['user_id'] = $_POST['user_id'];
    // }
    // if (isset($_POST['username'])) {
    //     $_SESSION['username'] = $_POST['username']; // 保留作为辅助显示
    // }
    // if (isset($_POST['room'])) {
    //     $_SESSION['room'] = $_POST['room'];
    // }
    // // 确保同时保存room_id
    // if (isset($_POST['room_id'])) {
    //     $_SESSION['room_id'] = $_POST['room_id'];
    // }
    // if (isset($_POST['first_user_id'])) {
    //     $_SESSION['first_user_id'] = $_POST['first_user_id'];
    // }
    // if (isset($_POST['room_members'])) {
    //     $_SESSION['room_members'] = json_decode($_POST['room_members'], true);
    // }
    // 为了兼容现有代码，设置username1和username2
    // if (isset($_SESSION['user_id'])) {
    //     $_SESSION['user_id1'] = $_SESSION['room']['members'][0]['id'];
    //     $_SESSION['username1'] = $_SESSION['room']['members'][0]['id'];
        // 假设房间中有两个用户，这里简单处理第二个用户
        // if (isset($_SESSION['room']['user_cnt']) && $_SESSION['room']['user_cnt'] >= 2) {
        //     foreach ($_SESSION['room_members'] as $member) {
        //         if ($member['id'] != $_SESSION['user_id']) {
        //             $_SESSION['user_id2'] = $member['id'];
        //             $_SESSION['username2'] = $member['name'];
        //             break;
        //         }
        //     }
        // }
    // }
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
    <script >
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth;
        var role = ''; // 角色：'describer' 或 'guesser'
        var gameUrl = ''; // 游戏URL
        <?php 
            // 优先使用user_id作为主要标识，username保留作为辅助显示
            $user_id = $_SESSION['user_id'];
            $username=$_SESSION['username'];
            $room = $_SESSION['room']['name'];
            // 确保在SESSION中设置room_id
            $room_id = isset($_SESSION['room']['id']) ? $_SESSION['room']['id'] : '';
            
            // 记录日志 - 只记录特定用户ID
            if (shouldLog($user_id)) {
                Logger::info("用户进入房间页面", ["username" => $username, "user_id" => $user_id, "room" => $room, "room_id" => $room_id]);
            }
            
            // 优先声明user_id，username保留作为辅助显示
            print("var user_id=$user_id;");
            print("var username='$username'; // 保留作为辅助显示\n");
            print("var room = '$room';\n");
            // 确保在JavaScript变量中包含room_id
            print("var room_id = '$room_id';\n");
        ?>
        
        // 等待5秒后自动开始游戏
        function checkUserCount()
        {
            console.log("[LOG] time called - Count1: " + Count1);            
            timeCur=timeObj.getTime();  
            Count1=Count1+1;            
            if(Count1>=5)
            {
                Count1=-1000;
                clearInterval(timer1);                
                console.log("[LOG] time up! 5秒已到，准备请求服务器分配角色");
                
                // 请求服务器分配角色
                requestServerRoleAssignment();
            }          
        }
        
        // 请求服务器分配角色
        function requestServerRoleAssignment() {
            console.log('[LOG] 请求服务器分配角色 - user_id: ' + user_id + ', room_id: ' + room_id);
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'assign_role.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            // 准备请求参数
            const params = `user_id=${encodeURIComponent(user_id)}&room_id=${encodeURIComponent(room_id)}`;
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.log('[LOG] 角色分配响应:', response);
                            
                            if (response.status === 'success') {
                                // 保存服务器分配的角色
                                role = response.role;
                                
                                // 所有角色都先跳转到start.php
                                gameUrl = 'start.php'; // 先跳转到start.php
                                console.log('[LOG] 服务器分配的角色:', role, '游戏URL:', gameUrl);
                                
                                // 创建表单并提交到相应的游戏页面
                                submitGameForm();
                            } else {
                                console.error('[LOG] 角色分配失败:', response.message);
                                // 失败时可以提供一个默认行为或提示用户
                                alert('角色分配失败，请刷新页面重试');
                            }
                        } catch (e) {
                            console.error('[LOG] 解析角色分配响应失败:', e);
                            alert('系统错误，请刷新页面重试');
                        }
                    } else {
                        console.error('[LOG] 请求角色分配失败，HTTP状态码:', xhr.status);
                        alert('网络错误，请检查您的连接后重试');
                    }
                }
            };
            
            // 发送请求
            xhr.send(params);
        }
        
        // 创建表单并提交到游戏页面
            function submitGameForm() {
                // 将必要信息存储到sessionStorage，同时保存room和room_id
                sessionStorage.setItem('user_id', user_id);
                sessionStorage.setItem('username', username); // 保留作为辅助显示
                sessionStorage.setItem('room', room);
                // 确保保存room_id，优先使用PHP传递的值
                sessionStorage.setItem('room_id', room_id);
                sessionStorage.setItem('role', role);
                
                console.log('[LOG] 保存会话信息并跳转 - user_id: ' + user_id + ', role: ' + role + ', room_id: ' + room_id);
                console.log('[LOG] 跳转至:', gameUrl);
                // 直接跳转，不再通过表单POST
                window.location.href = gameUrl;
            }
        
        // 启动计时器
        var timer1 = setInterval(checkUserCount, 1000);
    </script>   
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
                // 优先从sessionStorage获取数据，如果不存在则使用PHP提供的值
        var text_out1 = '';
        // 尝试从sessionStorage获取房间成员信息
        if (sessionStorage.getItem('room_members')) {
            const members = JSON.parse(sessionStorage.getItem('room_members'));
            if (members.length > 0) {
                text_out1 = members[0].name;
            }
        }
        // 如果没有从sessionStorage获取到，使用PHP提供的值
        text_out1 = text_out1 || '<?php echo $_SESSION['room']['members'][0]['name'] ?? ''; ?>';
        // 确保在sessionStorage中存在room_id
        if (sessionStorage.getItem('room_id')) {
            // 可以通过AJAX将room_id保存到服务器端SESSION
            console.log('[LOG] 准备保存room_id到服务器端SESSION: ' + sessionStorage.getItem('room_id'));
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_room_id.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('room_id=' + encodeURIComponent(sessionStorage.getItem('room_id')));
            console.log('[LOG] 已发送room_id保存请求');
            
            // 添加响应处理以记录保存结果
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        console.log('[LOG] room_id保存成功');
                    } else {
                        console.error('[LOG] room_id保存失败，HTTP状态码:', xhr.status);
                    }
                }
            }
        }
        window.addEventListener('load', () => {
            console.log('[LOG] 页面加载完成，准备更新用户1的弧形文本: ' + text_out1);
            // sessionStorage是客户端存储，无法直接同步到服务器端的SESSION
            // 如需同步，需要通过AJAX请求发送数据到服务器
            updateArcText(text_out1, 'textPathElement1', 'user1_arcPath',220,50);
            console.log('[LOG] 用户1的弧形文本已更新');
        });
        window.addEventListener('resize', () => {
            console.log('[LOG] 页面大小调整，更新用户1的弧形文本');
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
                // 获取对手信息
        var text_out2 = '';
        // 尝试从sessionStorage获取房间成员信息
        if (sessionStorage.getItem('room_members')) {
            const members = JSON.parse(sessionStorage.getItem('room_members'));
            if (members.length > 0) {
                text_out1 = members[1].name;
            }
        }
        // 如果没有从sessionStorage获取到，使用PHP提供的值
        text_out2 = text_out2 || '<?php echo $_SESSION['room']['members'][1]['name'] ?? ''; ?>';
        window.addEventListener('load', () => {
            console.log('[LOG] 页面加载完成，准备更新用户2的弧形文本: ' + text_out2);
            updateArcText(text_out2, 'textPathElement2', 'user2_arcPath',220,50);
            console.log('[LOG] 用户2的弧形文本已更新');
        });
        window.addEventListener('resize', () => {
            console.log('[LOG] 页面大小调整，更新用户2的弧形文本');
            const text = document.getElementById('textPathElement2')?.textContent || text_out2;
            updateArcText(text, 'textPathElement2', 'user2_arcPath',220,50);
        });
            </script>
        </div>
        
    </div>

 
</body>
</html>