<?php
/**
 * DATABASE DIAGNOSTIC & REPAIR SCRIPT
 * 
 * This script diagnoses database issues and creates/fixes the admin user
 * Access via: http://localhost/chatbot_project/diagnose.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$messages = [];
$errors = [];

try {
    echo "<style>
        body { font-family: Poppins, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .section { margin-bottom: 25px; padding: 15px; background: #f9f9f9; border-radius: 6px; border-left: 4px solid #667eea; }
        .section h3 { margin-top: 0; color: #667eea; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .info { color: #3b82f6; }
        .code { background: #f0f0f0; padding: 8px 12px; border-radius: 4px; font-family: monospace; font-size: 13px; margin: 5px 0; }
        .button { display: inline-block; padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: 500; }
        .button:hover { background: #5568d3; }
    </style>";
    
    echo "<div class='container'>";
    echo "<h1>🔧 Database Diagnostic Tool</h1>";
    
    $conn = getDatabaseConnection();
    
    // 1. Check database connection
    echo "<div class='section'>";
    echo "<h3>1️⃣ Database Connection</h3>";
    echo "<p class='success'>✓ Database connection successful</p>";
    echo "</div>";
    
    // 2. Check tables
    echo "<div class='section'>";
    echo "<h3>2️⃣ Database Tables</h3>";
    
    $tables = ['users', 'conversations', 'messages', 'knowledge_base', 'user_preferences', 'favorite_responses'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p class='success'>✓ Table '$table' exists</p>";
        } else {
            echo "<p class='error'>✗ Table '$table' NOT FOUND</p>";
        }
    }
    echo "</div>";
    
    // 3. Check users table columns
    echo "<div class='section'>";
    echo "<h3>3️⃣ Users Table Columns</h3>";
    
    $result = $conn->query("DESCRIBE users");
    $columns_found = [];
    while ($row = $result->fetch_assoc()) {
        $columns_found[] = $row['Field'];
        $icon = in_array($row['Field'], ['id', 'username', 'email', 'password', 'role']) ? '✓' : 'ℹ️';
        echo "<p>$icon Column: <span class='code'>{$row['Field']}</span></p>";
    }
    
    if (!in_array('role', $columns_found)) {
        echo "<p class='error'>⚠️ Missing 'role' column - Adding it now...</p>";
        if ($conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'")) {
            echo "<p class='success'>✓ 'role' column added successfully</p>";
        } else {
            echo "<p class='error'>✗ Failed to add 'role' column: " . $conn->error . "</p>";
        }
    }
    echo "</div>";
    
    // 4. Check admin user
    echo "<div class='section'>";
    echo "<h3>4️⃣ Admin User</h3>";
    
    $result = $conn->query("SELECT id, username, email, role FROM users WHERE username='admin'");
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo "<p class='success'>✓ Admin user found</p>";
        echo "<p>ID: <span class='code'>" . $admin['id'] . "</span></p>";
        echo "<p>Username: <span class='code'>" . $admin['username'] . "</span></p>";
        echo "<p>Email: <span class='code'>" . $admin['email'] . "</span></p>";
        echo "<p>Role: <span class='code'>" . $admin['role'] . "</span></p>";
        
        // Verify password works
        $password = 'admin123';
        $stored_password = $conn->query("SELECT password FROM users WHERE username='admin'")->fetch_assoc()['password'];
        
        if (password_verify($password, $stored_password)) {
            echo "<p class='success'>✓ Password 'admin123' is correct</p>";
        } else {
            echo "<p class='error'>✗ Password verification failed</p>";
            echo "<p>Resetting password to 'admin123'...</p>";
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            if ($conn->query("UPDATE users SET password='$new_hash' WHERE username='admin'")) {
                echo "<p class='success'>✓ Password reset successfully</p>";
            } else {
                echo "<p class='error'>✗ Failed to reset password</p>";
            }
        }
    } else {
        echo "<p class='error'>✗ Admin user NOT FOUND</p>";
        echo "<p>Creating admin user now...</p>";
        
        $username = 'admin';
        $email = 'admin@chatbot.com';
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $role = 'admin';
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password_hash, $role);
        
        if ($stmt->execute()) {
            echo "<p class='success'>✓ Admin user created successfully</p>";
            echo "<p>Username: <span class='code'>admin</span></p>";
            echo "<p>Password: <span class='code'>admin123</span></p>";
        } else {
            echo "<p class='error'>✗ Failed to create admin user: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
    echo "</div>";
    
    // 5. Test login
    echo "<div class='section'>";
    echo "<h3>5️⃣ Login Test</h3>";
    
    $test_user = $conn->query("SELECT id, username, password, role FROM users WHERE username='admin'");
    if ($test_user->num_rows > 0) {
        $user = $test_user->fetch_assoc();
        $test_password = 'admin123';
        
        if (password_verify($test_password, $user['password'])) {
            echo "<p class='success'>✓ Login test PASSED</p>";
            echo "<p>You can now login with: <span class='code'>admin / admin123</span></p>";
        } else {
            echo "<p class='error'>✗ Login test FAILED - Password mismatch</p>";
        }
    } else {
        echo "<p class='error'>✗ Admin user not found for testing</p>";
    }
    echo "</div>";
    
    // 6. Summary and next steps
    echo "<div class='section'>";
    echo "<h3>✅ Summary & Next Steps</h3>";
    echo "<ol>";
    echo "<li>Go to: <span class='code'>http://localhost/chatbot_project/pages/login.php</span></li>";
    echo "<li>Click on <strong>Admin Login</strong> tab</li>";
    echo "<li>Enter: <strong>admin</strong> / <strong>admin123</strong></li>";
    echo "<li>You should now be able to login to the admin panel</li>";
    echo "</ol>";
    echo "<p style='margin-top: 15px;'>";
    echo "<a href='/chatbot_project/pages/login.php' class='button'>Go to Login</a> ";
    echo "<a href='/chatbot_project/pages/admin/index.php' class='button'>Go to Admin Panel</a>";
    echo "</p>";
    echo "</div>";
    
    closeDatabaseConnection($conn);
    
} catch (Exception $e) {
    echo "<div class='container'>";
    echo "<h1 style='color: #ef4444;'>❌ Error</h1>";
    echo "<p>An error occurred: " . $e->getMessage() . "</p>";
    echo "<p>Make sure your database is running and properly configured in <span class='code'>config/database.php</span></p>";
    echo "</div>";
}
?>
