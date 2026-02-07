<?php
/**
 * DELETE ALL CONVERSATIONS API
 * 
 * Deletes all conversations and messages for the logged-in user.
 * This action is permanent and cannot be undone.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$user_id = getCurrentUserId();
$conn = getDatabaseConnection();

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete all messages for conversations belonging to this user
    $conn->query("DELETE FROM messages WHERE conversation_id IN (SELECT id FROM conversations WHERE user_id = $user_id)");

    // Delete all favorite responses for messages in this user's conversations
    $conn->query("DELETE FROM favorite_responses WHERE user_id = $user_id");

    // Delete all conversations for this user
    $conn->query("DELETE FROM conversations WHERE user_id = $user_id");

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'All conversations and messages deleted']);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

closeDatabaseConnection($conn);
?>
