@echo off
REM Set MariaDB header and library paths
set MARIADB_INCLUDE=E:\software\wampsever\bin\mariadb\mariadb11.3.2\include
set MARIADB_LIB=E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib

REM Set PATH environment variable to ensure the program can find MariaDB DLL files
echo Setting environment variables...
set PATH=%MARIADB_LIB%;%PATH%

REM Compile the program
echo Compiling UserStatusUpdater.cpp with MariaDB libraries...
g++ UserStatusUpdater.cpp -o user_status_updater.exe -I%MARIADB_INCLUDE% -L%MARIADB_LIB% -lmysql

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

REM Display program exit code
echo Program exit code: %ERRORLEVEL%

REM Pause to view output
pause