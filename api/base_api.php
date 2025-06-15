<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers first
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Function to send JSON response and exit
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit();
}

// Function to send error response
function sendJsonError($message, $status_code = 400) {
    error_log("API Error: " . $message);
    sendJsonResponse(array("error" => $message), $status_code);
}

// Try to include database config with multiple path attempts
$config_loaded = false;
$config_paths = [
    __DIR__ . '/../config/database.php',
    '../config/database.php',
    'config/database.php',
    './config/database.php'
];

foreach ($config_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $config_loaded = true;
        break;
    }
}

if (!$config_loaded) {
    sendJsonError("Database configuration file not found. Checked paths: " . implode(', ', $config_paths), 500);
}

class BaseAPI {
    protected $conn;
    protected $table_name;

    public function __construct($table_name) {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
            $this->table_name = $table_name;
            
            if (!$this->conn) {
                throw new Exception("Database connection failed");
            }
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            sendJsonError("Database connection failed: " . $e->getMessage(), 500);
        }
    }

    protected function sendResponse($data, $status_code = 200) {
        sendJsonResponse($data, $status_code);
    }

    protected function sendError($message, $status_code = 400) {
        sendJsonError($message, $status_code);
    }

    protected function validateRequired($data, $required_fields) {
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return "Field '$field' is required";
            }
        }
        return null;
    }

    protected function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}
?>
