#include <iostream>
#include <mysql.h> // MariaDB兼容MySQL的API
#include <ctime>
#include <cstdlib>
#include <string>
#include <fstream>
#include <sstream>
#include <map>

using namespace std;

// 数据库配置结构体
struct DBConfig {
	string host;
	string user;
	string password;
	string database;
	unsigned int port;
};

// 读取INI配置文件函数
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

// 应用配置结构体，对应PHP中的$status_threshold
struct AppConfig {
	int type5_seconds;  // 3分钟未响应设为type=5
	int type2_seconds;  // 60分钟未响应设为type=2
};

// 从配置文件获取数据库配置
DBConfig getDBConfig() {
	DBConfig config;
	
	// 先尝试从配置文件读取
	auto configMap = readConfigFile("db_config.ini");
	if (configMap.count("database")) {
		auto& dbSection = configMap["database"];
		
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
	
	return config;
}

// 获取应用配置
AppConfig getAppConfig() {
	AppConfig config;
	
	// 先尝试从配置文件读取
	auto configMap = readConfigFile("db_config.ini");
	if (configMap.count("app")) {
		auto& appSection = configMap["app"];
		
		config.type5_seconds = appSection.count("type5_seconds") ? std::stoi(appSection["type5_seconds"]) : 180;
		config.type2_seconds = appSection.count("type2_seconds") ? std::stoi(appSection["type2_seconds"]) : 3600;
	} else {
		// 配置文件不存在或格式错误时，使用默认值
		config.type5_seconds = 180;   // 3分钟 = 180秒
		config.type2_seconds = 3600;  // 60分钟 = 3600秒
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

// 执行更新用户状态的操作
bool updateUserStatus(MYSQL* conn, const AppConfig& appConfig) {
	try {
		// 获取当前时间戳
		time_t now = time(nullptr);
		unsigned int currentTimestamp = static_cast<unsigned int>(now);
		
		// 计算阈值时间戳
		unsigned int type5Threshold = currentTimestamp - appConfig.type5_seconds;
		unsigned int type2Threshold = currentTimestamp - appConfig.type2_seconds;
		
		// 1. 先将超过60分钟未响应的用户设为type=2
		string updateType2 = "UPDATE tb_user SET type = 2 "
		"WHERE last_active_time < FROM_UNIXTIME(" + to_string(type2Threshold) + ") " + 
		" AND type != 2";
		
		if (mysql_query(conn, updateType2.c_str()) != 0) {
			cerr << "Update type 2 failed: " << mysql_error(conn) << endl;
			return false;
		}
		int type2Count = mysql_affected_rows(conn);
		
		// 2. 再将超过3分钟但不到60分钟未响应的用户设为type=5
		string updateType5 = "UPDATE tb_user SET type = 5 "
		"WHERE last_active_time < FROM_UNIXTIME(" + to_string(type5Threshold) + ") " + 
		" AND last_active_time >= FROM_UNIXTIME(" + to_string(type2Threshold) + ") " +
		" AND type != 5";
		
		if (mysql_query(conn, updateType5.c_str()) != 0) {
			cerr << "Update type 5 failed: " << mysql_error(conn) << endl;
			return false;
		}
		int type5Count = mysql_affected_rows(conn);
		
		cout << "Status update completed." << endl;
		cout << "Updated to type 2: " << type2Count << " records" << endl;
		cout << "Updated to type 5: " << type5Count << " records" << endl;
		
		return true;
	}
	catch (const exception& e) {
		cerr << "Error updating user status: " << e.what() << endl;
		return false;
	}
}

// 修复房间状态的功能
bool fixRoomStatus(MYSQL* conn) {
	try {
		// 1. 查找只有一个用户的房间
		string findSingleUserRooms = "SELECT id, name, user_id0, user_id1 FROM tb_room WHERE user_cnt = 1";
		
		if (mysql_query(conn, findSingleUserRooms.c_str()) != 0) {
			cerr << "Find single user rooms failed: " << mysql_error(conn) << endl;
			return false;
		}
		
		MYSQL_RES* result = mysql_store_result(conn);
		if (!result) {
			cerr << "mysql_store_result failed: " << mysql_error(conn) << endl;
			return false;
		}
		
		int singleUserRoomCount = 0;
		
		// 处理每个只有一个用户的房间
		MYSQL_ROW row;
		while ((row = mysql_fetch_row(result)) != nullptr) {
			unsigned int roomId = atoi(row[0]);
			string roomName = row[1] ? row[1] : "未命名房间";
			unsigned int userId0 = row[2] ? atoi(row[2]) : 0;
			unsigned int userId1 = row[3] ? atoi(row[3]) : 0;
			
			// 确定用户ID（其中一个应该是非零）
			unsigned int userId = (userId0 > 0) ? userId0 : userId1;
			
			if (userId > 0) {
				// 获取用户信息
				string getUserInfo = "SELECT name FROM tb_user WHERE id = " + to_string(userId);
				if (mysql_query(conn, getUserInfo.c_str()) != 0) {
					cerr << "Get user info failed: " << mysql_error(conn) << endl;
					continue;
				}
				
				MYSQL_RES* userResult = mysql_store_result(conn);
				if (!userResult) {
					cerr << "mysql_store_result for user failed: " << mysql_error(conn) << endl;
					continue;
				}
				
				MYSQL_ROW userRow = mysql_fetch_row(userResult);
				string username = userRow && userRow[0] ? userRow[0] : "未知用户";
				mysql_free_result(userResult);
				
				// 重置用户的in_room状态
				string updateUserStatus = "UPDATE tb_user SET in_room = 0 WHERE id = " + to_string(userId);
				if (mysql_query(conn, updateUserStatus.c_str()) != 0) {
					cerr << "Update user in_room failed: " << mysql_error(conn) << endl;
					continue;
				}
				
				cout << "用户 " << username << " (ID: " << userId << ") 的in_room状态已重置为0" << endl;
			}
			
			// 清理这个房间 - 将用户计数设为0，清除用户ID
			string updateRoom = "UPDATE tb_room SET user_cnt = 0, user_id0 = NULL, user_id1 = NULL WHERE id = " + to_string(roomId);
			if (mysql_query(conn, updateRoom.c_str()) != 0) {
				cerr << "Update room failed: " << mysql_error(conn) << endl;
				continue;
			}
			
			cout << "房间 " << roomName << " (ID: " << roomId << ") 已清理" << endl;
			singleUserRoomCount++;
		}
		
		mysql_free_result(result);
		
		// 2. 清理tb_room表中所有用户计数为0的房间的word_id
		string clearWordId = "UPDATE tb_room SET word_id = 0 WHERE user_cnt = 0";
		if (mysql_query(conn, clearWordId.c_str()) != 0) {
			cerr << "Clear word_id failed: " << mysql_error(conn) << endl;
			return false;
		}
		
		// 3. 处理一个用户退出房间的情况
		// 当用户type不为4（不在游戏中）且没有和另一个用户一起进入游戏（type不为3）
		// 修复列名错误：将status改为type
		string findAbnormalUsers = "SELECT id, name, in_room FROM tb_user WHERE in_room > 0 AND type NOT IN (3, 4)";
		
		if (mysql_query(conn, findAbnormalUsers.c_str()) != 0) {
			cerr << "Find abnormal users failed: " << mysql_error(conn) << endl;
			return false;
		}
		
		MYSQL_RES* abnormalUsersResult = mysql_store_result(conn);
		if (!abnormalUsersResult) {
			cerr << "mysql_store_result for abnormal users failed: " << mysql_error(conn) << endl;
			return false;
		}
		
		int abnormalUserCount = 0;
		
		// 处理每个异常用户
		while ((row = mysql_fetch_row(abnormalUsersResult)) != nullptr) {
			unsigned int userId = atoi(row[0]);
			string userName = row[1] ? row[1] : "未知用户";
			unsigned int roomId = atoi(row[2]);
			
			// 获取房间信息
			string getRoomInfo = "SELECT id, name, user_id0, user_id1, user_cnt FROM tb_room WHERE id = " + to_string(roomId);
			if (mysql_query(conn, getRoomInfo.c_str()) != 0) {
				cerr << "Get room info failed: " << mysql_error(conn) << endl;
				continue;
			}
			
			MYSQL_RES* roomResult = mysql_store_result(conn);
			if (!roomResult) {
				cerr << "mysql_store_result for room failed: " << mysql_error(conn) << endl;
				continue;
			}
			
			MYSQL_ROW roomRow = mysql_fetch_row(roomResult);
			if (roomRow) {
				string roomName = roomRow[1] ? roomRow[1] : "未命名房间";
				unsigned int user_id0 = roomRow[2] ? atoi(roomRow[2]) : 0;
				unsigned int user_id1 = roomRow[3] ? atoi(roomRow[3]) : 0;
				
				// 获取房间中的另一个用户ID
				unsigned int otherUserId = (user_id0 == userId) ? user_id1 : user_id0;
				
				// 如果有另一个用户，也将其in_room状态重置为0
				if (otherUserId > 0) {
					string getOtherUserInfo = "SELECT name FROM tb_user WHERE id = " + to_string(otherUserId);
					if (mysql_query(conn, getOtherUserInfo.c_str()) != 0) {
						cerr << "Get other user info failed: " << mysql_error(conn) << endl;
					} else {
						MYSQL_RES* otherUserResult = mysql_store_result(conn);
						if (otherUserResult) {
							MYSQL_ROW otherUserRow = mysql_fetch_row(otherUserResult);
							string otherUsername = otherUserRow && otherUserRow[0] ? otherUserRow[0] : "未知用户";
							mysql_free_result(otherUserResult);
							
							// 重置另一个用户的in_room状态
							string updateOtherUser = "UPDATE tb_user SET in_room = 0 WHERE id = " + to_string(otherUserId);
							if (mysql_query(conn, updateOtherUser.c_str()) != 0) {
								cerr << "Update other user in_room failed: " << mysql_error(conn) << endl;
							} else {
								cout << "用户 " << otherUsername << " (ID: " << otherUserId << ") 已从异常房间中移出" << endl;
							}
						}
					}
				}
				
				// 清理房间
				string updateRoomStatus = "UPDATE tb_room SET user_cnt = 0, user_id0 = NULL, user_id1 = NULL, word_id = 0 WHERE id = " + to_string(roomId);
				if (mysql_query(conn, updateRoomStatus.c_str()) != 0) {
					cerr << "Clean room failed: " << mysql_error(conn) << endl;
				} else {
					cout << "用户 " << userName << " (ID: " << userId << ") 所在的异常房间 " << roomName << " (ID: " << roomId << ") 已清理" << endl;
				}
				
				// 重置当前用户的in_room状态
				string updateCurrentUser = "UPDATE tb_user SET in_room = 0 WHERE id = " + to_string(userId);
				if (mysql_query(conn, updateCurrentUser.c_str()) != 0) {
					cerr << "Update current user in_room failed: " << mysql_error(conn) << endl;
				}
				
				abnormalUserCount++;
			}
			
			mysql_free_result(roomResult);
		}
		
		mysql_free_result(abnormalUsersResult);
		
		// 4. 特别重置catten用户的in_room状态（根据用户反馈的问题）
		string resetCatten = "UPDATE tb_user SET in_room = 0 WHERE name = 'catten'";
		if (mysql_query(conn, resetCatten.c_str()) != 0) {
			cerr << "Reset catten user failed: " << mysql_error(conn) << endl;
		} else {
			int affectedRows = mysql_affected_rows(conn);
			if (affectedRows > 0) {
				cout << "用户 catten 的in_room状态已重置为0" << endl;
			}
		}
		
		cout << "房间状态修复完成！" << endl;
		cout << "清理的单人房间数量: " << singleUserRoomCount << endl;
		cout << "处理的异常用户数量: " << abnormalUserCount << endl;
		
		return true;
	} catch (const exception& e) {
		cerr << "Error fixing room status: " << e.what() << endl;
		return false;
	}
}

int main() {
	// 获取配置
	DBConfig dbConfig = getDBConfig();
	AppConfig appConfig = getAppConfig();

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

	// 执行更新操作
	bool success = updateUserStatus(conn, appConfig);
	if (!success) {
		cerr << "用户状态更新失败！" << endl;
	}

	// 调用修复房间状态的函数
	bool roomStatusFixed = fixRoomStatus(conn);
	if (!roomStatusFixed) {
		cerr << "房间状态修复失败！" << endl;
	}

	// 清理资源
	mysql_close(conn);

	// 添加暂停，让用户能看到输出
	cerr << "按任意键继续..." << endl;
	system("pause");

	return (success && roomStatusFixed) ? 0 : 1;
}
