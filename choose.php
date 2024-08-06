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
    // echo "随机选择的两个词是：" . $word1 . " 和 " . $word2;

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
    <img src="./choose1.svg" height="100%" style="position:fixed;z-index:-1;filter:brightness(50%)">
    <img src="./choose2svg" style="position:fixed;float:left;left:0vw;top:0vh;">
    <!-- <?php echo $word1. " 和 ". $word2;?> -->
    <span style="font-size: 80pt;font-family:'title.ttf';font-color:white;">
        <table>
            <tr><td>
                <?php echo $word1;?> 
            </td></tr>
            <tr><td>
                <?php echo $word2;?> 
            </td></tr>
            <tr><td>
                <?php echo $word3;?> 
            </td></tr>
            <tr><td>
                <?php echo $word4;?> 
            </td></tr>
        </table>
    </span>

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
