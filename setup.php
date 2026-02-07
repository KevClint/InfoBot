<?php
/**
 * DATABASE SETUP SCRIPT
 * 
 * Run this script once to create missing tables and columns
 * Access via: http://localhost/infobot/setup.php
 */

require_once __DIR__ . '/config/database.php';

$conn = getDatabaseConnection();
$messages = [];
$errors = [];

try {
    // Create user_preferences table
    $sql1 = "CREATE TABLE IF NOT EXISTS user_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        dark_mode BOOLEAN DEFAULT FALSE,
        font_size VARCHAR(20) DEFAULT 'medium',
        theme_color VARCHAR(20) DEFAULT 'blue',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql1)) {
        $messages[] = "✅ user_preferences table created successfully";
    } else {
        $messages[] = "ℹ️ user_preferences table already exists or skipped";
    }

    // Create favorite_responses table
    $sql2 = "CREATE TABLE IF NOT EXISTS favorite_responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
        UNIQUE KEY unique_favorite (user_id, message_id),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql2)) {
        $messages[] = "✅ favorite_responses table created successfully";
    } else {
        $messages[] = "ℹ️ favorite_responses table already exists or skipped";
    }

    // Add role column to users table if it doesn't exist
    $checkRole = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_NAME='users' AND COLUMN_NAME='role' AND TABLE_SCHEMA=DATABASE()";
    $result = $conn->query($checkRole);
    
    if ($result->num_rows === 0) {
        $sql3 = "ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'";
        if ($conn->query($sql3)) {
            $messages[] = "✅ Added 'role' column to users table";
        } else {
            $errors[] = "❌ Failed to add 'role' column: " . $conn->error;
        }
    } else {
        $messages[] = "ℹ️ 'role' column already exists in users table";
    }

    // Add is_active column to knowledge_base table if it doesn't exist
    $checkActive = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME='knowledge_base' AND COLUMN_NAME='is_active' AND TABLE_SCHEMA=DATABASE()";
    $result = $conn->query($checkActive);
    
    if ($result->num_rows === 0) {
        $sql4 = "ALTER TABLE knowledge_base ADD COLUMN is_active BOOLEAN DEFAULT TRUE";
        if ($conn->query($sql4)) {
            $messages[] = "✅ Added 'is_active' column to knowledge_base table";
        } else {
            $errors[] = "❌ Failed to add 'is_active' column: " . $conn->error;
        }
    } else {
        $messages[] = "ℹ️ 'is_active' column already exists in knowledge_base table";
    }

    // Verify admin user
    $checkAdmin = $conn->query("SELECT id FROM users WHERE username='admin' LIMIT 1");
    if ($checkAdmin->num_rows === 0) {
        $adminPassword = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // admin123
        $sql5 = "INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@chatbot.com', ?, 'admin')";
        $stmt = $conn->prepare($sql5);
        $stmt->bind_param("s", $adminPassword);
        if ($stmt->execute()) {
            $messages[] = "✅ Admin user created (username: admin, password: admin123)";
        } else {
            $errors[] = "❌ Failed to create admin user: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $messages[] = "ℹ️ Admin user already exists";
    }

    closeDatabaseConnection($conn);

} catch (Exception $e) {
    $errors[] = "❌ Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - InfoBot</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Poppins, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            line-height: 1.6;
            font-size: 14px;
        }
        .success {
            background: #d1f0d8;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .status {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .status h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .next-steps {
            list-style: none;
            padding: 0;
        }
        .next-steps li {
            padding: 8px 0;
            color: #666;
            font-size: 14px;
        }
        .next-steps li:before {
            content: "→ ";
            color: #667eea;
            font-weight: bold;
            margin-right: 8px;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        a, button {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Database Setup</h1>
        <p class="subtitle">Initializing your InfoBot database...</p>

        <?php if (!empty($messages)): ?>
            <div class="messages">
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?php echo strpos($msg, '✅') !== false ? 'success' : 'info'; ?>">
                        <?php echo htmlspecialchars($msg); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="messages">
                <?php foreach ($errors as $error): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="status">
            <h3>✅ Setup Complete!</h3>
            <p style="color: #666; margin-bottom: 15px; font-size: 14px;">
                Your database is now ready. All required tables and columns have been created.
            </p>
            <ul class="next-steps">
                <li>Login with: <strong>admin</strong> / <strong>admin123</strong></li>
                <li>Change the default admin password immediately</li>
                <li>Start using the chatbot application</li>
                <li>You can delete this setup.php file after verification</li>
            </ul>
            <div class="button-group">
                <a href="/infobot/pages/login.php" class="btn-primary">Go to Login</a>
                <a href="/infobot/pages/chat.php" class="btn-secondary">Go to Chat</a>
            </div>
        </div>
    </div>
</body>
</html>
