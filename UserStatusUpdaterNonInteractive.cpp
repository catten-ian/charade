#include <iostream>
#include <mysql.h> // MariaDB compatible with MySQL API
#include <ctime>
#include <cstdlib>
#include <string>
#include <fstream>
#include <sstream>
#include <map>

using namespace std;

// Database configuration structure
struct DBConfig {
	string host;
	string user;
	string password;
	string database;
	unsigned int port;
};

// Function to read PHP format configuration file
std::map<std::string, std::string> readPHPConfigFile(const std::string& filename) {
	std::map<std::string, std::string> config;
	std::ifstream file(filename);

	if (!file.is_open()) {
		std::cerr << "Failed to open configuration file: " << filename << std::endl;
		return config;
	}

	std::string line;
	while (std::getline(file, line)) {
		// Skip comments, empty lines and PHP tags
		if (line.empty() || line[0] == '#' || line[0] == '/' || line.find("<?php") != std::string::npos || line.find("?>") != std::string::npos) {
			continue;
		}

		// Parse variable definitions
		size_t pos = line.find('=');
		if (pos != std::string::npos && line.find('$') != std::string::npos) {
			// Extract variable name (remove $ sign)
			size_t varStart = line.find('$');
			if (varStart != std::string::npos) {
				size_t varEnd = line.find_first_of(" =;", varStart + 1);
				if (varEnd != std::string::npos) {
					std::string key = line.substr(varStart + 1, varEnd - varStart - 1);
					
					// Extract variable value (remove quotes and semicolon)
					size_t valueStart = line.find_first_of("'\"");
					size_t valueEnd = std::string::npos;
					if (valueStart != std::string::npos) {
						char quote = line[valueStart];
						valueEnd = line.find(quote, valueStart + 1);
						if (valueEnd != std::string::npos) {
							std::string value = line.substr(valueStart + 1, valueEnd - valueStart - 1);
							config[key] = value;
						}
					} else {
						// Handle unquoted numeric values
						size_t endPos = line.find(';', pos + 1);
						if (endPos != std::string::npos) {
							std::string value = line.substr(pos + 1, endPos - pos - 1);
							// Remove spaces
							value.erase(0, value.find_first_not_of(" \t"));
							value.erase(value.find_last_not_of(" \t") + 1);
							config[key] = value;
						}
					}
				}
			}
		}
	}

	file.close();
	return config;
}

// Function to read INI configuration file (kept for compatibility)
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

		// Parse key-value pairs
		size_t pos = line.find('=');
		if (pos != std::string::npos && !currentSection.empty()) {
			std::string key = line.substr(0, pos);
			std::string value = line.substr(pos + 1);

			// Remove spaces in key-value pairs
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

// Application configuration structure, corresponds to $status_threshold in PHP
struct AppConfig {
	int type5_seconds;  // Set to type=5 after 3 minutes of inactivity
	int type2_seconds;  // Set to type=2 after 60 minutes of inactivity
};

// Get database configuration from PHP config file
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
		// If PHP config file reading fails, try to read INI config file
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
			// If config file doesn't exist or format is incorrect, use environment variables or default values
			std::cout << "Configuration file does not exist or format is incorrect, attempting to use environment variables or default values" << std::endl;
			config.host = getenv("DB_HOST") ? getenv("DB_HOST") : "localhost";
			config.user = getenv("DB_USER") ? getenv("DB_USER") : "charade";
			config.password = getenv("DB_PASSWORD") ? getenv("DB_PASSWORD") : "pwdtest1";
			config.database = getenv("DB_NAME") ? getenv("DB_NAME") : "chdb";
			config.port = getenv("DB_PORT") ? stoi(getenv("DB_PORT")) : 3307;
		}
	}
	
	return config;
}

// Get application configuration
AppConfig getAppConfig() {
	AppConfig config;
	
	// First try to read from PHP configuration file (based on search_codebase results, app config is in config.app.inc)
	auto appConfigMap = readPHPConfigFile("config.app.inc");
	if (!appConfigMap.empty() && appConfigMap.count("status_threshold")) {
		// Simple parsing of PHP array configuration (may need more complex parsing logic)
		config.type5_seconds = 180;   // Default value: 3 minutes = 180 seconds
		config.type2_seconds = 3600;  // Default value: 60 minutes = 3600 seconds
	} else {
		// Fall back to INI file
		auto configMap = readConfigFile("db_config.ini");
		if (configMap.count("app")) {
			auto& appSection = configMap["app"];
			
			config.type5_seconds = appSection.count("type5_seconds") ? std::stoi(appSection["type5_seconds"]) : 180;
			config.type2_seconds = appSection.count("type2_seconds") ? std::stoi(appSection["type2_seconds"]) : 3600;
		} else {
			// If config file doesn't exist or format is incorrect, use default values
			config.type5_seconds = 180;   // 3 minutes = 180 seconds
			config.type2_seconds = 3600;  // 60 minutes = 3600 seconds
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

// Execute user status update operation
bool updateUserStatus(MYSQL* conn, const AppConfig& appConfig) {
	try {
		// Get current timestamp
		time_t now = time(nullptr);
		unsigned int currentTimestamp = static_cast<unsigned int>(now);
		
		// Calculate threshold timestamps
		unsigned int type5Threshold = currentTimestamp - appConfig.type5_seconds;
		unsigned int type2Threshold = currentTimestamp - appConfig.type2_seconds;
		
		// 1. First set users who haven't responded for over 60 minutes to type=2
		string updateType2 = "UPDATE tb_user SET type = 2 "
		"WHERE last_active_time < FROM_UNIXTIME(" + to_string(type2Threshold) + ") " + 
		" AND type != 2";
		
		if (mysql_query(conn, updateType2.c_str()) != 0) {
			cerr << "Update type 2 failed: " << mysql_error(conn) << endl;
			return false;
		}
		int type2Count = mysql_affected_rows(conn);
		
		// 2. Then set users who haven't responded for over 3 minutes but less than 60 minutes to type=5
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

// Function to fix room status
bool fixRoomStatus(MYSQL* conn) {
	try {
		// 1. Find rooms with only one user
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
		
		// Process each room with only one user
		MYSQL_ROW row;
		while ((row = mysql_fetch_row(result)) != nullptr) {
			unsigned int roomId = atoi(row[0]);
			string roomName = row[1] ? row[1] : "Unnamed Room";
			unsigned int userId0 = row[2] ? atoi(row[2]) : 0;
			unsigned int userId1 = row[3] ? atoi(row[3]) : 0;
			
			// Determine user ID (one should be non-zero)
			unsigned int userId = (userId0 > 0) ? userId0 : userId1;
			
			if (userId > 0) {
				// Get user information
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
				string username = userRow && userRow[0] ? userRow[0] : "Unknown User";
				mysql_free_result(userResult);
				
				// Reset user's in_room status
				string updateUserStatus = "UPDATE tb_user SET in_room = 0 WHERE id = " + to_string(userId);
				if (mysql_query(conn, updateUserStatus.c_str()) != 0) {
					cerr << "Update user in_room failed: " << mysql_error(conn) << endl;
					continue;
				}
				
				cout << "User " << username << " (ID: " << userId << ") in_room status has been reset to 0" << endl;
			}
			
			// Clean up this room - set user count to 0, clear user IDs
			string updateRoom = "UPDATE tb_room SET user_cnt = 0, user_id0 = NULL, user_id1 = NULL WHERE id = " + to_string(roomId);
			if (mysql_query(conn, updateRoom.c_str()) != 0) {
				cerr << "Update room failed: " << mysql_error(conn) << endl;
				continue;
			}
			
			cout << "Room " << roomName << " (ID: " << roomId << ") has been cleaned up" << endl;
			singleUserRoomCount++;
		}
		
		mysql_free_result(result);
		
		// 2. Clear word_id for all rooms with 0 users in tb_room table
		string clearWordId = "UPDATE tb_room SET word_id = 0 WHERE user_cnt = 0";
		if (mysql_query(conn, clearWordId.c_str()) != 0) {
			cerr << "Clear word_id failed: " << mysql_error(conn) << endl;
			return false;
		}
		
		// 3. Handle cases where a user exits a room
		// When user type is not 4 (not in game) and not entered game with another user (type not 3)
		// Fix column name error: change status to type
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
		
		// Process each abnormal user
		while ((row = mysql_fetch_row(abnormalUsersResult)) != nullptr) {
			unsigned int userId = atoi(row[0]);
			string userName = row[1] ? row[1] : "Unknown User";
			unsigned int roomId = atoi(row[2]);
			
			// Get room information
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
				string roomName = roomRow[1] ? roomRow[1] : "Unnamed Room";
				unsigned int user_id0 = roomRow[2] ? atoi(roomRow[2]) : 0;
				unsigned int user_id1 = roomRow[3] ? atoi(roomRow[3]) : 0;
				
				// Get ID of the other user in the room
				unsigned int otherUserId = (user_id0 == userId) ? user_id1 : user_id0;
				
				// If there is another user, also reset their in_room status to 0
				if (otherUserId > 0) {
					string getOtherUserInfo = "SELECT name FROM tb_user WHERE id = " + to_string(otherUserId);
					if (mysql_query(conn, getOtherUserInfo.c_str()) != 0) {
						cerr << "Get other user info failed: " << mysql_error(conn) << endl;
					} else {
						MYSQL_RES* otherUserResult = mysql_store_result(conn);
						if (otherUserResult) {
							MYSQL_ROW otherUserRow = mysql_fetch_row(otherUserResult);
							string otherUsername = otherUserRow && otherUserRow[0] ? otherUserRow[0] : "Unknown User";
							mysql_free_result(otherUserResult);
							
							// Reset other user's in_room status
							string updateOtherUser = "UPDATE tb_user SET in_room = 0 WHERE id = " + to_string(otherUserId);
							if (mysql_query(conn, updateOtherUser.c_str()) != 0) {
								cerr << "Update other user in_room failed: " << mysql_error(conn) << endl;
							} else {
								cout << "User " << otherUsername << " (ID: " << otherUserId << ") has been removed from abnormal room" << endl;
							}
						}
					}
				}
				
				// Clean up the room
				string updateRoomStatus = "UPDATE tb_room SET user_cnt = 0, user_id0 = NULL, user_id1 = NULL, word_id = 0 WHERE id = " + to_string(roomId);
				if (mysql_query(conn, updateRoomStatus.c_str()) != 0) {
					cerr << "Clean room failed: " << mysql_error(conn) << endl;
				} else {
					cout << "Abnormal room " << roomName << " (ID: " << roomId << ") containing user " << userName << " (ID: " << userId << ") has been cleaned up" << endl;
				}
				
				// Reset current user's in_room status
				string updateCurrentUser = "UPDATE tb_user SET in_room = 0 WHERE id = " + to_string(userId);
				if (mysql_query(conn, updateCurrentUser.c_str()) != 0) {
					cerr << "Update current user in_room failed: " << mysql_error(conn) << endl;
				}
				
				abnormalUserCount++;
			}
			
			mysql_free_result(roomResult);
		}
		
		mysql_free_result(abnormalUsersResult);
		
		// 4. Specifically reset catten user's in_room status (based on user feedback issue)
		string resetCatten = "UPDATE tb_user SET in_room = 0 WHERE name = 'catten'";
		if (mysql_query(conn, resetCatten.c_str()) != 0) {
			cerr << "Reset catten user failed: " << mysql_error(conn) << endl;
		} else {
			int affectedRows = mysql_affected_rows(conn);
			if (affectedRows > 0) {
				cout << "User catten's in_room status has been reset to 0" << endl;
			}
		}
		
		cout << "Room status repair completed!" << endl;
		cout << "Number of single-user rooms cleaned: " << singleUserRoomCount << endl;
		cout << "Number of abnormal users processed: " << abnormalUserCount << endl;
		
		return true;
	} catch (const exception& e) {
		cerr << "Error fixing room status: " << e.what() << endl;
		return false;
	}
}

int main() {
	// Get configuration
	DBConfig dbConfig = getDBConfig();
	AppConfig appConfig = getAppConfig();

	// Print database connection information (for debugging)
	cout << "Connecting to database: " << dbConfig.host << ":" << dbConfig.port << endl;
	cout << "Database name: " << dbConfig.database << endl;
	cout << "Username: " << dbConfig.user << endl;
	
	// Initialize database connection
	MYSQL* conn = initDBConnection(dbConfig);
	if (!conn) {
		cerr << "Failed to connect to database, press any key to continue..." << endl;
		return 1;
	}

	cout << "Database connection successful!" << endl;

	// Execute update operation
	bool success = updateUserStatus(conn, appConfig);
	if (!success) {
		cerr << "Failed to update user status!" << endl;
	}

	// Call function to fix room status
	bool roomStatusFixed = fixRoomStatus(conn);
	if (!roomStatusFixed) {
		cerr << "Failed to repair room status!" << endl;
	}

	// Clean up resources
	mysql_close(conn);

	// Add pause so user can see the output
	cerr << "Press any key to continue..." << endl;

	return (success && roomStatusFixed) ? 0 : 1;
}
