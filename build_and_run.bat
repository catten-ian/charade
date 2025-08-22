@echo off
REM 设置MariaDB的头文件和库文件路径
set MARIADB_INCLUDE=E:\software\wampsever\bin\mariadb\mariadb11.3.2\include
set MARIADB_LIB=E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib

REM 设置PATH环境变量，确保程序能找到MariaDB的DLL文件
echo 设置环境变量...
set PATH=%MARIADB_LIB%;%PATH%

REM 编译程序
echo 使用MariaDB库编译UserStatusUpdater.cpp...
g++ UserStatusUpdater.cpp -o user_status_updater.exe -I%MARIADB_INCLUDE% -L%MARIADB_LIB% -lmysql

REM 检查编译是否成功
if %ERRORLEVEL% NEQ 0 (
    echo 编译失败！错误码: %ERRORLEVEL%
    pause
    exit /b %ERRORLEVEL%
)

echo 编译成功！

REM 运行程序
echo 运行user_status_updater.exe...
user_status_updater.exe

REM 显示程序退出代码
echo 程序退出代码: %ERRORLEVEL%

REM 暂停以便查看输出
pause