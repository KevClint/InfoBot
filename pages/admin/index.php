<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAdmin();

$conn = getDatabaseConnection();
$user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$conversation_count = $conn->query("SELECT COUNT(*) as count FROM conversations")->fetch_assoc()['count'];
$message_count = $conn->query("SELECT COUNT(*) as count FROM messages")->fetch_assoc()['count'];
$kb_count = $conn->query("SELECT COUNT(*) as count FROM knowledge_base WHERE is_active = TRUE")->fetch_assoc()['count'];
closeDatabaseConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - InfoBot</title>
    <link rel="icon" href="<?php echo BASE_PATH; ?>assets/icons/logo-robot-64px.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/premium-ui.css">
    <script src="<?php echo BASE_PATH; ?>assets/js/theme-init.js"></script>
</head>
<body>
<header class="ui-header">
    <div class="ui-header-inner">
        <a href="<?php echo BASE_PATH; ?>pages/admin/index.php" class="ui-brand"><span class="material-symbols-rounded">admin_panel_settings</span>InfoBot Admin</a>
        <nav class="ui-nav">
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/chat.php"><span class="material-symbols-rounded">chat</span>Chat</a>
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/admin/knowledge.php"><span class="material-symbols-rounded">school</span>Knowledge</a>
            <a class="ui-nav-link active" href="<?php echo BASE_PATH; ?>pages/admin/index.php"><span class="material-symbols-rounded">dashboard</span>Dashboard</a>
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/logout.php"><span class="material-symbols-rounded">logout</span>Logout</a>
        </nav>
    </div>
</header>

<main class="ui-container">
    <section class="ui-page-head">
        <h1 class="ui-title">Admin Dashboard</h1>
        <p class="ui-subtitle">System overview and quick actions.</p>
    </section>

    <section class="ui-stat-grid" style="margin-bottom:14px;">
        <article class="ui-stat"><div class="ui-stat-title">Total Users</div><div class="ui-stat-value"><?php echo (int)$user_count; ?></div></article>
        <article class="ui-stat"><div class="ui-stat-title">Conversations</div><div class="ui-stat-value"><?php echo (int)$conversation_count; ?></div></article>
        <article class="ui-stat"><div class="ui-stat-title">Messages</div><div class="ui-stat-value"><?php echo (int)$message_count; ?></div></article>
        <article class="ui-stat"><div class="ui-stat-title">Active KB Entries</div><div class="ui-stat-value"><?php echo (int)$kb_count; ?></div></article>
    </section>

    <section class="ui-card">
        <div class="ui-card-header"><h3>Quick Actions</h3></div>
        <div class="ui-actions">
            <a class="ui-btn primary" href="<?php echo BASE_PATH; ?>pages/admin/knowledge.php"><span class="material-symbols-rounded">add</span>Add Knowledge Entry</a>
            <a class="ui-btn secondary" href="<?php echo BASE_PATH; ?>pages/admin/knowledge.php"><span class="material-symbols-rounded">edit</span>Manage Knowledge Base</a>
            <a class="ui-btn secondary" href="<?php echo BASE_PATH; ?>pages/chat.php"><span class="material-symbols-rounded">chat</span>Open Chat</a>
        </div>
    </section>
</main>
</body>
</html>

