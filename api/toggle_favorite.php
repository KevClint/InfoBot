<?php
/**
 * TOGGLE FAVORITE RESPONSE API
 * 
 * Handles adding/removing favorite responses.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/preferences.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'error' => 'Not authenticated'
    ]);
    exit();
}

$user_id = getCurrentUserId();
$input = json_decode(file_get_contents('php://input'), true);
$message_id = intval($input['message_id'] ?? 0);

if (!$message_id) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid message ID'
    ]);
    exit();
}

// Check if already favorited
if (isFavorited($user_id, $message_id)) {
    // Remove from favorites
    if (removeFavoriteResponse($user_id, $message_id)) {
        echo json_encode([
            'success' => true,
            'action' => 'removed',
            'message' => 'Removed from favorites'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to remove favorite'
        ]);
    }
} else {
    // Add to favorites
    if (addFavoriteResponse($user_id, $message_id)) {
        echo json_encode([
            'success' => true,
            'action' => 'added',
            'message' => 'Added to favorites'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to add favorite'
        ]);
    }
}
?>
