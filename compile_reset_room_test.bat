@echo off
REM 编译测试脚本，使用正确的MariaDB路径编译reset_room.cpp

REM 设置MariaDB的头文件和库文件路径
set "MARIADB_INCLUDE=E:\software\wampsever\bin\mariadb\mariadb11.3.2\include"
set "MARIADB_LIB=E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib"

REM 设置PATH环境变量
set "PATH=%MARIADB_LIB%;%PATH%"

REM 检查头文件和库文件路径是否存在
echo 检查MariaDB头文件路径: %MARIADB_INCLUDE%
if exist "%MARIADB_INCLUDE%\mysql.h" (
    echo ✓ mysql.h 头文件存在
) else (
    echo ✗ 找不到mysql.h头文件，路径: %MARIADB_INCLUDE%
    pause
    exit /b 1
)

echo 检查MariaDB库文件路径: %MARIADB_LIB%
if exist "%MARIADB_LIB%\libmysql.dll" (
    echo ✓ libmysql.dll 库文件存在
) else (
    echo ✗ 找不到libmysql.dll库文件，路径: %MARIADB_LIB%
    pause
    exit /b 1
)

echo.
echo 开始编译reset_room.cpp...

REM 执行编译命令
g++ reset_room.cpp -o reset_room.exe -I"%MARIADB_INCLUDE%" -L"%MARIADB_LIB%" -lmysql

REM 检查编译结果
if %ERRORLEVEL% EQU 0 (
    echo.
echo ✓ 编译成功！生成了reset_room.exe
    echo 程序已准备好测试
) else (
    echo.
echo ✗ 编译失败！错误码: %ERRORLEVEL%
    echo 请检查reset_room.cpp文件是否正确，或者MariaDB库是否正确安装
)

pause