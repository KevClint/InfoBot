<?php
/**
 * ADMIN DASHBOARD
 * 
 * Main admin panel providing overview of system statistics
 * and quick access to admin features.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAdmin();

$user_id = getCurrentUserId();
$conn = getDatabaseConnection();

// Get statistics
$users_query = $conn->query("SELECT COUNT(*) as count FROM users");
$user_count = $users_query->fetch_assoc()['count'];

$conversations_query = $conn->query("SELECT COUNT(*) as count FROM conversations");
$conversation_count = $conversations_query->fetch_assoc()['count'];

$messages_query = $conn->query("SELECT COUNT(*) as count FROM messages");
$message_count = $messages_query->fetch_assoc()['count'];

$kb_query = $conn->query("SELECT COUNT(*) as count FROM knowledge_base WHERE is_active = TRUE");
$kb_count = $kb_query->fetch_assoc()['count'];

closeDatabaseConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - InfoBot</title>
    <link rel="stylesheet" href="/infobot/assets/css/style.css">
    <link rel="stylesheet" href="/infobot/assets/css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/infobot/pages/admin/index.php" class="logo">
                    <span class="material-symbols-outlined">admin_panel_settings</span>
                    InfoBot Admin
                </a>
                <nav class="nav">
                    <a href="/infobot/pages/chat.php" class="nav-link">
                        <span class="material-symbols-outlined">chat</span>
                        <span>Chat</span>
                    </a>
                    <a href="/infobot/pages/admin/knowledge.php" class="nav-link">
                        <span class="material-symbols-outlined">school</span>
                        <span>Knowledge Base</span>
                    </a>
                    <a href="/infobot/pages/admin/index.php" class="nav-link active">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="/infobot/pages/logout.php" class="nav-link">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars(getCurrentUsername()); ?></strong>!</p>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <span class="material-symbols-outlined">people</span>
                </div>
                <div class="stat-content">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo $user_count; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon conversations">
                    <span class="material-symbols-outlined">chat_bubble</span>
                </div>
                <div class="stat-content">
                    <h3>Conversations</h3>
                    <p class="stat-number"><?php echo $conversation_count; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon messages">
                    <span class="material-symbols-outlined">message</span>
                </div>
                <div class="stat-content">
                    <h3>Total Messages</h3>
                    <p class="stat-number"><?php echo $message_count; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon knowledge">
                    <span class="material-symbols-outlined">lightbulb</span>
                </div>
                <div class="stat-content">
                    <h3>Knowledge Base Entries</h3>
                    <p class="stat-number"><?php echo $kb_count; ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-section">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="/infobot/pages/admin/knowledge.php" class="action-button">
                    <span class="material-symbols-outlined">add</span>
                    Add Knowledge Entry
                </a>
                <a href="/infobot/pages/admin/knowledge.php" class="action-button">
                    <span class="material-symbols-outlined">edit</span>
                    Manage Knowledge Base
                </a>
                <a href="/infobot/pages/chat.php" class="action-button">
                    <span class="material-symbols-outlined">chat</span>
                    Test Chatbot
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle dark mode from body class
        function loadTheme() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            if (isDark) {
                document.body.classList.add('dark-mode');
            }
        }
        loadTheme();
    </script>
</body>
</html>
