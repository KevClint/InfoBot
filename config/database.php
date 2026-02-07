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
