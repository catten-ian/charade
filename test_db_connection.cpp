#include <iostream>
#include <mysql.h>
#include <string>
#include <fstream>
#include <chrono>
#include <thread>

using namespace std;

// 将错误信息写入文件和控制台
void logError(const string& message) {
    // 写入控制台
    cerr << "错误: " << message << endl;
    
    // 写入日志文件
    ofstream logFile("db_connection_log.txt", ios::app);
    if (logFile.is_open()) {
        time_t now = time(nullptr);
        char* dt = ctime(&now);
        // 移除换行符
        dt[strlen(dt) - 1] = '\0';
        logFile << "[" << dt << "] " << message << endl;
        logFile.close();
    }
}

// 将信息写入文件和控制台
void logInfo(const string& message) {
    // 写入控制台
    cout << message << endl;
    
    // 写入日志文件
    ofstream logFile("db_connection_log.txt", ios::app);
    if (logFile.is_open()) {
        time_t now = time(nullptr);
        char* dt = ctime(&now);
        // 移除换行符
        dt[strlen(dt) - 1] = '\0';
        logFile << "[" << dt << "] " << message << endl;
        logFile.close();
    }
}

int main() {
    // 清空之前的日志文件
    ofstream logFile("db_connection_log.txt", ios::trunc);
    logFile.close();
    
    logInfo("开始测试数据库连接...");
    
    // 初始化MySQL连接
    MYSQL* conn = mysql_init(nullptr);
    if (!conn) {
        logError("mysql_init() 失败");
        // 使用get_last_error而不是mysql_error，因为conn可能为null
        logError("MySQL错误码: " + to_string(mysql_errno(nullptr)));
        logInfo("按任意键退出...");
        system("pause");
        return 1;
    }
    
    logInfo("MySQL初始化成功，准备连接数据库...");
    
    // 设置连接参数（从config.inc和db_config.txt获取）
    const char* host = "localhost";
    const char* user = "charade";
    const char* password = "pwdtest1";
    const char* database = "chdb";
    unsigned int port = 3307;
    
    string params = "连接参数: ";
    params += "\n  主机: " + string(host);
    params += "\n  用户名: " + string(user);
    params += "\n  数据库: " + string(database);
    params += "\n  端口: " + to_string(port);
    logInfo(params);
    
    // 连接数据库
    if (!mysql_real_connect(conn, host, user, password, database, port, nullptr, 0)) {
        string errorMsg = "mysql_real_connect() 失败: " + string(mysql_error(conn));
        logError(errorMsg);
        logError("MySQL错误码: " + to_string(mysql_errno(conn)));
        mysql_close(conn);
        logInfo("按任意键退出...");
        system("pause");
        return 1;
    }
    
    logInfo("数据库连接成功！");
    
    // 尝试执行简单查询
    if (mysql_query(conn, "SELECT * FROM tb_user LIMIT 1")) {
        string errorMsg = "查询失败: " + string(mysql_error(conn));
        logError(errorMsg);
        logError("MySQL错误码: " + to_string(mysql_errno(conn)));
    } else {
        logInfo("查询成功，可以访问tb_user表");
    }
    
    // 清理资源
    mysql_close(conn);
    
    logInfo("测试完成，按任意键退出...");
    system("pause");
    
    return 0;
}