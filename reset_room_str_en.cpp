#include <iostream>
#include <fstream>
#include <string>
#include <map>
#include <cstdlib>
#include <sstream>
#include <mysql.h>

using namespace std;

// Database configuration structure
struct DBConfig {
    string host;
    string user;
    string password;
    string database;
    int port;
};

// Read key-value pairs from PHP configuration file
std::map<std::string, std::string> readPHPConfigFile(const std::string& filename) {
    std::map<std::string, std::string> config;
    std::ifstream file(filename);

    if (!file.is_open()) {
        std::cerr << "Failed to open PHP configuration file: " << filename << std::endl;
        return config;
    }

    std::string line;
    while (std::getline(file, line)) {
        // Skip comments and empty lines
        if (line.empty() || line[0] == ';' || line.substr(0, 2) == "//") {
            continue;
        }

        // Find $ variable definition
        size_t varPos = line.find('$');
        if (varPos != std::string::npos) {
            // Find equal sign
            size_t equalPos = line.find('=', varPos);
            if (equalPos != std::string::npos) {
                // Find semicolon or quotation mark
                size_t endPos = line.find(';', equalPos);
                if (endPos == std::string::npos) {
                    endPos = line.size();
                }

                // Extract variable name
                std::string varName = line.substr(varPos + 1, equalPos - varPos - 1);
                // Remove spaces and special characters from variable name
                varName.erase(0, varName.find_first_not_of(" \t"));
                varName.erase(varName.find_last_not_of(" \t") + 1);

                // Extract value (considering quotes)
                std::string value = line.substr(equalPos + 1, endPos - equalPos - 1);
                // Remove spaces, quotes and special characters from value
                value.erase(0, value.find_first_not_of(" \t'\""));
                value.erase(value.find_last_not_of(" \t'\";\r\n") + 1);

                // Store key-value pair
                config[varName] = value;
            }
        }
    }

    file.close();
    return config;
}

// Read INI configuration file function (retained for compatibility)
std::map<std::string, std::map<std::string, std::string>> readConfigFile(const std::string& filename) {
    std::map<std::string, std::map<std::string, std::string>> config;
    std::string currentSection;
    std::ifstream file(filename);

    if (!file.is_open()) {
        std::cerr << "Failed to open configuration file: " << filename << std::endl;
        return config;
    }

    std::string line;
    while (std::getline(file, line)) {
        // Skip comments and empty lines
        if (line.empty() || line[0] == ';' || line[0] == '#') {
            continue;
        }

        // Check if it's a section header
        if (line[0] == '[' && line.back() == ']') {
            currentSection = line.substr(1, line.size() - 2);
            continue;
        }

        // Parse key-value pair
        size_t pos = line.find('=');
        if (pos != std::string::npos && !currentSection.empty()) {
            std::string key = line.substr(0, pos);
            std::string value = line.substr(pos + 1);

            // Remove spaces from key-value pair
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

// Get database configuration from PHP configuration file
DBConfig getDBConfig() {
    DBConfig config;
    
    // First try to read from PHP configuration file
    auto configMap = readPHPConfigFile("../config.inc");
    if (!configMap.empty()) {
        config.host = configMap.count("db_host") ? configMap["db_host"] : (configMap.count("db_server") ? configMap["db_server"] : "localhost");
        config.user = configMap.count("db_user") ? configMap["db_user"] : "charade";
        config.password = configMap.count("db_password") ? configMap["db_password"] : "pwdtest1";
        config.database = configMap.count("db_name") ? configMap["db_name"] : (configMap.count("db_database") ? configMap["db_database"] : "chdb");
        config.port = configMap.count("db_port") ? std::stoi(configMap["db_port"]) : 3307;
    } else {
        // If PHP configuration file reading fails, fall back to INI file
        std::cout << "Failed to read PHP configuration file, attempting to read INI configuration file" << std::endl;
        auto configMapIni = readConfigFile("db_config.ini");
        if (configMapIni.count("database")) {
            auto& dbSection = configMapIni["database"];
            
            config.host = dbSection.count("host") ? dbSection["host"] : "localhost";
            config.user = dbSection.count("user") ? dbSection["user"] : "charade";
            config.password = dbSection.count("password") ? dbSection["password"] : "pwdtest1";
            config.database = dbSection.count("database") ? dbSection["database"] : "chdb";
            config.port = dbSection.count("port") ? std::stoi(dbSection["port"]) : 3307;
        } else {
            // When configuration file does not exist or is formatted incorrectly, use environment variables or default values
            std::cout << "Configuration file does not exist or is formatted incorrectly, attempting to use environment variables or default values" << std::endl;
            config.host = getenv("DB_HOST") ? getenv("DB_HOST") : "localhost";
            config.user = getenv("DB_USER") ? getenv("DB_USER") : "charade";
            config.password = getenv("DB_PASSWORD") ? getenv("DB_PASSWORD") : "pwdtest1";
            config.database = getenv("DB_NAME") ? getenv("DB_NAME") : "chdb";
            config.port = getenv("DB_PORT") ? stoi(getenv("DB_PORT")) : 3307;
        }
    }
    
    return config;
}

// Initialize database connection (using MariaDB)
MYSQL* initDBConnection(const DBConfig& config) {
    MYSQL* conn = mysql_init(nullptr);
    if (!conn) {
        cerr << "mysql_init failed" << endl;
        return nullptr;
    }
    
    // Set connection timeout
    mysql_options(conn, MYSQL_OPT_CONNECT_TIMEOUT, "30");
    
    // Connect to MariaDB database
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
    // Check if room_id parameter is provided
    if (argc < 2) {
        cout << "Usage: reset_room_str.exe <room_id>" << endl;
        cout << "Note: room_id can be a string type" << endl;
        return 1;
    }
    
    // Directly use string type room_id parameter
    string room_id = argv[1];
    if (room_id.empty()) {
        cout << "Invalid room_id" << endl;
        return 1;
    }
    
    // Get configuration
    DBConfig dbConfig = getDBConfig();
    // Print database connection information (for debugging)
    cout << "Connecting to database: " << dbConfig.host << ":" << dbConfig.port << endl;
    cout << "Database name: " << dbConfig.database << endl;
    cout << "Username: " << dbConfig.user << endl;
    
    // Initialize database connection
    MYSQL* conn = initDBConnection(dbConfig);
    if (!conn) {
        cerr << "Database connection failed, press any key to continue..." << endl;
        system("pause");
        return 1;
    }

    cout << "Database connection successful!" << endl;
    
    // Check current room status (Note: string type room_id is used here, need to wrap in single quotes)
    string check_sql = "SELECT status, round FROM tb_room WHERE id = '" + room_id + "'";
    if (mysql_query(conn, check_sql.c_str())) {
        cout << "Failed to query room information: " << mysql_error(conn) << endl;
        mysql_close(conn);
        return 1;
    }
    
    MYSQL_RES* result = mysql_store_result(conn);
    if (result == NULL) {
        cout << "Failed to get query result: " << mysql_error(conn) << endl;
        mysql_close(conn);
        return 1;
    }
    
    MYSQL_ROW row = mysql_fetch_row(result);
    if (row == NULL) {
        cout << "Specified room ID not found: " << room_id << endl;
        mysql_free_result(result);
        mysql_close(conn);
        return 1;
    }
    
    // Get current status and round number
    int current_status = atoi(row[0]);
    int current_round = atoi(row[1]);
    
    mysql_free_result(result);
    
    // Check if status is 3
    if (current_status == 3) {
        // Reset status to 2 and increase round number
        int new_round = current_round + 1;
        string update_sql = "UPDATE tb_room SET status = 2, round = " + to_string(new_round) + " WHERE id = '" + room_id + "'";
        
        if (mysql_query(conn, update_sql.c_str())) {
            cout << "Failed to update room information: " << mysql_error(conn) << endl;
            mysql_close(conn);
            return 1;
        }
        
        // Check affected rows
        if (mysql_affected_rows(conn) > 0) {
            cout << "Successfully reset room status and increased round number: room_id='" << room_id 
                 << "', old status=" << current_status << ", new status=2" 
                 << ", old round=" << current_round << ", new round=" << new_round << endl;
        } else {
            cout << "Updating room information did not affect any rows" << endl;
            mysql_close(conn);
            return 1;
        }
    } else {
        cout << "Room status is not 3, no update needed: room_id='" << room_id << "', current status=" << current_status << endl;
    }
    
    // Close database connection
    mysql_close(conn);
    
    return 0;
}