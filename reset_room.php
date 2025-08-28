<?php
// reset_room.php - PHP implementation of room reset functionality
// This script directly implements the room reset logic without relying on external C++ programs

// Function to read key-value pairs from PHP configuration file
function readPHPConfigFile($filename) {
    $config = [];
    
    if (!file_exists($filename)) {
        fwrite(STDERR, "Failed to open PHP configuration file: $filename\n");
        return $config;
    }
    
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (empty($line) || $line[0] == ';' || substr($line, 0, 2) == '//') {
            continue;
        }
        
        // Find $ variable definition
        $varPos = strpos($line, '$');
        if ($varPos !== false) {
            // Find equal sign
            $equalPos = strpos($line, '=', $varPos);
            if ($equalPos !== false) {
                // Find semicolon or quotation mark
                $endPos = strpos($line, ';', $equalPos);
                if ($endPos === false) {
                    $endPos = strlen($line);
                }
                
                // Extract variable name
                $varName = substr($line, $varPos + 1, $equalPos - $varPos - 1);
                // Remove spaces and special characters from variable name
                $varName = trim($varName);
                
                // Extract value (considering quotes)
                $value = substr($line, $equalPos + 1, $endPos - $equalPos - 1);
                // Remove spaces, quotes and special characters from value
                $value = trim($value, " \t'\";\r\n");
                
                // Store key-value pair
                $config[$varName] = $value;
            }
        }
    }
    
    return $config;
}

// Function to read INI configuration file (retained for compatibility)
function readConfigFile($filename) {
    $config = [];
    
    if (!file_exists($filename)) {
        fwrite(STDERR, "Failed to open configuration file: $filename\n");
        return $config;
    }
    
    $currentSection = '';
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (empty($line) || $line[0] == ';' || $line[0] == '#') {
            continue;
        }
        
        // Check if it's a section header
        if ($line[0] == '[' && substr($line, -1) == ']') {
            $currentSection = substr($line, 1, -1);
            if (!isset($config[$currentSection])) {
                $config[$currentSection] = [];
            }
            continue;
        }
        
        // Parse key-value pair
        $pos = strpos($line, '=');
        if ($pos !== false && !empty($currentSection)) {
            $key = substr($line, 0, $pos);
            $value = substr($line, $pos + 1);
            
            // Remove spaces from key-value pair
            $key = trim($key);
            $value = trim($value);
            
            $config[$currentSection][$key] = $value;
        }
    }
    
    return $config;
}

// Function to get database configuration
function getDBConfig() {
    $config = [];
    
    // First try to read from PHP configuration file
    $configMap = readPHPConfigFile('../config.inc');
    if (!empty($configMap)) {
        $config['host'] = isset($configMap['db_host']) ? $configMap['db_host'] : (isset($configMap['db_server']) ? $configMap['db_server'] : 'localhost');
        $config['user'] = isset($configMap['db_user']) ? $configMap['db_user'] : 'charade';
        $config['password'] = isset($configMap['db_password']) ? $configMap['db_password'] : 'pwdtest1';
        $config['database'] = isset($configMap['db_name']) ? $configMap['db_name'] : (isset($configMap['db_database']) ? $configMap['db_database'] : 'chdb');
        $config['port'] = isset($configMap['db_port']) ? (int)$configMap['db_port'] : 3307;
    } else {
        // If PHP configuration file reading fails, fall back to INI file
        echo "Failed to read PHP configuration file, attempting to read INI configuration file\n";
        $configMapIni = readConfigFile('db_config.ini');
        if (isset($configMapIni['database'])) {
            $dbSection = $configMapIni['database'];
            
            $config['host'] = isset($dbSection['host']) ? $dbSection['host'] : 'localhost';
            $config['user'] = isset($dbSection['user']) ? $dbSection['user'] : 'charade';
            $config['password'] = isset($dbSection['password']) ? $dbSection['password'] : 'pwdtest1';
            $config['database'] = isset($dbSection['database']) ? $dbSection['database'] : 'chdb';
            $config['port'] = isset($dbSection['port']) ? (int)$dbSection['port'] : 3307;
        } else {
            // When configuration file does not exist or is formatted incorrectly, use environment variables or default values
            echo "Configuration file does not exist or is formatted incorrectly, attempting to use environment variables or default values\n";
            $config['host'] = getenv('DB_HOST') ?: 'localhost';
            $config['user'] = getenv('DB_USER') ?: 'charade';
            $config['password'] = getenv('DB_PASSWORD') ?: 'pwdtest1';
            $config['database'] = getenv('DB_NAME') ?: 'chdb';
            $config['port'] = getenv('DB_PORT') ? (int)getenv('DB_PORT') : 3307;
        }
    }
    
    return $config;
}

// Initialize database connection
function initDBConnection($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
            $config['user'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 30, // Set connection timeout
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        fwrite(STDERR, "Connection failed: " . $e->getMessage() . "\n");
        fwrite(STDERR, "Error code: " . $e->getCode() . "\n");
        return null;
    }
}

// Function to reset room status
function resetRoom($room_id, $is_cli = false) {
    // Check if room_id is valid
    if (empty($room_id)) {
        echo "Invalid room_id\n";
        if (!$is_cli) http_response_code(400);
        return 1;
    }
    
    // Get configuration
    $dbConfig = getDBConfig();
    // Print database connection information (for debugging)
    echo "Connecting to database: {$dbConfig['host']}:{$dbConfig['port']}\n";
    echo "Database name: {$dbConfig['database']}\n";
    echo "Username: {$dbConfig['user']}\n";
    
    // Initialize database connection
    $pdo = initDBConnection($dbConfig);
    if (!$pdo) {
        fwrite(STDERR, "Database connection failed, press any key to continue...\n");
        if ($is_cli) {
            if (PHP_OS_FAMILY === 'Windows') {
                shell_exec('pause');
            } else {
                readline();
            }
        } else {
            http_response_code(500);
        }
        return 1;
    }
    
    echo "Database connection successful!\n";
    
    try {
        // Check current room status
        $check_sql = "SELECT status, round FROM tb_room WHERE id = ?";
        $stmt = $pdo->prepare($check_sql);
        $stmt->execute([$room_id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$room) {
            echo "Specified room ID not found: $room_id\n";
            if (!$is_cli) http_response_code(404);
            return 1;
        }
        
        // Get current status and round number
        $current_status = (int)$room['status'];
        $current_round = (int)$room['round'];
        
        // Check if status is 3
        if ($current_status === 3) {
            // Reset status to 2 and increase round number
            $new_round = $current_round + 1;
            $update_sql = "UPDATE tb_room SET status = 2, round = ? WHERE id = ?";
            $stmt = $pdo->prepare($update_sql);
            $stmt->execute([$new_round, $room_id]);
            
            // Check affected rows
            if ($stmt->rowCount() > 0) {
                echo "Successfully reset room status and increased round number: room_id='$room_id', old status=$current_status, new status=2, old round=$current_round, new round=$new_round\n";
                if (!$is_cli) http_response_code(200);
            } else {
                echo "Updating room information did not affect any rows\n";
                if (!$is_cli) http_response_code(500);
                return 1;
            }
        } else {
            echo "Room status is not 3, no update needed: room_id='$room_id', current status=$current_status\n";
            if (!$is_cli) http_response_code(200);
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
        if (!$is_cli) http_response_code(500);
        return 1;
    }
    
    return 0;
}

// Handle different execution modes
if (php_sapi_name() === 'cli') {
    // Command line mode
    if (isset($argv[1])) {
        $room_id = $argv[1];
        exit(resetRoom($room_id, true));
    } else {
        echo "Usage: php reset_room.php <room_id>\n";
        echo "Note: room_id can be a string type\n";
        exit(1);
    }
} else {
    // Web browser mode
    if (isset($_GET['room_id'])) {
        $room_id = $_GET['room_id'];
        resetRoom($room_id, false);
    } else {
        echo "This script must be run with a room_id parameter.\n";
        echo "Usage: reset_room.php?room_id=<room_id>\n";
        http_response_code(400);
    }
}
?>