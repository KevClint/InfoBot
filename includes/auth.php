<?php
/**
 * AUTHENTICATION HELPER FUNCTIONS
 * 
 * This file contains all functions related to user authentication,
 * session management, and security.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged-in user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current logged-in username
 * 
 * @return string|null Username or null if not logged in
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Get current user's role
 * 
 * @return string|null User role or null if not logged in
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? 'user';
}

/**
 * Check if current user is admin
 * 
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return getCurrentUserRole() === 'admin';
}

/**
 * Require admin access, redirect to home if not admin
 */
function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: /infobot/pages/chat.php');
        exit();
    }
}

/**
 * Require user to be logged in, redirect to login if not
 * 
 * @param string $redirect_to Page to redirect to after login
 */
function requireLogin($redirect_to = '') {
    if (!isLoggedIn()) {
        $redirect = $redirect_to ? '?redirect=' . urlencode($redirect_to) : '';
        header('Location: /infobot/pages/login.php' . $redirect);
        exit();
    }
}

/**
 * Login user and create session
 * 
 * @param int $user_id User ID
 * @param string $username Username
 * @param string $role User role (default: 'user')
 */
function loginUser($user_id, $username, $role = 'user') {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = $role;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user and destroy session
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password securely
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token for forms
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

?>
