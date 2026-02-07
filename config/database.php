<?php
/**
 * DATABASE CONFIGURATION
 * 
 * This file contains all database connection settings.
 * Update these values to match your XAMPP MySQL configuration.
 */

// Database credentials
define('DB_HOST', 'localhost');      // Usually 'localhost' for XAMPP
define('DB_USER', 'root');           // Default XAMPP MySQL username
define('DB_PASS', '');               // Default XAMPP MySQL password (empty)
define('DB_NAME', 'ai_chatbot_db');  // Our database name

// Groq API Configuration
define('GROQ_API_KEY', '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');  // Get from: https://console.groq.com/
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('GROQ_MODEL', 'llama-3.1-8b-instant');  // Fast and efficient model

/**
 * Create database connection
 * 
 * @return mysqli Database connection object
 */
function getDatabaseConnection() {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8 to support all characters
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Close database connection
 * 
 * @param mysqli $conn Database connection to close
 */
function closeDatabaseConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

?>
