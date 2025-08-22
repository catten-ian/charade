#include <iostream>
#include <mysql.h>
#include <fstream>

using namespace std;

int main() {
    // 创建日志文件（使用绝对路径）
    ofstream logFile("e:\\software\\wampsever\\www\\charade\\db_test_log.txt", ios::out);
    if (!logFile.is_open()) {
        cerr << "无法创建日志文件" << endl;
        system("pause");
        return 1;
    }
    
    logFile << "简单数据库连接测试开始" << endl;
    
    // 初始化MySQL库
    if (mysql_library_init(0, NULL, NULL)) {
        logFile << "mysql_library_init() 失败" << endl;
        logFile.close();
        cerr << "MySQL库初始化失败" << endl;
        system("pause");
        return 1;
    }
    
    logFile << "MySQL库初始化成功" << endl;
    
    // 初始化MySQL连接
    MYSQL* conn = mysql_init(nullptr);
    if (!conn) {
        logFile << "mysql_init() 失败" << endl;
        logFile.close();
        mysql_library_end();
        cerr << "MySQL连接初始化失败" << endl;
        system("pause");
        return 1;
    }
    
    logFile << "MySQL连接对象创建成功" << endl;
    
    // 设置连接参数
    const char* host = "localhost";
    const char* user = "charade";
    const char* password = "pwdtest1";
    const char* database = "chdb";
    unsigned int port = 3307;
    
    logFile << "尝试连接到: " << host << ":" << port << ", 数据库: " << database << ", 用户: " << user << endl;
    
    // 连接数据库
    if (!mysql_real_connect(conn, host, user, password, database, port, nullptr, 0)) {
        logFile << "mysql_real_connect() 失败: " << mysql_error(conn) << endl;
        logFile << "错误码: " << mysql_errno(conn) << endl;
        
        // 尝试不同的连接方式
        logFile << "尝试使用127.0.0.1替代localhost..." << endl;
        if (!mysql_real_connect(conn, "127.0.0.1", user, password, database, port, nullptr, 0)) {
            logFile << "使用127.0.0.1连接失败: " << mysql_error(conn) << endl;
        } else {
            logFile << "使用127.0.0.1连接成功！" << endl;
        }
        
        logFile.close();
        mysql_close(conn);
        mysql_library_end();
        cerr << "数据库连接失败，请查看日志文件了解详情" << endl;
        system("pause");
        return 1;
    }
    
    logFile << "数据库连接成功！" << endl;
    
    // 尝试简单查询
    if (mysql_query(conn, "SELECT 1")) {
        logFile << "简单查询失败: " << mysql_error(conn) << endl;
    } else {
        logFile << "简单查询成功" << endl;
    }
    
    // 清理资源
    mysql_close(conn);
    mysql_library_end();
    logFile << "测试完成" << endl;
    logFile.close();
    
    cout << "测试完成，请查看日志文件了解详情" << endl;
    system("pause");
    
    return 0;
}