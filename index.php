<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_PATH . 'pages/chat.php');
    exit();
}

header('Location: ' . BASE_PATH . 'pages/login.php');
exit();
