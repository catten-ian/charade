@echo off
REM Set MySQL client library path
echo Setting environment variables...
set PATH=e:\software\wampsever\bin\mysql\mysql8.3.0\lib;%PATH%

REM Run the program
echo Running user_status_updater.exe...
user_status_updater.exe

REM Show exit code
echo Exit code: %ERRORLEVEL%

REM Pause to view output
pause