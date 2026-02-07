<?php
/**
 * LOGIN PAGE
 * 
 * This page handles user login with username/password.
 * Validates credentials and creates session on success.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to chat
if (isLoggedIn()) {
    header('Location: /infobot/pages/chat.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Get database connection
        $conn = getDatabaseConnection();
        
        // Prepare statement to get user
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (verifyPassword($password, $user['password'])) {
                // Login successful
                loginUser($user['id'], $user['username']);
                
                // Redirect to chat or specified page
                $redirect = $_GET['redirect'] ?? '/infobot/pages/chat.php';
                header('Location: ' . $redirect);
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
        
        $stmt->close();
        closeDatabaseConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Chatbot</title>
    <link rel="stylesheet" href="/infobot/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card card">
            <div class="auth-header">
                <div class="auth-icon">
                    <span class="material-symbols-outlined">smart_toy</span>
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to continue to AI Chatbot</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="material-symbols-outlined">error</span>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span class="material-symbols-outlined">check_circle</span>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Enter your username"
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <span class="material-symbols-outlined">login</span>
                    Sign In
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="/infobot/pages/register.php">Create one</a>
            </div>
        </div>
    </div>
</body>
</html>
