@echo off

REM Compile the English version of reset_room_str program
g++ reset_room_str_en.cpp -o reset_room_str_en.exe -I"e:\software\wampsever\bin\mysql\mysql8.3.0\include" -L"e:\software\wampsever\bin\mysql\mysql8.3.0\lib" -llibmysql

REM Check if compilation succeeded
if %errorlevel% equ 0 (
echo Compilation successful! reset_room_str_en.exe has been created.
echo You can run it using: reset_room_str_en.exe <room_id>
) else (
echo Compilation failed with error code: %errorlevel%
)

REM Pause to see the output
pause