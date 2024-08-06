<?php
    // Start the session
    session_start();
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
    <img src="./start1.svg" height="100%" style="position:fixed;z-index:-1;filter:brightness(50%)">
    <img src="./start2.svg" style="position:fixed;float:left;left:0vw;top:0vh;">
    <img src="./start4.svg" style="position:fixed;float:right;right:0vw;top:0vh">
    <img src="./start5.svg" style="position: absolute; bottom: 0px; right: 0px;">
    <img src="./start3.png" style="position: absolute; bottom: 0px; left: 0px;">
    <img src="./start6.svg" style="transform: translateX(50%);transition: transform 0.1s linear 2s;">

    <script>
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
            timeCur=timeObj.getTime();  
            Count1=Count1+1;          
            if(Count1>=5)
            {
                /* stop timer */
                Count1=-1000;
                clearInterval(timer1);                
                console.log("time up!");
                
                if(user_id==first_user_id)
                {
                    window.location.href="choose.php";
                }
                else
                {
                    window.location.href="guess.php";
                }
            }   
        }
    </script>
</body>
</html>
