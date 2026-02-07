<?php
/**
 * DELETE CONVERSATION API ENDPOINT
 * 
 * This endpoint handles deleting conversations.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/chatbot.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'error' => 'Not authenticated'
    ]);
    exit();
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$conversation_id = intval($input['conversation_id'] ?? 0);
$user_id = getCurrentUserId();

// Validate input
if (!$conversation_id) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid conversation ID'
    ]);
    exit();
}

// Delete conversation
if (deleteConversation($conversation_id, $user_id)) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to delete conversation'
    ]);
}
?>
