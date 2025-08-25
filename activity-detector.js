// 获取当前页面的类型（从URL中提取）
function getPageType() {
    const path = window.location.pathname;
    if (path.includes('exampleroom2')) {
        return 'exampleroom2';
    } else if (path.includes('exampleroom')) {
        return 'exampleroom';
    } else if (path.includes('start')) {
        return 'start';
    } else if (path.includes('guess')) {
        return 'guess';
    } else if (path.includes('choose')) {
        return 'choose';
    } else if (path.includes('right')) {
        return 'right';
    } else if (path.includes('wrong')) {
        return 'wrong';
    } else if (path.includes('end')) {
        return 'end';
    } else if (path.includes('rest')) {
        return 'rest';
    } else if (path.includes('waiting')) {
        return 'waiting';
    } else if (path.includes('describe')) {
        return 'describe';
    }
    return 'other';
}

// 用户名应该在引用此脚本的页面中定义为全局变量
// 例如: var username = "<?php echo $_SESSION['username']; ?>";
var isUserActive = true; // 标记用户是否有操作
var heartbeatInterval;
var currentPageType = getPageType(); // 获取当前页面类型

// 监听用户操作（点击、键盘、滚动）
let debounceTimer = null;

function resetInactivityTimer() {
    // 防抖处理：短时间内多次调用只执行最后一次
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        // 只有状态从非活跃变为活跃时才执行
        if (!isUserActive) {
            isUserActive = true;
            sendHeartbeat(); // 发送心跳
        }
    }, 300); // 300ms防抖间隔，可根据需求调整
}

document.addEventListener('click', resetInactivityTimer);
document.addEventListener('keypress', resetInactivityTimer);
document.addEventListener('scroll', resetInactivityTimer);

// 定时发送心跳（30秒一次）
function startHeartbeat() {
    heartbeatInterval = setInterval(() => {
        sendHeartbeat();
    }, 30000); // 30秒
}

// 发送心跳到后端
function sendHeartbeat() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/charade/heartbeat.php');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    // 只需发送必要的活动状态和页面类型信息，用户身份已通过SESSION维护
    xhr.send(`is_active=${isUserActive ? 1 : 0}&page_type=${encodeURIComponent(currentPageType)}`);
    
    // 重置非活跃标记（下次心跳前无操作则为false）
    isUserActive = false;
}

// 页面加载时启动心跳
window.onload = startHeartbeat;

// 页面关闭时发送离线通知
window.onbeforeunload = () => {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/charade/heartbeat.php', false); // 同步请求
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    // 只需发送离线状态和页面类型信息，用户身份已通过SESSION维护
    xhr.send(`is_online=0&page_type=${encodeURIComponent(currentPageType)}`);
};