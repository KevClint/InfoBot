<?php
/**
 * FIX MISSING DATABASE COLUMNS
 * 
 * Adds missing columns to existing tables based on schema updates.
 * This script should only be run once after schema changes.
 */

require_once __DIR__ . '/config/database.php';

$conn = getDatabaseConnection();
$fixed = [];
$errors = [];

try {
    // Check and add role column to users table
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($result->num_rows == 0) {
        if ($conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'")) {
            $fixed[] = "✓ Added 'role' column to users table";
        } else {
            $errors[] = "✗ Failed to add 'role' column to users table: " . $conn->error;
        }
    } else {
        $fixed[] = "✓ 'role' column already exists in users table";
    }

    // Check and add is_active column to knowledge_base table
    $result = $conn->query("SHOW COLUMNS FROM knowledge_base LIKE 'is_active'");
    if ($result->num_rows == 0) {
        if ($conn->query("ALTER TABLE knowledge_base ADD COLUMN is_active BOOLEAN DEFAULT TRUE")) {
            $fixed[] = "✓ Added 'is_active' column to knowledge_base table";
        } else {
            $errors[] = "✗ Failed to add 'is_active' column to knowledge_base table: " . $conn->error;
        }
    } else {
        $fixed[] = "✓ 'is_active' column already exists in knowledge_base table";
    }

    // Verify admin user exists and has admin role
    $admin_check = $conn->query("SELECT id, username, role FROM users WHERE username='admin'");
    if ($admin_check->num_rows > 0) {
        $admin = $admin_check->fetch_assoc();
        if ($admin['role'] !== 'admin') {
            if ($conn->query("UPDATE users SET role='admin' WHERE username='admin'")) {
                $fixed[] = "✓ Updated admin user role to 'admin'";
            } else {
                $errors[] = "✗ Failed to update admin user role: " . $conn->error;
            }
        } else {
            $fixed[] = "✓ Admin user already has 'admin' role";
        }
    }

} catch (Exception $e) {
    $errors[] = "✗ Error: " . $e->getMessage();
}

closeDatabaseConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Database Columns</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
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
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #888;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-section {
            margin-bottom: 30px;
        }
        .status-title {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-item {
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.6;
        }
        .status-item.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #10b981;
        }
        .status-item.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #ef4444;
        }
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }
        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        .summary {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .summary strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Fix Complete</h1>
        <p class="subtitle">Column initialization status</p>

        <?php if (!empty($fixed)): ?>
        <div class="status-section">
            <div class="status-title">✓ Fixed Items</div>
            <?php foreach ($fixed as $item): ?>
                <div class="status-item success"><?php echo htmlspecialchars($item); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="status-section">
            <div class="status-title">✗ Errors</div>
            <?php foreach ($errors as $error): ?>
                <div class="status-item error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="summary">
            <strong>All columns are present and correct!</strong><br>
            Your database is ready to use.
        </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="/infobot/pages/login.php" class="btn btn-primary">Go to Login</a>
            <a href="/infobot/pages/admin/index.php" class="btn btn-secondary">Admin Dashboard</a>
        </div>
    </div>
</body>
</html>
