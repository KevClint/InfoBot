<?php
/**
 * CHAT API ENDPOINT
 * 
 * This endpoint receives user messages, sends them to Groq API,
 * and returns the AI response. Also saves messages to database.
 */

header('Content-Type: application/json');
@ini_set('max_execution_time', '300');
@set_time_limit(300);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/chatbot.php';

/**
 * Extract and validate image attachments from request payload.
 *
 * @param mixed $rawAttachments
 * @return array[] Array of ['name' => string, 'mime' => string, 'base64' => string]
 */
function parseImageAttachments($rawAttachments) {
    if (!is_array($rawAttachments)) {
        return [];
    }

    $allowedMime = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif'
    ];
    $maxFiles = 4;
    $maxBytes = 5 * 1024 * 1024; // 5MB each
    $parsed = [];

    foreach ($rawAttachments as $item) {
        if (count($parsed) >= $maxFiles) {
            break;
        }
        if (!is_array($item)) {
            continue;
        }

        $name = trim((string)($item['name'] ?? 'image'));
        $mime = strtolower(trim((string)($item['mime'] ?? '')));
        $base64 = trim((string)($item['base64'] ?? ''));

        if ($name === '') {
            $name = 'image';
        }
        if (!in_array($mime, $allowedMime, true) || $base64 === '') {
            continue;
        }

        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            continue;
        }
        if (strlen($decoded) > $maxBytes) {
            continue;
        }

        $parsed[] = [
            'name' => $name,
            'mime' => $mime,
            'base64' => $base64
        ];
    }

    return $parsed;
}

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
$local_model = trim((string)($input['local_model'] ?? ''));
$attachments = parseImageAttachments($input['attachments'] ?? []);
$user_id = getCurrentUserId();

if ($provider === 'local_llama') {
    $provider = 'local';
    $local_model = defined('LLM_MODEL_LLAMA') ? (string)LLM_MODEL_LLAMA : 'llama3.2:3b';
} elseif ($provider === 'local_gemma') {
    $provider = 'local';
    $local_model = defined('LLM_MODEL_GEMMA') ? (string)LLM_MODEL_GEMMA : 'gemma3:4b';
}

if ($provider !== 'api' && $provider !== 'local' && $provider !== 'hf') {
    $provider = 'api';
}
if ($provider !== 'local') {
    $local_model = '';
}
if ($local_model !== '' && !preg_match('/^[A-Za-z0-9._:-]{1,100}$/', $local_model)) {
    $local_model = '';
}

// Validate input
if (!$conversation_id || ($user_message === '' && empty($attachments))) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request data'
    ]);
    exit();
}

// Save user message to database (text + attachment labels)
$storedUserMessage = $user_message;
if (!empty($attachments)) {
    $attachmentNames = array_map(static function ($att) {
        return (string)($att['name'] ?? 'image');
    }, $attachments);
    $suffix = '[Image' . (count($attachmentNames) > 1 ? 's' : '') . ': ' . implode(', ', $attachmentNames) . ']';
    $storedUserMessage = $storedUserMessage === '' ? $suffix : ($storedUserMessage . "\n\n" . $suffix);
}

if (!saveMessage($conversation_id, $user_id, 'user', $storedUserMessage)) {
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
    $titleSource = $user_message !== '' ? $user_message : 'Image conversation';
    $title = substr($titleSource, 0, 60);
    if (strlen($titleSource) > 60) {
        $title .= '...';
    }
    updateConversationTitle($conversation_id, $user_id, $title);
}

// Prefer direct answer from active knowledge base when matched
$kb_answer = empty($attachments) ? getKnowledgeBaseAnswer($user_message) : null;
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

// Attach images only for current request (not persisted in history payload).
if (!empty($attachments)) {
    $lastIndex = count($chat_history) - 1;
    if ($lastIndex >= 0 && isset($chat_history[$lastIndex]['role']) && $chat_history[$lastIndex]['role'] === 'user') {
        if ($provider === 'local') {
            $chat_history[$lastIndex]['content'] = $user_message !== '' ? $user_message : 'Please analyze the attached image(s).';
            $chat_history[$lastIndex]['images'] = array_map(static function ($att) {
                return (string)($att['base64'] ?? '');
            }, $attachments);
        } else {
            $parts = [];
            $parts[] = [
                'type' => 'text',
                'text' => $user_message !== '' ? $user_message : 'Please analyze the attached image(s).'
            ];
            foreach ($attachments as $att) {
                $parts[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'data:' . $att['mime'] . ';base64,' . $att['base64']
                    ]
                ];
            }
            $chat_history[$lastIndex]['content'] = $parts;
        }
    }
}

// Get response from selected provider (api=Groq, local=Ollama)
$api_response = getChatbotResponse($chat_history, $provider, $local_model);

if ($api_response['success']) {
    $bot_message = $api_response['message'];
    
    // Save bot response to database
    if (saveMessage($conversation_id, $user_id, 'assistant', $bot_message)) {
        echo json_encode([
            'success' => true,
            'message' => $bot_message,
            'provider' => $provider,
            'attachments_count' => count($attachments),
            'attachments_used' => !empty($attachments)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save bot response'
        ]);
    }
} else {
    $errorMessage = (string)$api_response['error'];
    if (!empty($attachments)) {
        $errorMessage = 'Image processing failed for the selected provider/model. Try a vision-capable model. Details: ' . $errorMessage;
    }
    echo json_encode([
        'success' => false,
        'error' => $errorMessage
    ]);
}
?>
