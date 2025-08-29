@echo off
REM Continuous run script for UserStatusUpdater
REM This script will keep running and execute UserStatusUpdater every 30 seconds

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

REM Compile the program if it doesn't exist or if source file is newer
if not exist user_status_updater.exe (
    echo Compiling UserStatusUpdater.cpp with MariaDB libraries...
    g++ -I%MARIADB_INCLUDE% -L%MARIADB_LIB% UserStatusUpdater.cpp -o user_status_updater.exe -lmariadb
    
    REM Check if compilation succeeded
    if %ERRORLEVEL% NEQ 0 (
        echo Compilation failed! Error code: %ERRORLEVEL%
        pause
        exit /b %ERRORLEVEL%
    )
    
    echo Compilation successful!
)

REM Display start message with timestamp
for /f "tokens=*" %%a in ('powershell -Command "Get-Date -Format 'yyyy-MM-dd HH:mm:ss'"') do set timestamp=%%a
echo ======================================
echo Continuous UserStatusUpdater Started at %timestamp%
echo Will execute every 30 seconds
echo Press Ctrl+C to stop

:loop
    REM Get current timestamp
    for /f "tokens=*" %%a in ('powershell -Command "Get-Date -Format 'yyyy-MM-dd HH:mm:ss'"') do set timestamp=%%a
    
    echo ======================================
echo Executing UserStatusUpdater at %timestamp%
    
    REM Run the program
    user_status_updater.exe
    
    echo Program exit code: %ERRORLEVEL%
    
    REM Wait for 30 seconds before next run
    echo Waiting 30 seconds for next execution...
    timeout /t 30 /nobreak >nul
    
    goto loop