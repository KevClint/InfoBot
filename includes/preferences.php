<?php
/**
 * USER PREFERENCES AND FAVORITES FUNCTIONS
 * 
 * This file contains functions for managing user preferences
 * (dark mode, font size, theme color) and favorite responses.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get user preferences
 * 
 * @param int $user_id User ID
 * @return array User preferences or defaults if not set
 */
function getUserPreferences($user_id) {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("SELECT dark_mode, font_size, theme_color FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $prefs = $result->fetch_assoc();
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    // Return defaults if not found
    if (!$prefs) {
        return array(
            'dark_mode' => FALSE,
            'font_size' => 'medium',
            'theme_color' => 'blue'
        );
    }
    
    return $prefs;
}

/**
 * Update user preferences
 * 
 * @param int $user_id User ID
 * @param bool $dark_mode Dark mode enabled
 * @param string $font_size Font size (small, medium, large)
 * @param string $theme_color Theme color
 * @return bool True on success, false on failure
 */
function updateUserPreferences($user_id, $dark_mode = false, $font_size = 'medium', $theme_color = 'blue') {
    $conn = getDatabaseConnection();
    
    // Check if preferences exist for this user
    $check_stmt = $conn->prepare("SELECT id FROM user_preferences WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();
    
    if ($check_result->num_rows > 0) {
        // Update existing preferences
        $stmt = $conn->prepare("UPDATE user_preferences SET dark_mode = ?, font_size = ?, theme_color = ? WHERE user_id = ?");
        $stmt->bind_param("issi", $dark_mode, $font_size, $theme_color, $user_id);
    } else {
        // Insert new preferences
        $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, dark_mode, font_size, theme_color) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $dark_mode, $font_size, $theme_color);
    }
    
    $success = $stmt->execute();
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $success;
}

/**
 * Add response to user's favorites
 * 
 * @param int $user_id User ID
 * @param int $message_id Message ID of the bot response
 * @return bool True on success, false on failure
 */
function addFavoriteResponse($user_id, $message_id) {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("INSERT IGNORE INTO favorite_responses (user_id, message_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $message_id);
    
    $success = $stmt->execute();
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $success;
}

/**
 * Remove response from user's favorites
 * 
 * @param int $user_id User ID
 * @param int $message_id Message ID
 * @return bool True on success, false on failure
 */
function removeFavoriteResponse($user_id, $message_id) {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("DELETE FROM favorite_responses WHERE user_id = ? AND message_id = ?");
    $stmt->bind_param("ii", $user_id, $message_id);
    
    $success = $stmt->execute();
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $success;
}

/**
 * Check if a message is favorited by user
 * 
 * @param int $user_id User ID
 * @param int $message_id Message ID
 * @return bool True if favorited, false otherwise
 */
function isFavorited($user_id, $message_id) {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("SELECT id FROM favorite_responses WHERE user_id = ? AND message_id = ?");
    $stmt->bind_param("ii", $user_id, $message_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $is_favorited = $result->num_rows > 0;
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $is_favorited;
}

/**
 * Get user's favorite responses in a conversation
 * 
 * @param int $user_id User ID
 * @param int $conversation_id Conversation ID
 * @return array Array of favorite messages
 */
function getFavoriteResponses($user_id, $conversation_id) {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("
        SELECT m.id, m.content, m.created_at FROM messages m
        INNER JOIN favorite_responses f ON m.id = f.message_id
        WHERE f.user_id = ? AND m.conversation_id = ? AND m.role = 'assistant'
        ORDER BY f.created_at DESC
    ");
    $stmt->bind_param("ii", $user_id, $conversation_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $favorites = array();
    
    while ($row = $result->fetch_assoc()) {
        $favorites[] = $row;
    }
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $favorites;
}

/**
 * Search messages across all conversations by keyword
 * 
 * @param int $user_id User ID
 * @param string $keyword Search keyword
 * @param int $limit Number of results (default: 20)
 * @return array Array of search results
 */
function searchChatHistory($user_id, $keyword, $limit = 20) {
    $conn = getDatabaseConnection();
    
    $search_term = '%' . $conn->real_escape_string($keyword) . '%';
    
    $stmt = $conn->prepare("
        SELECT m.id, m.conversation_id, m.role, m.content, m.created_at,
               c.title AS conversation_title
        FROM messages m
        INNER JOIN conversations c ON m.conversation_id = c.id
        WHERE c.user_id = ? AND m.content LIKE ?
        ORDER BY m.created_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("isi", $user_id, $search_term, $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $search_results = array();
    
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $search_results;
}

?>
