<?php
    // Start the session
    session_start();
    //$_SESSION['postdata']=json_decode($_POST);
    $_SESSION['username'] = $_POST['username'];
    // 保存用户类型为1
    $_SESSION['type'] = '1';

    include "../config.inc";

    $conn=mysqli_connect("localhost",$db_user,$db_password,$db_name, $db_port);
    if(mysqli_connect_errno())
    {
        echo "connect db failed:".mysqli_connect_error();
    }

    $username=$_SESSION['username'];
    mysqli_set_charset($conn,"utf8");
    
    // 检查用户是否存在，如果不存在则创建
    $stmt = mysqli_prepare($conn, "SELECT id FROM tb_user WHERE name = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        // 用户不存在，创建新用户
        $stmt = mysqli_prepare($conn, "INSERT INTO tb_user(name) VALUES(?)");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
    }
    
    // 重置用户分数
    $stmt = mysqli_prepare($conn, "UPDATE tb_user SET score = 0 WHERE name = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    
    // 关闭预处理语句
    mysqli_stmt_close($stmt);
    
    $sql_select="SELECT name FROM tb_user WHERE name='$username'";
    $ret=mysqli_query($conn,$sql_select);
    $row=mysqli_fetch_array($ret);
    if($username==$row[0]) {

    }
    else {
        $sql_insert="INSERT INTO tb_user(name) VALUES('$username')";
        mysqli_query($conn,$sql_insert);
    }    
    mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charades!</title>
    <link rel="stylesheet" href="styles2.css">
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
    <div id="div_user" style="display:flex;justify-content:center;align-items:center;position:absolute;top:0;left:0;right:0;bottom:0;margin:auto;">
        <div style="position:relative; display:flex; justify-content:center; align-items:center; ">
            <img src="./avatarexample.png" id="div_img1" style="z-index:1; width:17vw; object-fit:contain;transform: translateY(-9vh);" />
            <img src="./room5.svg" style="z-index:0; width:55vw;  object-fit:contain; position:absolute;" />
        </div>
        
        <svg id="arcText" viewBox="0 0 600 200" style="position:absolute; top:45vh; ">
            <path id="arcPath" d="M 245 130 A 25 25 0 0 1 355 130" fill="none" stroke="none" />
            <text style="font-family: 'SourceHanSans-Normal'; text-anchor: middle; font-size: 18px; fill: #000;">
                <textPath href="#arcPath" id="textPathElement"></textPath>
            </text>
        </svg>
        <script src="./activity-detector.js"></script>
        <script>
        function updateArcText(text) {
            const textElement = document.getElementById('textPathElement');
            const path = document.getElementById('arcPath');
            const svg = document.getElementById('arcText');
            
            if (!textElement || !path || !svg) {
                console.error('找不到必要的DOM元素');
                return;
            }
            
            textElement.textContent = text;

            const maxWidth = svg.clientWidth * 0.8;
            
            setTimeout(() => {
                const textLength = textElement.getComputedTextLength() || text.length * 15;
                
                // 进一步减小字体和增加紧凑度
                const fontSize = Math.max(8, Math.min(18, maxWidth / (text.length * 0.9)));
                // textElement.style.fontSize = `${fontSize}px`;
                
                const radius = 50
                const centerX = 200;
                
                const startX = centerX - radius;
                const endX = centerX + radius;
                
                // 降低弧形位置并增加弯曲度
                // path.setAttribute('d', `M 100 175 A 45 25 0 0 1 210 175`);
                
                // textElement.setAttribute('startOffset', '50%');
                // 文本居中（与手动路径的显示逻辑一致）
                textElement.setAttribute('startOffset', '50%');
                textElement.setAttribute('text-anchor', 'middle');
                textElement.setAttribute('dominant-baseline', 'middle');
            }, 100);
        }

        //var text_out="这里输入文字";
        <?php
            echo "var text_out='".$_SESSION['username']."';";
        ?>
        //text_out="这里输入文字";
        window.addEventListener('load', () => updateArcText(text_out));
        window.addEventListener('resize', () => {
            const text = document.getElementById('textPathElement')?.textContent || text_out;
            updateArcText(text);
        });
        </script>
    </div>
        <!--<table style="position:relative;left:30vw;top:-60vh;">
            <tr style="scale:0.8"><td><img src="./avatarexample.png" id="div_img1" style="position:relative;left:0vw;top:80vh;" /></td></tr>
            <tr style="position:relative;"><td>
                <div id="div_img2" style="position:relative;top=80vh;">
                    <svg width="200" height="400" style="margin:auto;left:80px">
                        <path id="curve" d="M10 80 Q 95 10 180 80" style="opacity:0%"/>
                        <text style="font-size:calc(2vw);margin:auto;">
                        <textPath xlink:href="#curve">&#8194; &ensp;
                            <?php 
                                // $name_len=strlen($_SESSION['username']);
                                // $name_out="";
                                // if ( $name_len < 3 ) 
                                // {
                                //     $name_out="&#8194; &ensp;".$_SESSION['username'];
                                // }
                                // elseif ( $name_len < 5 )
                                // {
                                //     $name_out="&#8194; &ensp;".$_SESSION['username'];
                                // } 
                                // else 
                                // { 
                                //     $name_out=$_SESSION['username'];
                                // }
                                // echo $name_out; ?>
                            </textPath>
                        </text>
                  </svg>
                </div> 
            </td></tr>
        </table> -->
       <!-- <img src="./room5.svg" style="z-index:-1;left:0;top:0;width:70%;height:70%;justify-content: center;align-items: center" /> 
        
        
    </div>-->
    
      
    <script >
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth;
        <?php 
            $username=$_SESSION['username'];
            $user_id = $_SESSION['user_id'];
            print("var username='$username';\n") ;
            print("var user_id='$user_id';\n") ;
        ?>
        var response={"ret_code":4};
        function encodeFormDataToUrlParams(formData) {
            // 将 FormData 转为数组，再按对象编码逻辑处理
            return Array.from(formData.entries()).map(([key, value]) => {
                return `${encodeURIComponent(key)}=${encodeURIComponent(value)}`;
            }).join("&");
        }

        function checkUserStatus()
        {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/charade/paring.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            var fmData=new FormData();
            fmData.append('user_id',user_id);
            xhr.send(encodeFormDataToUrlParams(fmData));
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {                    
                    console.log(xhr.responseText);
                    var response=JSON.parse(xhr.responseText);
                    // 当配对成功(ret_code=0)或房间已满员(room_status=2)时都应该跳转
                    if(response.ret_code==0 || response.room_status==2)
                    {
                        clearInterval(timer1);

                        var form = document.createElement('form');
                        form.method = 'post';
                        form.action = 'exampleroom2.php';
                        var fUsername = document.createElement('input');
                        fUsername.type = 'hidden';
                        fUsername.name = 'username';
                        fUsername.value = response.username;
                        form.appendChild(fUsername);

                        var fUserId = document.createElement('input');
                        fUserId.type = 'hidden';
                        fUserId.name = 'user_id';
                        fUserId.value = response.user_id;
                        form.appendChild(fUserId);

                        var fRivalId = document.createElement('input');
                        fRivalId.type = 'hidden';
                        fRivalId.name = 'rival';
                        fRivalId.value = response.rival_id;
                        form.appendChild(fRivalId);

                        var fFirstUserId = document.createElement('input');
                        fFirstUserId.type = 'hidden';
                        fFirstUserId.name = 'first_user_id';
                        fFirstUserId.value = response.first_user_id;
                        form.appendChild(fFirstUserId);

                        var fRoom = document.createElement('input');
                        fRoom.type = 'hidden';
                        fRoom.name = 'room';
                        fRoom.value = response.room;
                        form.appendChild(fRoom);

                        // 确保rival_id存在
                        var fRivalId = document.createElement('input');
                        fRivalId.type = 'hidden';
                        fRivalId.name = 'rival_id';
                        fRivalId.value = response.rival_id || 0;
                        form.appendChild(fRivalId);

                        // 确保rival存在
                        var fRival = document.createElement('input');
                        fRival.type = 'hidden';
                        fRival.name = 'rival';
                        fRival.value = response.rival || '';
                        form.appendChild(fRival);

                        // 确保first_user_id存在
                        var fFirstUserId = document.createElement('input');
                        fFirstUserId.type = 'hidden';
                        fFirstUserId.name = 'first_user_id';
                        fFirstUserId.value = response.first_user_id || response.user_id;
                        form.appendChild(fFirstUserId);
                          
                        document.body.appendChild(form);
                        form.submit();
                    }

                }
            }
        }
        timer1=setInterval(checkUserStatus,2000);        
        /*
        function checkUserCount()
        {
            console.log("time called");            
            timeCur=timeObj.getTime();  
            Count1=Count1+1;          
            if(false)
            {
                console.log("time up!");
                user_div=document.getElementById("div_user");
                user_div.style.left = "-200px"; 
                div_img1=document.getElementById("div_img1");
                div_img1.style.left = "-200px";
                div_img2=document.getElementById("div_img2");
                div_img2.style.left ="-200px";
                div_img2.style.top="800px";
                Count1=-1000;
                clearInterval(timer1);
            }       
            if(Count1>=5)   
            {
                console.log("time up!");
                window.location.href="exampleroom2.php";
            }
        }
        */
    </script>  
</body>
</html>
