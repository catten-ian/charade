document.addEventListener('DOMContentLoaded', function() {
    const userInput = document.getElementById('user-input');
    const syncedText = document.getElementById('synced-text');

    userInput.addEventListener('input', function() {
        syncedText.textContent = userInput.value || '请输入用户名';
    });
});
function jspost(URL, PARAMS) {
    var temp = document.createElement("form");
    temp.action = URL;
    temp.method = "post";
    //如需新打开窗口 form 的target属性要设置为'_blank'
    //temp.target = "_blank";
    temp.style.display = "none";
    for (var x in PARAMS) {
        var opt = document.createElement("textarea");
        opt.name = x;
        opt.value = PARAMS[x];
        temp.appendChild(opt);
    }
    document.body.appendChild(temp);
    temp.submit();
    return temp;
}

document.getElementById('topButton').addEventListener('click', function() {
    // 渐变效果
    var img = document.getElementById('topButton');
    img.style.transition = 'opacity 0.5s';
    img.style.opacity = '0';

    console.log("topButton Pressed");

    // 延迟跳转，以便渐变效果完成
    setTimeout(function() {
        //window.location.href = 'exampleroom.html'; // 跳转到新页面
        var username=document.getElementById("user-input").value;
        // var item1=document.getElementById("user-input");
        // console.log(item1.value);        
        var post_data={'username':username};
        //alert(post_data);
        jspost("exampleroom.php",post_data);
    }, 500);
});
// console.log("loaded");