<?php
/**
 * SEARCH CHAT HISTORY API
 * 
 * Searches through user's past conversations by keyword.
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
$keyword = trim($input['keyword'] ?? '');

if (!$keyword || strlen($keyword) < 2) {
    echo json_encode([
        'success' => false,
        'error' => 'Keyword must be at least 2 characters long'
    ]);
    exit();
}

$results = searchChatHistory($user_id, $keyword, 50);

echo json_encode([
    'success' => true,
    'results' => $results,
    'count' => count($results)
]);
?>
