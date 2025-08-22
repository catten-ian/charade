<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Charades!</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      width: 100vw;
      height: 100vh;
      position: relative;
      overflow: hidden;
    }

    img {
      position: absolute;
      object-fit: contain;
    }

    /* 右上角 room2.png */
    #room2 {
      left: calc(90% ); /* 简单处理，让左边缘约为图片宽度的90%，可根据实际需求更精准计算 */
      width: 8vw;
      height: auto;
    }

    /* 左下角 room4.png */
    #room4 {
      left: 0;
      bottom: 0;
      width: 8vw;
      height: auto;
    }

    /* 右下角 Picture5.png */
    #picture5 {
      right: 5%;
      bottom: 5%;
      width: 9vw;
      height: auto;
    }

    /* 左上角 Picture6.png */
    #picture6 {
      left: 2%;
      top: 1%;
      width: 6vw;
      height: auto;
    }

    /* 左上角 Picture8.png */
    #picture8 {
      left: 4%;
      top: 3%;
      width: 40vw;
      height: auto;
    }

    /* 中间左右 room5 容器 */
   .room5-wrap {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 80vw;
      height: auto;
    }

    #room5-left {
      left: 20%;
    }

    #room5-right {
      right: 20%;
    }

    /* room5 相关样式，假设是 SVG ，内部处理文字沿弧形 */
    .room5-svg {
      width: 100%;
      height: auto;
      position: relative;
    }

    /* 头像 avatarexample.png 样式 */
   .avatar {
      position: absolute;
      top: 20%; /* 大概在圆环内位置，可根据实际调整 */
      left: 50%;
      transform: translateX(-50%);
      width: 30%;
      height: auto;
    }

    /* SVG 文字样式 */
    svg text {
      font-family: sans-serif;
      fill: #000;
      text-anchor: middle;
      dominant-baseline: middle;
    }
  </style>
</head>

<body>
  <!-- 右上角 room2.png -->
  <img src="room2.png" alt="room2" id="room2">
  <!-- 左下角 room4.png -->
  <img src="./room4.png"  style="position:absolute;left:-8vw;bottom:-19vh;overflow:hidden;width:21vw;z-index:1;transform: rotate(268deg);" /> <!-- downleft -->
  <!-- 右下角 Picture5.png -->
  <img src="Picture5.png" alt="Picture5" id="picture5">
  <!-- 左上角 Picture6.png -->
  <img src="Picture6.png" alt="Picture6" id="picture6">
  <!-- 左上角 Picture8.png -->
  <img src="Picture8.png" alt="Picture8" id="picture8">

  <!-- 左侧 room5 区域 -->
  <div class="room5-wrap" id="room5-left" style="left:300px;top:90vh;">
    <svg class="room5-svg" viewBox="0 0 300 300">
      <!-- 引入 room5 的 SVG 内容，假设是外部文件 -->
      <use xlink:href="./room5.svg" x="-15vw" y="0vh" style="scale:15%;"></use> 
      <!-- 头像 -->
      <image href="avatarexample.png" x="12.5vw" y="11vh" style="left:10vw;scale:12%;" />
      <!-- 弧形路径，需根据 room5 实际弧形绘制，这里模拟 -->
      <path id="left-arc" d="M 5 80 Q 50 20 95 80" fill="none" transform="translate(3, 32)" stroke="none" />
      <text>
        <textPath xlink:href="#left-arc" startOffset="50%">abcdef</textPath>
      </text>
    </svg>
  </div>

  <!-- 右侧 room5 区域 -->
  <div class="room5-wrap" id="room5-right" style="left:50vw;top:90vh;">
    <svg class="room5-svg" viewBox="0 0 300 300">
      <use xlink:href="room5.svg" x="-15vw" y="0vh" style="scale:15%;" ></use> 
      <image href="avatarexample.png" x="12.5vw" y="11vh" style="left:10vw;scale:12%;" />
      <path id="right-arc" d="M 5 80 Q 50 20 95 80" fill="none" transform="translate(3,32)" stroke="none" />
      <text>
        <textPath xlink:href="#right-arc" startOffset="50%">abcdef</textPath>
      </text>
    </svg>
  </div>

  <script>
    // 后期动态修改文字示例
    // 比如修改左侧文字
    const leftText = document.querySelector('#left-arc + text textPath');
    // leftText.textContent = '新文字';
    // 右侧同理
    const rightText = document.querySelector('#right-arc + text textPath');
    // rightText.textContent = '新文字';
  </script>
</body>

</html>