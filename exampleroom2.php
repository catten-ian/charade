<?php
    // Start the session
    session_start();
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['user_id'] = $_POST['user_id'];
    $_SESSION['room'] = $_POST['room'];
    $_SESSION['rival'] = $_POST['rival'];
    $_SESSION['rival_id'] = $_POST['rival_id'];
    $_SESSION['first_user_id'] = $_POST['first_user_id'];
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
        <table style="position:absolute;left:0vw;top:-60vh;">
            <tr style="position:absolute;scale:0.8">
                <td><img src="./avatarexample.png" id="div_img1" style="position:relative;left:-55vw;top:40vh;" /></td>
                <td><img src="./avatarexample.png" id="div2_img1" style="position:relative;left:-20vw;top:40vh;" /></td>
            </tr>
            <tr style="position:absolute;">
                <td><div id="div_img2" style="position:relative;top:75vh;">
                    <svg width="400" height="400" style="position:absolute;margin:auto;left:-40vw">
                        <path id="curve" d="M10 80 Q 95 10 180 80" style="opacity:0%"/>
                        <text style="position:absolute;font-size:calc(2vw);margin:auto;">
                        <textPath xlink:href="#curve">
                            <?php                                 
                                $name_out="";
                                $leftName;
                                if($_SESSION['first_user_id']==$_SESSION['user_id'])
                                {
                                    $leftName=$_SESSION['username'];
                                }
                                else
                                {
                                    $leftName=$_SESSION['rival'];
                                }
                                $name_len=strlen($leftName);
                                if ( $name_len < 3 ) 
                                {
                                    $name_out="&#8194; &ensp;".$leftName;                                    
                                }
                                elseif ( $name_len < 5 )
                                {
                                    $name_out="&#8194; &ensp;".$leftName;
                                } 
                                else 
                                { 
                                    $name_out=$leftName;
                                }
                                echo $name_out; ?>
                            </textPath>
                        </text>
                  </svg>
                </div></td>
                <td><div id="div2_img2" style="position:relative;top:75vh;">
                    <svg width="400" height="400" style="position:absolute;margin:auto;left:2vw">
                        <path id="curve" d="M10 80 Q 95 10 180 80" style="opacity:0%"/>
                        <text style="position:absolute;font-size:calc(2vw);margin:auto;">
                        <textPath xlink:href="#curve">
                            <?php                                 
                                $name_out="";
                                $rightName;
                                if($_SESSION['first_user_id']==$_SESSION['user_id'])
                                {
                                    $rightName=$_SESSION['rival'];
                                }
                                else
                                {
                                    $rightName=$_SESSION['username'];
                                }
                                $name_len=strlen($rightName);
                                if ( $name_len < 3 ) 
                                {
                                    $name_out="&#8194; &ensp;".$rightName;
                                }
                                elseif ( $name_len < 5 )
                                {
                                    $name_out="&#8194; &ensp;".$rightName;
                                } 
                                else 
                                { 
                                    $name_out=$rightName;
                                }
                                echo $name_out; ?>
                            </textPath>
                        </text>
                  </svg>
                </div></td>
            </tr>
            <tr>
                <td>
                    <img src="./room5.svg" style="position:absolute;z-index:-1;cale:70%;left:-60vw;top:30vh;justify-content: center;align-items: center;" />
                </td>
                <td>
                    <img src="./room5.svg" style="position:absolute;z-index:-1;cale:70%;left:-20vw;top:30vh;justify-content: center;align-items: center;" />
                </td>
            </tr>
        </table>
        
        
        
    </div>
    
      
    <script >
        var timeObj=new Date();
        var startTimeInMs=timeObj.getTime();
        var timeCur;
        var Count1=0;
        var window_width=window.innerWidth;
        timer1=setInterval(checkUserCount,1000);    
        <?php 
            $username=$_SESSION['username'];
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
        function checkUserCount()
        {
            console.log("time called");            
            timeCur=timeObj.getTime();  
            Count1=Count1+1;          
            if(Count1>=5)
            {
                /* stop timer */
                Count1=-1000;
                clearInterval(timer1);                
                console.log("time up!");

                var form = document.createElement('form');
                form.method = 'post';
                form.action = 'game.php';
                var fUsername = document.createElement('input');
                fUsername.type = 'hidden';
                fUsername.name = 'username';
                fUsername.value = username;
                form.appendChild(fUsername);

                var fUserId = document.createElement('input');
                fUserId.type = 'hidden';
                fUserId.name = 'user_id';
                fUserId.value = user_id;
                form.appendChild(fUserId);

                var fRoom = document.createElement('input');
                fRoom.type = 'hidden';
                fRoom.name = 'room';
                fRoom.value = room;
                form.appendChild(fRoom);

                var fRivalId = document.createElement('input');
                fRivalId.type = 'hidden';
                fRivalId.name = 'rival_id';
                fRivalId.value = rival_id;
                form.appendChild(fRivalId);

                var fRival = document.createElement('input');
                fRival.type = 'hidden';
                fRival.name = 'rival';
                fRival.value = rival;
                form.appendChild(fRival);

                var fFirstUserId = document.createElement('input');
                fFirstUserId.type = 'hidden';
                fFirstUserId.name = 'first_user_id';
                fFirstUserId.value = first_user_id;
                form.appendChild(fFirstUserId);
                
                document.body.appendChild(form);
                form.submit();
            }          
        }
        
    </script>  
</body>
</html>
