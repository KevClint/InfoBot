<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$user_id = getCurrentUserId();

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_PATH . 'pages/manage.php');
    exit();
}

$kb_id = intval($_GET['id']);
$conn = getDatabaseConnection();
$stmt = $conn->prepare("SELECT kb.*, u.username as creator_name FROM knowledge_base kb JOIN users u ON kb.created_by = u.id WHERE kb.id = ?");
$stmt->bind_param("i", $kb_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: ' . BASE_PATH . 'pages/manage.php');
    exit();
}

$entry = $result->fetch_assoc();
$stmt->close();
closeDatabaseConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Entry - InfoBot</title>
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
        <a href="<?php echo BASE_PATH; ?>pages/chat.php" class="ui-brand"><span class="material-symbols-rounded">smart_toy</span>InfoBot</a>
        <nav class="ui-nav">
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/chat.php"><span class="material-symbols-rounded">chat</span>Chat</a>
            <a class="ui-nav-link active" href="<?php echo BASE_PATH; ?>pages/settings.php"><span class="material-symbols-rounded">settings</span>Settings</a>
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/logout.php"><span class="material-symbols-rounded">logout</span>Logout</a>
        </nav>
    </div>
</header>

<main class="ui-container" style="max-width:860px;">
    <section class="ui-page-head">
        <h1 class="ui-title">Knowledge Entry</h1>
        <p class="ui-subtitle">Full details and metadata for this knowledge item.</p>
    </section>

    <article class="ui-card">
        <div class="ui-card-header">
            <span class="ui-badge"><?php echo htmlspecialchars($entry['category']); ?></span>
        </div>

        <section style="margin-bottom:16px;">
            <h3 style="margin:0 0 6px;">Question</h3>
            <div><?php echo nl2br(htmlspecialchars($entry['question'])); ?></div>
        </section>

        <section style="margin-bottom:16px;">
            <h3 style="margin:0 0 6px;">Answer</h3>
            <div><?php echo nl2br(htmlspecialchars($entry['answer'])); ?></div>
        </section>

        <section class="ui-muted" style="font-size:13px; border-top:1px solid var(--line); padding-top:12px;">
            Created by <strong><?php echo htmlspecialchars($entry['creator_name']); ?></strong> on <?php echo date('F j, Y', strtotime($entry['created_at'])); ?>
            <?php if ($entry['updated_at'] !== $entry['created_at']): ?>
                â€¢ Updated <?php echo date('F j, Y', strtotime($entry['updated_at'])); ?>
            <?php endif; ?>
        </section>

        <div class="ui-actions" style="margin-top:14px;">
            <a class="ui-btn secondary" href="<?php echo BASE_PATH; ?>pages/manage.php"><span class="material-symbols-rounded">arrow_back</span>Back</a>
            <?php if ((int)$entry['created_by'] === (int)$user_id): ?>
                <a class="ui-btn primary" href="<?php echo BASE_PATH; ?>pages/knowledge_form.php?id=<?php echo (int)$entry['id']; ?>"><span class="material-symbols-rounded">edit</span>Edit</a>
                <form method="POST" action="<?php echo BASE_PATH; ?>pages/manage.php" style="display:inline;" onsubmit="return confirm('Delete this entry?');">
                    <input type="hidden" name="action" value="delete_knowledge">
                    <input type="hidden" name="knowledge_id" value="<?php echo (int)$entry['id']; ?>">
                    <button type="submit" class="ui-btn danger"><span class="material-symbols-rounded">delete</span>Delete</button>
                </form>
            <?php endif; ?>
        </div>
    </article>
</main>
</body>
</html>

