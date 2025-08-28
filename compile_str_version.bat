@echo off
REM 编译支持字符串房间ID的reset_room_str.cpp

g++ -o reset_room_str.exe reset_room_str.cpp -IE:\software\wampsever\bin\mariadb\mariadb11.3.2\include\mysql -LE:\software\wampsever\bin\mariadb\mariadb11.3.2\lib -llibmariadb

IF %ERRORLEVEL% EQU 0 (
    ECHO 编译成功！
    ECHO 生成了支持字符串房间ID的reset_room_str.exe
) ELSE (
    ECHO 编译失败！
)

pause