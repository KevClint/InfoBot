<?php
/**
 * CHATBOT API HELPER FUNCTIONS
 * 
 * This file contains provider helpers to interact with:
 * - Groq API (remote)
 * - Ollama API (local)
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Send message to selected provider and get response.
 * 
 * @param array $messages Array of messages in chat history
 * @param string $provider Provider name: 'api' (Groq) or 'local' (Ollama)
 * @return array Response with 'success' and 'message' or 'error'
 */
function getChatbotResponse($messages, $provider = 'api') {
    $normalized_provider = strtolower(trim((string)$provider));
    if ($normalized_provider === 'local') {
        return getLocalChatbotResponse($messages);
    }

    return getGroqChatbotResponse($messages);
}

/**
 * Send message to Groq API and get response.
 *
 * @param array $messages
 * @return array
 */
function getGroqChatbotResponse($messages) {
    // Prepare the request data
    $data = array(
        'model' => GROQ_MODEL,
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 1000,
        'top_p' => 1,
        'stream' => false
    );
    
    // Initialize cURL
    $ch = curl_init(GROQ_API_URL);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ));
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Check for cURL errors
    if ($curl_error) {
        return array(
            'success' => false,
            'error' => 'Connection error: ' . $curl_error
        );
    }
    
    // Check HTTP response code
    if ($http_code !== 200) {
        return array(
            'success' => false,
            'error' => 'API error (HTTP ' . $http_code . '): ' . $response
        );
    }
    
    // Decode JSON response
    $result = json_decode($response, true);
    
    // Check if response is valid
    if (!isset($result['choices'][0]['message']['content'])) {
        return array(
            'success' => false,
            'error' => 'Invalid API response'
        );
    }
    
    // Return successful response
    return array(
        'success' => true,
        'message' => $result['choices'][0]['message']['content']
    );
}

/**
 * Send message to local Ollama API and get response.
 *
 * @param array $messages
 * @return array
 */
function getLocalChatbotResponse($messages) {
    $data = array(
        'model' => LLM_MODEL,
        'messages' => $messages,
        'options' => array(
            'temperature' => 0.5,
            'num_predict' => 220
        ),
        'keep_alive' => '10m',
        'stream' => false
    );

    $ch = curl_init(LLM_API_URL);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        return array(
            'success' => false,
            'error' => 'Connection error: ' . $curl_error
        );
    }

    if ($http_code !== 200) {
        return array(
            'success' => false,
            'error' => 'Local API error (HTTP ' . $http_code . '): ' . $response
        );
    }

    $result = json_decode($response, true);
    if (!isset($result['message']['content'])) {
        return array(
            'success' => false,
            'error' => 'Invalid local API response'
        );
    }

    return array(
        'success' => true,
        'message' => $result['message']['content']
    );
}

/**
 * Get system prompt for the chatbot
 * This defines the chatbot's personality and behavior
 * 
 * @return string System prompt
 */
function getSystemPrompt() {
    return "You are a helpful, friendly, and knowledgeable AI assistant. " .
           "Your goal is to provide accurate, clear, and helpful responses to user questions. " .
           "Be conversational and warm in your tone, but remain professional. " .
           "If you don't know something, admit it honestly. " .
           "Keep your responses concise but informative. " .
           "Always be respectful and encouraging.";
}

/**
 * Get a knowledge-base answer for the current user message.
 * Uses exact match first, then close text containment matching.
 *
 * @param string $user_message User input
 * @return string|null Matched answer or null when no match is found
 */
function getKnowledgeBaseAnswer($user_message) {
    $normalized = preg_replace('/\s+/', ' ', trim($user_message));
    if ($normalized === '') {
        return null;
    }

    $conn = getDatabaseConnection();

    $sql = "SELECT answer
            FROM knowledge_base
            WHERE is_active = TRUE
              AND (
                LOWER(TRIM(question)) = LOWER(TRIM(?))
                OR LOWER(?) LIKE CONCAT('%', LOWER(TRIM(question)), '%')
                OR LOWER(question) LIKE CONCAT('%', LOWER(?), '%')
              )
            ORDER BY
              CASE WHEN LOWER(TRIM(question)) = LOWER(TRIM(?)) THEN 0 ELSE 1 END,
              CHAR_LENGTH(question) ASC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $normalized, $normalized, $normalized, $normalized);
    $stmt->execute();
    $result = $stmt->get_result();

    $answer = null;
    if ($row = $result->fetch_assoc()) {
        $answer = $row['answer'];
    }

    $stmt->close();
    closeDatabaseConnection($conn);

    return $answer;
}

/**
 * Save message to database
 * 
 * @param int $conversation_id Conversation ID
 * @param int $user_id User ID
 * @param string $role Message role ('user' or 'assistant')
 * @param string $content Message content
 * @return bool True on success, false on failure
 */
function saveMessage($conversation_id, $user_id, $role, $content) {
    $conn = getDatabaseConnection();
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO messages (conversation_id, user_id, role, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $conversation_id, $user_id, $role, $content);
    
    $success = $stmt->execute();
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $success;
}

/**
 * Create new conversation
 * 
 * @param int $user_id User ID
 * @param string $title Conversation title
 * @return int|false New conversation ID or false on failure
 */
function createConversation($user_id, $title = 'New Conversation') {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("INSERT INTO conversations (user_id, title) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $title);
    
    if ($stmt->execute()) {
        $conversation_id = $stmt->insert_id;
        $stmt->close();
        closeDatabaseConnection($conn);
        return $conversation_id;
    }
    
    $stmt->close();
    closeDatabaseConnection($conn);
    return false;
}

/**
 * Get conversation messages
 * 
 * @param int $conversation_id Conversation ID
 * @return array Array of messages
 */
function getConversationMessages($conversation_id) {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("SELECT role, content, created_at FROM messages WHERE conversation_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $conversation_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $messages = array();
    
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $messages;
}

/**
 * Get user's conversations
 * 
 * @param int $user_id User ID
 * @return array Array of conversations
 */
function getUserConversations($user_id) {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("SELECT id, title, created_at, updated_at FROM conversations WHERE user_id = ? ORDER BY updated_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $conversations = array();
    
    while ($row = $result->fetch_assoc()) {
        $conversations[] = $row;
    }
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $conversations;
}

/**
 * Delete conversation
 * 
 * @param int $conversation_id Conversation ID
 * @param int $user_id User ID (for security check)
 * @return bool True on success, false on failure
 */
function deleteConversation($conversation_id, $user_id) {
    $conn = getDatabaseConnection();
    
    // Delete only if conversation belongs to user
    $stmt = $conn->prepare("DELETE FROM conversations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $conversation_id, $user_id);
    
    $success = $stmt->execute();
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $success;
}

/**
 * Update conversation title
 * 
 * @param int $conversation_id Conversation ID
 * @param int $user_id User ID (for security check)
 * @param string $title New title
 * @return bool True on success, false on failure
 */
function updateConversationTitle($conversation_id, $user_id, $title) {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("UPDATE conversations SET title = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $title, $conversation_id, $user_id);
    
    $success = $stmt->execute();
    
    $stmt->close();
    closeDatabaseConnection($conn);
    
    return $success;
}

?>
