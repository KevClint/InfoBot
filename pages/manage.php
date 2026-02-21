<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = getCurrentUserId();
$is_admin = isAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $conn = getDatabaseConnection();

    if ($_POST['action'] === 'delete_conversation') {
        $conv_id = intval($_POST['conversation_id']);
        $stmt = $conn->prepare("DELETE FROM conversations WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $conv_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . BASE_PATH . 'pages/manage.php?success=conversation_deleted');
        exit();
    }

    if ($_POST['action'] === 'delete_knowledge') {
        if (!$is_admin) {
            header('Location: ' . BASE_PATH . 'pages/manage.php');
            exit();
        }
        $kb_id = intval($_POST['knowledge_id']);
        $stmt = $conn->prepare("DELETE FROM knowledge_base WHERE id = ? AND created_by = ?");
        $stmt->bind_param("ii", $kb_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . BASE_PATH . 'pages/manage.php?success=knowledge_deleted');
        exit();
    }

    closeDatabaseConnection($conn);
}

$conn = getDatabaseConnection();
$stmt = $conn->prepare("SELECT c.id, c.title, c.created_at, c.updated_at, COUNT(m.id) as message_count FROM conversations c LEFT JOIN messages m ON c.id = m.conversation_id WHERE c.user_id = ? GROUP BY c.id ORDER BY c.updated_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$conversations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$knowledge_base = array();
if ($is_admin) {
    $stmt = $conn->prepare("SELECT kb.*, u.username as creator_name FROM knowledge_base kb JOIN users u ON kb.created_by = u.id ORDER BY kb.created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $knowledge_base = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
closeDatabaseConnection($conn);

$success_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'conversation_deleted': $success_message = 'Conversation deleted successfully.'; break;
        case 'knowledge_deleted': if ($is_admin) $success_message = 'Knowledge entry deleted successfully.'; break;
        case 'knowledge_added': if ($is_admin) $success_message = 'Knowledge entry added successfully.'; break;
        case 'knowledge_updated': if ($is_admin) $success_message = 'Knowledge entry updated successfully.'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage - InfoBot</title>
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
            <a class="ui-nav-link active" href="<?php echo BASE_PATH; ?>pages/manage.php"><span class="material-symbols-rounded">folder</span>Manage</a>
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/settings.php"><span class="material-symbols-rounded">settings</span>Settings</a>
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/logout.php"><span class="material-symbols-rounded">logout</span>Logout</a>
        </nav>
    </div>
</header>

<main class="ui-container">
    <section class="ui-page-head">
        <h1 class="ui-title">Manage Workspace</h1>
        <p class="ui-subtitle">Review and manage your conversations.</p>
    </section>

    <?php if ($success_message): ?>
        <div class="ui-alert success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <section class="ui-card" style="margin-bottom:14px;">
        <div class="ui-card-header">
            <h3>Conversations</h3>
            <a href="<?php echo BASE_PATH; ?>pages/chat.php" class="ui-btn primary sm"><span class="material-symbols-rounded">add</span>New Chat</a>
        </div>

        <?php if (empty($conversations)): ?>
            <div class="ui-empty">No conversations yet.</div>
        <?php else: ?>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead><tr><th>Title</th><th>Messages</th><th>Created</th><th>Updated</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($conversations as $conv): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($conv['title']); ?></strong></td>
                            <td><?php echo (int)$conv['message_count']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($conv['created_at'])); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($conv['updated_at'])); ?></td>
                            <td>
                                <div class="ui-actions">
                                    <a class="ui-btn secondary sm" href="<?php echo BASE_PATH; ?>pages/chat.php?conversation_id=<?php echo (int)$conv['id']; ?>"><span class="material-symbols-rounded">visibility</span></a>
                                    <form method="POST" onsubmit="return confirm('Delete this conversation?');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_conversation">
                                        <input type="hidden" name="conversation_id" value="<?php echo (int)$conv['id']; ?>">
                                        <button type="submit" class="ui-btn danger sm"><span class="material-symbols-rounded">delete</span></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($is_admin): ?>
    <section class="ui-card">
        <div class="ui-card-header">
            <h3>Knowledge Base</h3>
            <a href="<?php echo BASE_PATH; ?>pages/knowledge_form.php" class="ui-btn primary sm"><span class="material-symbols-rounded">add</span>Add Entry</a>
        </div>

        <?php if (empty($knowledge_base)): ?>
            <div class="ui-empty">No knowledge entries available.</div>
        <?php else: ?>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead><tr><th>Question</th><th>Category</th><th>Created By</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($knowledge_base as $kb): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars(substr($kb['question'], 0, 70)) . (strlen($kb['question']) > 70 ? '...' : ''); ?></strong></td>
                            <td><span class="ui-badge"><?php echo htmlspecialchars($kb['category']); ?></span></td>
                            <td><?php echo htmlspecialchars($kb['creator_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($kb['created_at'])); ?></td>
                            <td>
                                <div class="ui-actions">
                                    <a class="ui-btn secondary sm" href="<?php echo BASE_PATH; ?>pages/knowledge_view.php?id=<?php echo (int)$kb['id']; ?>"><span class="material-symbols-rounded">visibility</span></a>
                                    <?php if ((int)$kb['created_by'] === (int)$user_id): ?>
                                        <a class="ui-btn secondary sm" href="<?php echo BASE_PATH; ?>pages/knowledge_form.php?id=<?php echo (int)$kb['id']; ?>"><span class="material-symbols-rounded">edit</span></a>
                                        <form method="POST" onsubmit="return confirm('Delete this entry?');" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_knowledge">
                                            <input type="hidden" name="knowledge_id" value="<?php echo (int)$kb['id']; ?>">
                                            <button type="submit" class="ui-btn danger sm"><span class="material-symbols-rounded">delete</span></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</main>
</body>
</html>

