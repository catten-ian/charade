@echo off
REM 设置MariaDB的头文件和库文件路径
echo 设置MariaDB环境变量...
set MARIADB_INCLUDE=E:\software\wampsever\bin\mariadb\mariadb11.3.2\include
set MARIADB_LIB=E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib

REM 设置PATH环境变量，确保程序能找到MariaDB的DLL文件
set PATH=%MARIADB_LIB%;%PATH%

REM 编译reset_room.cpp程序
echo 使用MariaDB库编译reset_room.cpp...
g++ reset_room.cpp -o reset_room.exe -I%MARIADB_INCLUDE% -L%MARIADB_LIB% -lmysql

REM 检查编译是否成功
if %ERRORLEVEL% NEQ 0 (
    echo 编译失败！错误码: %ERRORLEVEL%
    pause
    exit /b %ERRORLEVEL%
)

echo 编译成功！

echo. 
echo ====== 测试说明 ======
echo 这个批处理文件将运行reset_room.exe来测试其功能
echo 请确保：
echo 1. WAMP服务器已启动，数据库服务正常运行
echo 2. 数据库中有要测试的房间数据
echo. 

REM 询问用户是否要输入房间ID进行测试
echo 请输入要测试的房间ID（留空使用示例房间ID 'example room'）: 
set /p ROOM_ID=

REM 如果用户没有输入，则使用默认房间ID
if "%ROOM_ID%"=="" (
    set ROOM_ID=example room
    echo 未输入房间ID，将使用默认房间ID: example room
)

REM 运行测试
echo. 
echo 运行reset_room.exe测试，房间ID: %ROOM_ID%
echo ===========================
reset_room.exe %ROOM_ID%

echo. 
echo 程序退出代码: %ERRORLEVEL%
echo. 

echo ====== 测试结果说明 ======
echo 如果输出显示 "成功更新房间状态"，则说明程序正常工作
echo 如果输出显示 "房间状态不为3，无需重置"，则说明房间状态不是需要重置的状态
echo 如果输出显示错误信息，请检查数据库连接配置和房间ID是否正确

echo. 
echo 按任意键退出...
pause > nul