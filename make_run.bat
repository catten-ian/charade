@echo off

REM Simple batch file to compile and run UserStatusUpdater using Makefile settings

REM Set MariaDB header and library paths
echo Setting MariaDB paths...
set MARIADB_INCLUDE=E:\software\wampsever\bin\mariadb\mariadb11.3.2\include\mysql
set MARIADB_LIB=E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib

REM Set PATH environment variable
echo Setting environment variables...
set PATH=%MARIADB_LIB%;%PATH%

REM Check if g++ is available
echo Checking for g++ compiler...
g++ --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Error: g++ compiler not found. Please install MinGW or another C++ compiler.
    pause
    exit /b 1
)

REM Compile the program
echo Compiling UserStatusUpdater.cpp with MariaDB libraries...
g++ -I%MARIADB_INCLUDE% -L%MARIADB_LIB% UserStatusUpdater.cpp -o user_status_updater.exe -lmariadb

REM Check if compilation succeeded
if %ERRORLEVEL% NEQ 0 (
    echo Compilation failed! Error code: %ERRORLEVEL%
    pause
    exit /b %ERRORLEVEL%
)

echo Compilation successful!

REM Run the program
echo Running user_status_updater.exe...
user_status_updater.exe

echo Program exit code: %ERRORLEVEL%

REM Pause to view output
pause >nul | set /p="Press any key to continue . . . "