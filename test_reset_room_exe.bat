@echo off
REM 测试reset_room.exe可执行文件

ECHO 正在测试reset_room.exe...
ECHO 用法: reset_room.exe [room_id]
ECHO 当前使用默认测试房间ID: "test_room"

reset_room.exe test_room

IF %ERRORLEVEL% EQU 0 (
    ECHO 程序运行完成！
) ELSE (
    ECHO 程序运行失败，错误代码: %ERRORLEVEL%
)

pause