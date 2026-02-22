<?php
/**
 * CHAT API ENDPOINT
 * 
 * This endpoint receives user messages, sends them to Groq API,
 * and returns the AI response. Also saves messages to database.
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
$user_message = trim($input['message'] ?? '');
$provider = strtolower(trim((string)($input['provider'] ?? 'api')));
$user_id = getCurrentUserId();

if ($provider !== 'api' && $provider !== 'local') {
    $provider = 'api';
}

// Validate input
if (!$conversation_id || !$user_message) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request data'
    ]);
    exit();
}

// Save user message to database
if (!saveMessage($conversation_id, $user_id, 'user', $user_message)) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save message'
    ]);
    exit();
}

// Update conversation title if this is the first message
$existing_messages = getConversationMessages($conversation_id);
if (count($existing_messages) === 1) {
    // Generate title from first message (max 60 characters)
    $title = substr($user_message, 0, 60);
    if (strlen($user_message) > 60) {
        $title .= '...';
    }
    updateConversationTitle($conversation_id, $user_id, $title);
}

// Prefer direct answer from active knowledge base when matched
$kb_answer = getKnowledgeBaseAnswer($user_message);
if ($kb_answer !== null) {
    if (saveMessage($conversation_id, $user_id, 'assistant', $kb_answer)) {
        echo json_encode([
            'success' => true,
            'message' => $kb_answer
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save bot response'
        ]);
    }
    exit();
}

// Get conversation history for context (last 10 messages)
$messages = getConversationMessages($conversation_id);
$chat_history = [];

// Add system prompt
$chat_history[] = [
    'role' => 'system',
    'content' => getSystemPrompt()
];

// Add recent messages (limit to last 10 for context)
$recent_messages = array_slice($messages, -10);
foreach ($recent_messages as $msg) {
    $chat_history[] = [
        'role' => $msg['role'],
        'content' => $msg['content']
    ];
}

// Get response from selected provider (api=Groq, local=Ollama)
$api_response = getChatbotResponse($chat_history, $provider);

if ($api_response['success']) {
    $bot_message = $api_response['message'];
    
    // Save bot response to database
    if (saveMessage($conversation_id, $user_id, 'assistant', $bot_message)) {
        echo json_encode([
            'success' => true,
            'message' => $bot_message,
            'provider' => $provider
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save bot response'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => $api_response['error']
    ]);
}
?>
