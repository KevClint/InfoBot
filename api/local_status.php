<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'online' => false,
        'error' => 'Not authenticated'
    ]);
    exit();
}

$model = defined('LLM_MODEL') ? (string)LLM_MODEL : 'Unknown';
$apiUrl = defined('LLM_API_URL') ? (string)LLM_API_URL : '';
$healthUrl = preg_replace('#/api/chat/?$#', '/api/tags', $apiUrl);
if (!is_string($healthUrl) || $healthUrl === '') {
    $healthUrl = $apiUrl;
}

$online = false;
$error = '';

if ($healthUrl === '') {
    $error = 'Local API URL not configured';
} elseif (!function_exists('curl_init')) {
    $error = 'cURL is not available';
} else {
    $ch = curl_init($healthUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $online = ($response !== false && $httpCode >= 200 && $httpCode < 400);
    if (!$online) {
        $error = $curlErr !== '' ? $curlErr : ('HTTP ' . $httpCode);
    }
}

echo json_encode([
    'success' => true,
    'online' => $online,
    'model' => $model,
    'health_url' => $healthUrl,
    'error' => $online ? '' : $error
]);

