#include <iostream>
#include <mysql.h>
#include <string>

using namespace std;

int main(int argc, char* argv[]) {
    // 检查是否提供了room_id参数
    if (argc < 2) {
        cout << "用法: reset_room.exe <room_id>" << endl;
        return 1;
    }
    
    // 解析room_id参数
    int room_id = atoi(argv[1]);
    if (room_id <= 0) {
        cout << "无效的room_id" << endl;
        return 1;
    }
    
    // 数据库连接信息
    const char* host = "localhost";
    const char* user = "root";        // 假设使用root用户
    const char* password = "";       // 假设密码为空
    const char* database = "charade";
    unsigned int port = 3306;
    
    // 创建数据库连接
    MYSQL* conn = mysql_init(NULL);
    
    if (conn == NULL) {
        cout << "初始化数据库连接失败" << endl;
        return 1;
    }
    
    // 连接到数据库
    if (!mysql_real_connect(conn, host, user, password, database, port, NULL, 0)) {
        cout << "连接数据库失败: " << mysql_error(conn) << endl;
        mysql_close(conn);
        return 1;
    }
    
    cout << "成功连接到数据库" << endl;
    
    // 检查当前房间状态
    string check_sql = "SELECT status, round FROM tb_room WHERE id = " + to_string(room_id);
    if (mysql_query(conn, check_sql.c_str())) {
        cout << "查询房间信息失败: " << mysql_error(conn) << endl;
        mysql_close(conn);
        return 1;
    }
    
    MYSQL_RES* result = mysql_store_result(conn);
    if (result == NULL) {
        cout << "获取查询结果失败: " << mysql_error(conn) << endl;
        mysql_close(conn);
        return 1;
    }
    
    MYSQL_ROW row = mysql_fetch_row(result);
    if (row == NULL) {
        cout << "未找到指定房间ID" << endl;
        mysql_free_result(result);
        mysql_close(conn);
        return 1;
    }
    
    // 获取当前状态和轮数
    int current_status = atoi(row[0]);
    int current_round = atoi(row[1]);
    
    mysql_free_result(result);
    
    // 检查状态是否为3
    if (current_status == 3) {
        // 重置状态为2，并增加轮数
        int new_round = current_round + 1;
        string update_sql = "UPDATE tb_room SET status = 2, round = " + to_string(new_round) + " WHERE id = " + to_string(room_id);
        
        if (mysql_query(conn, update_sql.c_str())) {
            cout << "更新房间信息失败: " << mysql_error(conn) << endl;
            mysql_close(conn);
            return 1;
        }
        
        // 检查受影响的行数
        if (mysql_affected_rows(conn) > 0) {
            cout << "成功重置房间状态和增加轮数: room_id=" << room_id 
                 << ", 旧状态=" << current_status << ", 新状态=2" 
                 << ", 旧轮数=" << current_round << ", 新轮数=" << new_round << endl;
        } else {
            cout << "更新房间信息未影响任何行" << endl;
            mysql_close(conn);
            return 1;
        }
    } else {
        cout << "房间状态不是3，无需更新: room_id=" << room_id << ", 当前状态=" << current_status << endl;
    }
    
    // 关闭数据库连接
    mysql_close(conn);
    
    return 0;
}