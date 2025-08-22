@echo off
REM 设置MariaDB的DLL文件路径到PATH环境变量
echo 设置环境变量...
set PATH=E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib;%PATH%

REM 运行程序
echo 正在运行user_status_updater.exe...
echo 请查看程序输出，检查数据库连接是否成功
user_status_updater.exe

REM 显示程序退出代码
echo 程序退出代码: %ERRORLEVEL%

REM 暂停以便查看输出
pause