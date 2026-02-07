<?php
/**
 * INDEX PAGE
 * 
 * Main entry point of the application.
 * Redirects to chat if logged in, otherwise to login page.
 */

require_once __DIR__ . '/includes/auth.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to chat
    header('Location: /infobot/pages/chat.php');
} else {
    // Redirect to login
    header('Location: /infobot/pages/login.php');
}

exit();
?>
