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

    mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{
            @font-face {
                font-family: 'JiangXiZuoHei';
                src: url('./title.ttf') format('truetype');
                font-weight: normal;
                font-style: normal;
            }
        }
    </style>
    <title>Choose A Word</title>

    
</head>
<body bgcolor="#1270F8" style="overflow:hidden;">
    <form id="wordForm" action="describe.php" method="post">
        <input type="hidden" id="selectedWord" name="word">
    </form>

    <!-- <img src="./example4.png" style="left:0px;top:0px;z-index:-1;filter:brightness(50%);width:100vw;height:100vh;overflow:hidden;"> -->
    <!-- 四个角落的图片 -->
    <img src="./Choose3.svg"  style="position:absolute;left:62vw;top:0vh;overflow:hidden; width:38%;height:39%;z-index:0;opacity: 1;" /> <!-- topright -->
    <img src="./Choose4.svg"  style="position:absolute;left:0vw;top:56vh;overflow:hidden; width:46%;z-index:0;opacity: 1;" /> <!-- downleft -->
    <img src="./Choose2.svg"  style="position:absolute;left:2.5vw;top:-6.8vh;overflow:hidden;width:38%; z-index:0;filter: invert(100%);" /><!-- topleft-->
    <img src="./Choose1.svg"  style="position:absolute;left:71.8vw;top:70.8vh;width:28%;overflow:hidden;z-index:0;opacity: 1;" />  <!-- downright -->
    
    <div onclick="document.getElementById('selectedWord').value='<?php echo $word1; ?>'; document.getElementById('wordForm').submit();" style="position:absolute;left:23vw;top:25vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $word1; ?></p>
    </div>

    <div onclick="document.getElementById('selectedWord').value='<?php echo $word2; ?>'; document.getElementById('wordForm').submit();" style="position:absolute;left:56vw;top:25vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $word2; ?></p>
    </div>

    <div onclick="document.getElementById('selectedWord').value='<?php echo $word3; ?>'; document.getElementById('wordForm').submit();" style="position:absolute;left:23vw;top:54vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $word3; ?></p>
    </div>

    <div onclick="document.getElementById('selectedWord').value='<?php echo $word4; ?>'; document.getElementById('wordForm').submit();" style="position:absolute;left:56vw;top:54vh;width:23vw;height:20vh;z-index:0;justify-content:center;align-items:center;cursor: pointer;">
        <img src="./choose5.svg" style="position:absolute;height:26vh;width:23vw;"/> 
        <p style="position:absolute;width:23vw;height:20vh;text-align:center;line-height:5vh;font-family: 'JiangXiZuoHei', sans-serif;white-space:nowrap;font-size:5vw;color:black;z-index:5;"><?php echo $word4; ?></p>
    </div>
</body>
</html>
    