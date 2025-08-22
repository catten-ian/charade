@echo off
REM 设置MariaDB库路径
set PATH=%PATH%;E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib

REM 运行用户状态更新程序
user_status_updater.exe

REM 显示更新结果
 echo 用户状态更新完成！
 echo 按任意键退出...
 pause > nul