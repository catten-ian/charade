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
    guess    

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
  
        }
    </script>
</body>
</html>
