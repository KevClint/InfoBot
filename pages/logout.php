<?php
/**
 * LOGOUT PAGE
 * 
 * This page handles user logout by destroying the session
 * and redirecting to the login page.
 */

require_once __DIR__ . '/../includes/auth.php';

// Logout user
logoutUser();

// Redirect to login page
header('Location: /chatbot_project/pages/login.php');
exit();
?>
