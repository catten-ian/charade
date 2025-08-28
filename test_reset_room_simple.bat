@echo off
REM 简化的测试脚本，用于编译和运行reset_room.cpp
set MARIADB_INCLUDE=E:\software\wampsever\bin\mariadb\mariadb11.3.2\include
set MARIADB_LIB=E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib
set PATH=%MARIADB_LIB%;%PATH%

echo Compiling reset_room.cpp...
g++ reset_room.cpp -o reset_room.exe -I%MARIADB_INCLUDE% -L%MARIADB_LIB% -lmysql

if %ERRORLEVEL% NEQ 0 (
    echo Compilation failed!
    pause
    exit /b %ERRORLEVEL%
)

echo Compilation successful!
echo Running reset_room.exe with test room ID 'example room'
reset_room.exe "example room"

pause