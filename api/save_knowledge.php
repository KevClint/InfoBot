<?php
/**
 * SAVE KNOWLEDGE BASE ENTRY API
 * 
 * Handles creation and updating of knowledge base entries.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit();
}

$user_id = getCurrentUserId();
$kb_id = intval($_POST['kb_id'] ?? 0);
$question = trim($_POST['question'] ?? '');
$answer = trim($_POST['answer'] ?? '');
$category = trim($_POST['category'] ?? 'General');

// Validate input
if (!$question || !$answer) {
    echo json_encode([
        'success' => false,
        'error' => 'Question and answer are required'
    ]);
    exit();
}

$conn = getDatabaseConnection();

if ($kb_id > 0) {
    // Update existing entry
    $stmt = $conn->prepare("UPDATE knowledge_base SET question = ?, answer = ?, category = ? WHERE id = ? AND created_by = ?");
    $stmt->bind_param("sssii", $question, $answer, $category, $kb_id, $user_id);
} else {
    // Insert new entry
    $stmt = $conn->prepare("INSERT INTO knowledge_base (question, answer, category, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $question, $answer, $category, $user_id);
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Knowledge entry saved successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save knowledge entry'
    ]);
}

$stmt->close();
closeDatabaseConnection($conn);
?>
