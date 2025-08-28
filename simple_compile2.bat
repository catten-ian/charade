@echo off
REM 非常简单的编译脚本

g++ -o reset_room.exe reset_room.cpp -IE:\software\wampsever\bin\mariadb\mariadb11.3.2\include\mysql -LE:\software\wampsever\bin\mariadb\mariadb11.3.2\lib -llibmariadb

IF %ERRORLEVEL% EQU 0 (
    ECHO Compilation successful!
) ELSE (
    ECHO Compilation failed!
)

pause