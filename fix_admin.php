<?php
/**
 * QUICK FIX: Set Admin Role
 * 
 * This script fixes the admin user's role
 * Access via: http://localhost/chatbot_project/fix_admin.php
 */

require_once __DIR__ . '/config/database.php';

echo "<style>
    body { font-family: Poppins, sans-serif; background: #f5f5f5; padding: 20px; }
    .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #333; margin-bottom: 20px; }
    .success { color: #10b981; font-weight: bold; padding: 12px; background: #d1f0d8; border-left: 4px solid #10b981; margin: 15px 0; border-radius: 4px; }
    .error { color: #ef4444; font-weight: bold; padding: 12px; background: #f8d7da; border-left: 4px solid #ef4444; margin: 15px 0; border-radius: 4px; }
    .info { padding: 12px; background: #d1ecf1; border-left: 4px solid #0c5460; margin: 15px 0; border-radius: 4px; color: #0c5460; }
    .code { background: #f0f0f0; padding: 8px 12px; border-radius: 4px; font-family: monospace; font-size: 13px; }
    .button { display: inline-block; padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: 500; }
    .button:hover { background: #5568d3; }
</style>";

echo "<div class='container'>";
echo "<h1>🔧 Admin Role Fix</h1>";

try {
    $conn = getDatabaseConnection();
    
    // Check if role column exists
    $result = $conn->query("DESCRIBE users");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Add role column if missing
    if (!in_array('role', $columns)) {
        echo "<p class='info'>Adding 'role' column to users table...</p>";
        if ($conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'")) {
            echo "<p class='success'>✓ 'role' column added</p>";
        } else {
            echo "<p class='error'>✗ Failed to add 'role' column: " . $conn->error . "</p>";
            exit;
        }
    }
    
    // Get admin user
    $result = $conn->query("SELECT id, username, role FROM users WHERE username='admin'");
    
    if ($result->num_rows === 0) {
        echo "<p class='error'>✗ Admin user not found. Creating one...</p>";
        
        $username = 'admin';
        $email = 'admin@chatbot.com';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $role = 'admin';
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        
        if ($stmt->execute()) {
            echo "<p class='success'>✓ Admin user created!</p>";
        } else {
            echo "<p class='error'>✗ Failed to create admin: " . $stmt->error . "</p>";
            exit;
        }
        $stmt->close();
    } else {
        $admin = $result->fetch_assoc();
        echo "<p class='info'>Found admin user: <span class='code'>" . $admin['username'] . "</span></p>";
        echo "<p class='info'>Current role: <span class='code'>" . ($admin['role'] ?: 'NULL') . "</span></p>";
        
        // Update role to admin
        echo "<p class='info'>Updating role to 'admin'...</p>";
        if ($conn->query("UPDATE users SET role='admin' WHERE username='admin'")) {
            echo "<p class='success'>✓ Admin role updated successfully!</p>";
        } else {
            echo "<p class='error'>✗ Failed to update role: " . $conn->error . "</p>";
            exit;
        }
    }
    
    // Verify the fix
    $result = $conn->query("SELECT id, username, role FROM users WHERE username='admin'");
    $admin = $result->fetch_assoc();
    
    echo "<p class='info'><strong>Verification:</strong></p>";
    echo "<p class='info'>Username: <span class='code'>" . $admin['username'] . "</span></p>";
    echo "<p class='info'>Role: <span class='code'>" . $admin['role'] . "</span></p>";
    
    if ($admin['role'] === 'admin') {
        echo "<p class='success'>✓✓✓ Admin account is now properly configured!</p>";
        echo "<p style='margin-top: 20px; font-size: 14px;'><strong>You can now login with:</strong></p>";
        echo "<p style='font-size: 14px;'>";
        echo "Username: <span class='code'>admin</span><br>";
        echo "Password: <span class='code'>admin123</span>";
        echo "</p>";
        echo "<p style='margin-top: 20px;'>";
        echo "<a href='/chatbot_project/pages/login.php' class='button'>Go to Login →</a>";
        echo "</p>";
    }
    
    closeDatabaseConnection($conn);
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</div>";
?>
