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

if (!function_exists('curl_init')) {
    echo json_encode([
        'success' => true,
        'online' => false,
        'configured' => false,
        'provider' => 'unknown',
        'error' => 'cURL is not available'
    ]);
    exit();
}

/**
 * Check if an HTTP endpoint is reachable.
 *
 * @param string $url
 * @return array{online: bool, error: string}
 */
function checkEndpointOnline($url)
{
    $url = trim((string)$url);
    if ($url === '') {
        return [
            'online' => false,
            'error' => 'URL not configured'
        ];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $online = ($response !== false && $httpCode > 0);
    if ($online) {
        return [
            'online' => true,
            'error' => ''
        ];
    }

    return [
        'online' => false,
        'error' => $curlError !== '' ? $curlError : ('HTTP ' . $httpCode)
    ];
}

$provider = strtolower(trim((string)($_GET['provider'] ?? 'api')));

if ($provider !== 'api' && $provider !== 'hf') {
    echo json_encode([
        'success' => false,
        'online' => false,
        'error' => 'Unsupported provider'
    ]);
    exit();
}

if ($provider === 'api') {
    $configured = defined('GROQ_API_KEY') && trim((string)GROQ_API_KEY) !== '' && defined('GROQ_API_URL') && trim((string)GROQ_API_URL) !== '';
    $url = defined('GROQ_API_URL') ? (string)GROQ_API_URL : '';
} else {
    $configured = defined('HF_API_KEY') && trim((string)HF_API_KEY) !== '' && defined('HF_API_URL') && trim((string)HF_API_URL) !== '';
    $url = defined('HF_API_URL') ? (string)HF_API_URL : '';
}

if (!$configured) {
    echo json_encode([
        'success' => true,
        'provider' => $provider,
        'configured' => false,
        'online' => false,
        'error' => 'Not configured'
    ]);
    exit();
}

$status = checkEndpointOnline($url);

echo json_encode([
    'success' => true,
    'provider' => $provider,
    'configured' => true,
    'online' => (bool)$status['online'],
    'error' => (string)$status['error']
]);

