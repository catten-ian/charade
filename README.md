#charade
user type:1 online, 2 offline, 3 playing, 4 waiting,5 away, 6 idle
type 值	含义
1	缺省活动状态
2	60 分钟未响应
3	在游戏中
4	在房间中
5	脱离活动状态（3 分钟未响应或页面关闭）
6	浏览器打开但1分钟内无任何操作（包括鼠标移动、键盘输入）
room type:1 public, 2 private, 3 group
word type:1 animal, 2 object, 3 place, 4 thing, 5 person, 6 event, 7 idea, 8 feeling, 9 number, 10 time, 11 measurement, 12 feeling, 13 verb, 14 adjective, 15 random
difficulty level:1 easy, 2 medium, 3 hard

room status:0 default, 1 waiting, 2 playing, 3 finished, 4 closed
room status 值	含义
0	缺省状态
1	等待中
2	游戏中
3	已结束
4	已关闭

paring.php ret_code:0 pairing success, 1 pairing failed, 2 already paired, 3 already in room, 4 room full, 5 room not found, 6 room not public

# 每分钟执行一次状态更新
* * * * * /usr/bin/php /path/to/charade/cron/update_user_status.php
