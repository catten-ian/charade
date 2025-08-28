@echo off
REM 编译reset_room.cpp使用正确的MariaDB头文件和库路径

REM 设置正确的MariaDB路径
SET MARIADB_INCLUDE=E:\software\wampsever\bin\mariadb\mariadb11.3.2\include\mysql
SET MARIADB_LIB=E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib

REM 检查mysql.h是否存在
IF NOT EXIST "%MARIADB_INCLUDE%\mysql.h" (
    echo Error: mysql.h not found at %MARIADB_INCLUDE%\mysql.h
    pause
    exit /b 1
)

REM 检查库文件是否存在
IF NOT EXIST "%MARIADB_LIB%\libmariadb.lib" (
    echo Error: libmariadb.lib not found at %MARIADB_LIB%\libmariadb.lib
    pause
    exit /b 1
)

REM 编译reset_room.cpp
ECHO Compiling reset_room.cpp with correct paths...
g++ -o reset_room.exe reset_room.cpp -I"%MARIADB_INCLUDE%" -L"%MARIADB_LIB%" -llibmariadb

REM 检查编译结果
IF %ERRORLEVEL% EQU 0 (
    ECHO Compilation successful!
    ECHO reset_room.exe has been created.
    ECHO To test, run: reset_room.exe [room_id]
) ELSE (
    ECHO Compilation failed!
)

pause