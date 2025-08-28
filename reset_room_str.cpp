#include <mysql.h> // MariaDB兼容MySQL的API
#include <iostream>
#include <fstream>
#include <string>
#include <map>
#include <cstdlib>
#include <sstream>

using namespace std;

// 数据库配置结构体
struct DBConfig {
    string host;
    string user;
    string password;
    string database;
    int port;
};

// 从PHP配置文件读取键值对
std::map<std::string, std::string> readPHPConfigFile(const std::string& filename) {
    std::map<std::string, std::string> config;
    std::ifstream file(filename);

    if (!file.is_open()) {
        std::cerr << "无法打开PHP配置文件: " << filename << std::endl;
        return config;
    }

    std::string line;
    while (std::getline(file, line)) {
        // 跳过注释和空行
        if (line.empty() || line[0] == ';' || line.substr(0, 2) == "//") {
            continue;
        }

        // 查找$变量定义
        size_t varPos = line.find('$');
        if (varPos != std::string::npos) {
            // 查找等号
            size_t equalPos = line.find('=', varPos);
            if (equalPos != std::string::npos) {
                // 查找分号或引号
                size_t endPos = line.find(';', equalPos);
                if (endPos == std::string::npos) {
                    endPos = line.size();
                }

                // 提取变量名
                std::string varName = line.substr(varPos + 1, equalPos - varPos - 1);
                // 去除变量名中的空格和特殊字符
                varName.erase(0, varName.find_first_not_of(" \t"));
                varName.erase(varName.find_last_not_of(" \t") + 1);

                // 提取值（考虑引号）
                std::string value = line.substr(equalPos + 1, endPos - equalPos - 1);
                // 去除值中的空格、引号和特殊字符
                value.erase(0, value.find_first_not_of(" \t'\""));
                value.erase(value.find_last_not_of(" \t'\";\r\n") + 1);

                // 存储键值对
                config[varName] = value;
            }
        }
    }

    file.close();
    return config;
}

// 读取INI配置文件函数（保留用于兼容性）
std::map<std::string, std::map<std::string, std::string>> readConfigFile(const std::string& filename) {
    std::map<std::string, std::map<std::string, std::string>> config;
    std::string currentSection;
    std::ifstream file(filename);

    if (!file.is_open()) {
        std::cerr << "无法打开配置文件: " << filename << std::endl;
        return config;
    }

    std::string line;
    while (std::getline(file, line)) {
        // 跳过注释和空行
        if (line.empty() || line[0] == ';' || line[0] == '#') {
            continue;
        }

        // 检查是否是节标题
        if (line[0] == '[' && line.back() == ']') {
            currentSection = line.substr(1, line.size() - 2);
            continue;
        }

        // 解析键值对
        size_t pos = line.find('=');
        if (pos != std::string::npos && !currentSection.empty()) {
            std::string key = line.substr(0, pos);
            std::string value = line.substr(pos + 1);

            // 去除键值对中的空格
            key.erase(0, key.find_first_not_of(" \t"));
            key.erase(key.find_last_not_of(" \t") + 1);
            value.erase(0, value.find_first_not_of(" \t"));
            value.erase(value.find_last_not_of(" \t") + 1);

            config[currentSection][key] = value;
        }
    }

    file.close();
    return config;
}

// 从PHP配置文件获取数据库配置
DBConfig getDBConfig() {
    DBConfig config;
    
    // 先尝试从PHP配置文件读取
    auto configMap = readPHPConfigFile("../config.inc");
    if (!configMap.empty()) {
        config.host = configMap.count("db_host") ? configMap["db_host"] : (configMap.count("db_server") ? configMap["db_server"] : "localhost");
        config.user = configMap.count("db_user") ? configMap["db_user"] : "charade";
        config.password = configMap.count("db_password") ? configMap["db_password"] : "pwdtest1";
        config.database = configMap.count("db_name") ? configMap["db_name"] : (configMap.count("db_database") ? configMap["db_database"] : "chdb");
        config.port = configMap.count("db_port") ? std::stoi(configMap["db_port"]) : 3307;
    } else {
        // 如果PHP配置文件读取失败，回退到INI文件
        std::cout << "PHP配置文件读取失败，尝试读取INI配置文件" << std::endl;
        auto configMapIni = readConfigFile("db_config.ini");
        if (configMapIni.count("database")) {
            auto& dbSection = configMapIni["database"];
            
            config.host = dbSection.count("host") ? dbSection["host"] : "localhost";
            config.user = dbSection.count("user") ? dbSection["user"] : "charade";
            config.password = dbSection.count("password") ? dbSection["password"] : "pwdtest1";
            config.database = dbSection.count("database") ? dbSection["database"] : "chdb";
            config.port = dbSection.count("port") ? std::stoi(dbSection["port"]) : 3307;
        } else {
            // 配置文件不存在或格式错误时，使用环境变量或默认值
            std::cout << "配置文件不存在或格式错误，尝试使用环境变量或默认值" << std::endl;
            config.host = getenv("DB_HOST") ? getenv("DB_HOST") : "localhost";
            config.user = getenv("DB_USER") ? getenv("DB_USER") : "charade";
            config.password = getenv("DB_PASSWORD") ? getenv("DB_PASSWORD") : "pwdtest1";
            config.database = getenv("DB_NAME") ? getenv("DB_NAME") : "chdb";
            config.port = getenv("DB_PORT") ? stoi(getenv("DB_PORT")) : 3307;
        }
    }
    
    return config;
}

// 初始化数据库连接（使用MariaDB）
MYSQL* initDBConnection(const DBConfig& config) {
    MYSQL* conn = mysql_init(nullptr);
    if (!conn) {
        cerr << "mysql_init failed" << endl;
        return nullptr;
    }
    
    // 设置连接超时
    mysql_options(conn, MYSQL_OPT_CONNECT_TIMEOUT, "30");
    
    // 连接MariaDB数据库
    if (!mysql_real_connect(conn, config.host.c_str(), config.user.c_str(),
        config.password.c_str(), config.database.c_str(),
        config.port, nullptr, 0)) {
        cerr << "Connection failed: " << mysql_error(conn) << endl;
        cerr << "Error code: " << mysql_errno(conn) << endl;
        mysql_close(conn);
        return nullptr;
    }
    
    return conn;
}

int main(int argc, char* argv[]) {
    // 检查是否提供了room_id参数
    if (argc < 2) {
        cout << "用法: reset_room_str.exe <room_id>" << endl;
        cout << "注意: room_id可以是字符串类型" << endl;
        return 1;
    }
    
    // 直接使用字符串类型的room_id参数
    string room_id = argv[1];
    if (room_id.empty()) {
        cout << "无效的room_id" << endl;
        return 1;
    }
    
    // 获取配置
    DBConfig dbConfig = getDBConfig();
    // 打印数据库连接信息（用于调试）
    cout << "连接数据库: " << dbConfig.host << ":" << dbConfig.port << endl;
    cout << "数据库名: " << dbConfig.database << endl;
    cout << "用户名: " << dbConfig.user << endl;
    
    // 初始化数据库连接
    MYSQL* conn = initDBConnection(dbConfig);
    if (!conn) {
        cerr << "数据库连接失败，按任意键继续..." << endl;
        system("pause");
        return 1;
    }

    cout << "数据库连接成功！" << endl;
    
    // 检查当前房间状态（注意：这里使用字符串类型的room_id，需要用单引号包裹）
    string check_sql = "SELECT status, round FROM tb_room WHERE id = '" + room_id + "'";
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
        cout << "未找到指定房间ID: " << room_id << endl;
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
        string update_sql = "UPDATE tb_room SET status = 2, round = " + to_string(new_round) + " WHERE id = '" + room_id + "'";
        
        if (mysql_query(conn, update_sql.c_str())) {
            cout << "更新房间信息失败: " << mysql_error(conn) << endl;
            mysql_close(conn);
            return 1;
        }
        
        // 检查受影响的行数
        if (mysql_affected_rows(conn) > 0) {
            cout << "成功重置房间状态和增加轮数: room_id='" << room_id 
                 << "', 旧状态=" << current_status << ", 新状态=2" 
                 << ", 旧轮数=" << current_round << ", 新轮数=" << new_round << endl;
        } else {
            cout << "更新房间信息未影响任何行" << endl;
            mysql_close(conn);
            return 1;
        }
    } else {
        cout << "房间状态不是3，无需更新: room_id='" << room_id << "', 当前状态=" << current_status << endl;
    }
    
    // 关闭数据库连接
    mysql_close(conn);
    
    return 0;
}