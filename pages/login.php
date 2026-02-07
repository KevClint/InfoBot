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
    header('Location: ' . BASE_PATH . 'pages/chat.php');
    exit();
}

$error = '';
$success = '';

// Get login type from form or default to 'user'
$login_type = isset($_POST['login_type']) ? sanitizeInput($_POST['login_type']) : 'user';

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
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (verifyPassword($password, $user['password'])) {
                // Check login type requirements
                if ($login_type === 'admin' && $user['role'] !== 'admin') {
                    $error = 'Invalid admin credentials. This account is not an admin account.';
                } else {
                    // Login successful
                    loginUser($user['id'], $user['username'], $user['role']);
                    
                    // Redirect based on login type
                    if ($login_type === 'admin') {
                        $redirect = BASE_PATH . 'pages/admin/index.php';
                    } else {
                        $redirect = $_GET['redirect'] ?? BASE_PATH . 'pages/chat.php';
                    }
                    header('Location: ' . $redirect);
                    exit();
                }
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
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card card">
            <div class="auth-header">
                <div class="auth-icon">
                    <span class="material-symbols-outlined">smart_toy</span>
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to continue to InfoBot</p>
            </div>

            <!-- Login Type Tabs -->
            <div class="login-tabs">
                <button type="button" 
                        class="login-tab-btn <?php echo $login_type === 'user' ? 'active' : ''; ?>" 
                        onclick="switchLoginType('user')">
                    <span class="material-symbols-outlined">chat</span>
                    User Login
                </button>
                <button type="button" 
                        class="login-tab-btn <?php echo $login_type === 'admin' ? 'active' : ''; ?>" 
                        onclick="switchLoginType('admin')">
                    <span class="material-symbols-outlined">admin_panel_settings</span>
                    Admin Login
                </button>
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
                <input type="hidden" name="login_type" id="loginTypeInput" value="<?php echo htmlspecialchars($login_type); ?>">
                
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
                Don't have an account? <a href="<?php echo BASE_PATH; ?>pages/register.php">Create one</a>
            </div>
        </div>
    </div>

    <style>
        .login-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            background: #f3f4f6;
            padding: 8px;
            border-radius: 8px;
        }

        .login-tab-btn {
            flex: 1;
            padding: 12px 16px;
            background: transparent;
            border: 2px solid transparent;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 14px;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .login-tab-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .login-tab-btn.active {
            background: white;
            color: #6366f1;
            border-color: #6366f1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .login-tab-btn .material-symbols-outlined {
            font-size: 20px;
        }
    </style>

    <script>
        function switchLoginType(type) {
            document.getElementById('loginTypeInput').value = type;
            document.querySelectorAll('.login-tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.closest('.login-tab-btn').classList.add('active');
        }
    </script>
</body>
</html>
