<?php
// Correct path to EnvLoader
require_once __DIR__ . '/../includes/EnvLoader.php';

// Load the .env file
EnvLoader::load(__DIR__ . '/../.env');

// Database constants from .env
define('DB_HOST', EnvLoader::get('DB_HOST', 'localhost'));
define('DB_USER', EnvLoader::get('DB_USER', 'root'));
define('DB_PASS', EnvLoader::get('DB_PASS', ''));
define('DB_NAME', EnvLoader::get('DB_NAME', 'ai_chatbot_db'));

// Groq API constants from .env
define('GROQ_API_KEY', EnvLoader::get('GROQ_API_KEY', ''));
define('GROQ_API_URL', EnvLoader::get('GROQ_API_URL', ''));
define('GROQ_MODEL', EnvLoader::get('GROQ_MODEL', ''));

// Local Ollama constants from .env (used for local provider mode)
define('LLM_API_URL', EnvLoader::get('LLM_API_URL', 'http://127.0.0.1:11434/api/chat'));
define('LLM_MODEL', EnvLoader::get('LLM_MODEL', 'llama3.2:3b'));

// Determine BASE_PATH dynamically for Apache/XAMPP and root installs.
if (!defined('BASE_PATH')) {
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $base_path = '/';

    // Example matches:
    // /InfoBot/pages/chat.php  -> /InfoBot/
    // /pages/chat.php          -> /
    if (preg_match('#^(.*/)(?:pages|api|assets|includes|config|database)/#i', $script_name, $m)) {
        $base_path = $m[1];
    } elseif (preg_match('#^(.*/)[^/]+$#', $script_name, $m)) {
        $base_path = $m[1];
    }

    if ($base_path === '') {
        $base_path = '/';
    }
    if (substr($base_path, -1) !== '/') {
        $base_path .= '/';
    }

    define('BASE_PATH', $base_path);
}

/**
 * Create database connection
 */
function getDatabaseConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Close database connection
 */
function closeDatabaseConnection($conn) {
    if ($conn) $conn->close();
}
?>
