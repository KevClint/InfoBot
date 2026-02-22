<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput ?: '', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON payload.'
    ]);
    exit;
}

$prompt = (string)($payload['prompt'] ?? '');
$prompt = strip_tags($prompt);
$prompt = preg_replace('/[^\P{C}\n\r\t]/u', '', $prompt) ?? '';
$prompt = trim($prompt);

if ($prompt === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => 'Prompt is required.'
    ]);
    exit;
}

if (mb_strlen($prompt) > 8000) {
    $prompt = mb_substr($prompt, 0, 8000);
}

$selectedTier = strtolower(trim((string)($payload['model'] ?? 'standard')));
$standardModel = getenv('OLLAMA_MODEL_STANDARD') ?: 'llama3.2:3b';
$modelMap = [
    'standard' => $standardModel,
    'pro' => getenv('OLLAMA_MODEL_PRO') ?: $standardModel,
    'research' => getenv('OLLAMA_MODEL_RESEARCH') ?: $standardModel,
];
$model = $modelMap[$selectedTier] ?? $standardModel;

$systemPrompts = [
    'standard' => 'You are Infobot, a concise and reliable AI assistant.',
    'pro' => 'You are Infobot Pro, deliver accurate technical depth with clear structure.',
    'research' => 'You are Infobot Research, provide careful reasoning, assumptions, and verification-minded output.',
];
$systemPrompt = $systemPrompts[$selectedTier] ?? $systemPrompts['standard'];

$ollamaBody = [
    'model' => $model,
    'prompt' => $prompt,
    'system' => $systemPrompt,
    'stream' => false,
];

$ch = curl_init('http://localhost:11434/api/generate');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($ollamaBody, JSON_UNESCAPED_UNICODE),
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 180,
]);

$result = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($result === false) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Could not connect to Ollama. Ensure it is running on localhost:11434.',
        'details' => $curlError ?: 'Unknown cURL error'
    ]);
    exit;
}

$ollamaData = json_decode($result, true);
if (!is_array($ollamaData)) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid response received from Ollama.'
    ]);
    exit;
}

if ($httpCode >= 400) {
    $errorText = (string)($ollamaData['error'] ?? 'Ollama returned an error.');
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'error' => $errorText
    ]);
    exit;
}

$responseText = trim((string)($ollamaData['response'] ?? ''));
if ($responseText === '') {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'The model returned an empty response.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'response' => $responseText,
    'model' => $model
], JSON_UNESCAPED_UNICODE);
