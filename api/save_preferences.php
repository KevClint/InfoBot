<?php
/**
 * SAVE USER PREFERENCES API
 * 
 * Handles saving user preferences (dark mode, font size, theme color).
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/preferences.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'error' => 'Not authenticated'
    ]);
    exit();
}

$user_id = getCurrentUserId();
$input = json_decode(file_get_contents('php://input'), true);

$dark_mode = isset($input['dark_mode']) ? (bool)$input['dark_mode'] : false;
$font_size = trim($input['font_size'] ?? 'medium');
$theme_color = trim($input['theme_color'] ?? 'blue');

// Validate font size
$valid_sizes = ['small', 'medium', 'large'];
if (!in_array($font_size, $valid_sizes)) {
    $font_size = 'medium';
}

// Validate theme color
$valid_colors = ['blue', 'green', 'purple', 'orange', 'cyan'];
if (!in_array($theme_color, $valid_colors)) {
    $theme_color = 'blue';
}

if (updateUserPreferences($user_id, $dark_mode, $font_size, $theme_color)) {
    echo json_encode([
        'success' => true,
        'message' => 'Preferences saved successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save preferences'
    ]);
}
?>
