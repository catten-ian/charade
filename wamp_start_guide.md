# WAMP服务器启动指南

要正确运行猜词游戏和修复脚本，您需要确保WAMP服务器正在运行。以下是如何启动和确认WAMP服务器状态的步骤：

## 启动WAMP服务器

1. 找到桌面上的WAMP服务器图标，双击它启动服务器
   - 图标通常位于桌面，名称可能是"Wampserver"或"WAMP"
   - 或者您可以在开始菜单中搜索"WAMP"找到它

2. 观察任务栏中的WAMP图标状态：
   - 红色图标：服务器尚未启动
   - 橙色图标：服务器正在启动中
   - **绿色图标**：服务器已成功启动并运行

3. 如果图标一直是红色或橙色，请右键点击图标并选择"Start all services"（启动所有服务）

## 确认服务器正在运行

1. 打开任何浏览器（如Chrome、Firefox或Edge）

2. 在地址栏中输入：
   ```
   http://localhost/
   ```

3. 如果看到WAMP服务器的欢迎页面，则表示服务器已成功运行

## 测试游戏修复

1. 确认WAMP服务器已启动且图标为绿色

2. 在浏览器中访问修复脚本：
   ```
   http://localhost/charade/fix_room_status.php
   ```

3. 观察修复脚本的输出，确保没有错误

4. 打开两个不同的浏览器窗口（或使用隐私模式），分别访问：
   ```
   http://localhost/charade/login.html
   ```

5. 在两个窗口中分别登录catten和bear账号，观察是否能够成功配对并进入游戏

## 常见问题解决

- **如果WAMP图标一直是橙色**：
  - 右键点击图标，选择"Apache" > "Services" > "Install Service"
  - 同样操作安装MySQL/MariaDB服务
  - 重启WAMP服务器

- **如果无法访问localhost**：
  - 检查防火墙设置，确保80端口已开放
  - 确认没有其他程序占用80端口（如Skype、IIS等）
  - 右键点击WAMP图标，选择"Put Online"（联机）

- **如果数据库连接失败**：
  - 右键点击WAMP图标，选择"MariaDB" > "Services" > "Start/Resume Service"
  - 检查`db_config.ini`文件中的数据库配置是否正确

如果您遇到任何其他问题，请随时告诉我！