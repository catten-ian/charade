<?php
    // Start the session
    session_start();
    //$_SESSION['postdata']=json_decode($_POST);
    $_SESSION['username'] = $_POST['username'];

    include "../config.inc";

    $conn=mysqli_connect("localhost",$db_user,$db_password,$db_name, $db_port);
    if(mysqli_connect_errno())
    {
        echo "connect db failed:".mysqli_connect_error();
    }

    $username=$_SESSION['username'];
    mysqli_set_charset($conn,"utf8");
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
</head>

<body bgcolor="#1270F8">
    <img src="./example2.png" height="100%" style="z-index:-1;filter:brightness(50%)">
    <img src="./room2.png"  style="position:absolute;left:90vw;top:0vh" />
    <img src="./room4.png"  style="position:absolute;left:0vw;top:86vh;overflow:hidden" />
    <img src="./Picture5.png"  style="position:absolute;left:90vw;top:86vh;overflow:hidden" />
    <img src="./Picture6.png"  style="position:absolute;left:5vw;top:5vh;overflow:hidden;width:3%" />
    <img src="./Picture8.png"  style="position:absolute;left:5vw;top:9vh;width:20%;filter: invert(100%);" />    
    <p style="font-size: calc(2vw + 2vh);position:absolute;left:12vw;top:-3vh;color:white;">Cipher</p>
    <p style="font-size: calc(1.5vw + 1.5vh);position:absolute;left:11vw;top:9vh;color:white;text-shadow: rgba(255, 255, 255, 0.4) 5px 5px ;">666666</p>
    <div class="centered">
    </div>
    <div id="div_user" style="position:absolute;">
        <table style="position:absolute;left:30vw;top:-60vh;">
            <tr style="position:absolute;scale:0.8"><td><img src="./avatarexample.png" id="div_img1" style="position:relative;left:0vw;top:80vh;" /></td></tr>
            <tr style="position:absolute;"><td>
                <div id="div_img2" style="position:relative;top=80vh;">
                    <svg width="200" height="400" style="position:absolute;margin:auto;left:80px">
                        <path id="curve" d="M10 80 Q 95 10 180 80" style="opacity:0%"/>
                        <text style="position:absolute;font-size:calc(2vw);margin:auto;">
                        <textPath xlink:href="#curve">&#8194; &ensp;
                            <?php 
                                $name_len=strlen($_SESSION['username']);
                                $name_out="";
                                if ( $name_len < 3 ) 
                                {
                                    $name_out="&#8194; &ensp;".$_SESSION['username'];
                                }
                                elseif ( $name_len < 5 )
                                {
                                    $name_out="&#8194; &ensp;".$_SESSION['username'];
                                } 
                                else 
                                { 
                                    $name_out=$_SESSION['username'];
                                }
                                echo $name_out; ?>
                            </textPath>
                        </text>
                  </svg>
                </div> 
            </td></tr>
        </table>
        <img src="./room5.svg" style="z-index:-1;cale:70%;left:0;top:0;justify-content: center;align-items: center;" />
        
        
    </div>
    
      
    <script >
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth;
        <?php 
            $username=$_SESSION['username'];
            print("var username='$username';\n") ;
        ?>
        var response={"ret_code":4};
        function checkUserStatus()
        {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/charade/paring.php');
            xhr.setRequestHeader('Conten-Type', 'application/x-www-form-urlencoded');
            var fmData=new FormData();
            fmData.append('username',username);
            xhr.send(fmData);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log(xhr.responseText);
                    var response=JSON.parse(xhr.responseText);
                    if(response.ret_code==0)
                    {
                        clearInterval(timer1);

                        // debugger;
                        //window.location.href="exampleroom2.php";
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

                        var fRoom = document.createElement('input');
                        fRoom.type = 'hidden';
                        fRoom.name = 'room';
                        fRoom.value = response.room;
                        form.appendChild(fRoom);

                        var fRivalId = document.createElement('input');
                        fRivalId.type = 'hidden';
                        fRivalId.name = 'rival_id';
                        fRivalId.value = response.rival_id;
                        form.appendChild(fRivalId);

                        var fRival = document.createElement('input');
                        fRival.type = 'hidden';
                        fRival.name = 'rival';
                        fRival.value = response.rival;
                        form.appendChild(fRival);

                        var fFirstUserId = document.createElement('input');
                        fFirstUserId.type = 'hidden';
                        fFirstUserId.name = 'first_user_id';
                        fFirstUserId.value = response.first_user_id;
                        form.appendChild(fFirstUserId);
                        
                        document.body.appendChild(form);
                        form.submit();
                    }

                }
            }
        }
        timer1=setInterval(checkUserStatus,1000);        
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
