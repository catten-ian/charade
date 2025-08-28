@echo off
REM Very simple compile script for reset_room.cpp
set MARIADB_INCLUDE=E:\software\wampsever\bin\mariadb\mariadb11.3.2\include
set MARIADB_LIB=E:\software\wampsever\bin\mariadb\mariadb11.3.2\lib
echo Compiling reset_room.cpp...
g++ reset_room.cpp -o reset_room.exe -I%MARIADB_INCLUDE% -L%MARIADB_LIB% -lmysql
if exist reset_room.exe (
    echo Compilation succeeded!
) else (
    echo Compilation failed!
)
pause