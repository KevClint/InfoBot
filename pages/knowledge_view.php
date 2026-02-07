<?php
/**
 * KNOWLEDGE BASE VIEW PAGE
 * 
 * This page displays a single knowledge base entry (CRUD - Read).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Require user to be logged in
requireLogin();

$user_id = getCurrentUserId();

// Get knowledge base entry
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_PATH . 'pages/manage.php');
    exit();
}

$kb_id = intval($_GET['id']);
$conn = getDatabaseConnection();

$stmt = $conn->prepare("
    SELECT kb.*, u.username as creator_name
    FROM knowledge_base kb
    JOIN users u ON kb.created_by = u.id
    WHERE kb.id = ?
");
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
    <title>View Knowledge Entry - AI Chatbot</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo BASE_PATH; ?>pages/chat.php" class="logo">
                    <span class="material-symbols-outlined">smart_toy</span>
                    AI Chatbot
                </a>
                <nav class="nav">
                    <a href="<?php echo BASE_PATH; ?>pages/chat.php" class="nav-link">
                        <span class="material-symbols-outlined">chat</span>
                        <span>Chat</span>
                    </a>
                    <a href="<?php echo BASE_PATH; ?>pages/manage.php" class="nav-link active">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Manage</span>
                    </a>
                    <a href="<?php echo BASE_PATH; ?>pages/logout.php" class="nav-link">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container" style="padding-top: 32px; padding-bottom: 32px; max-width: 800px;">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <a href="<?php echo BASE_PATH; ?>pages/manage.php" style="color: inherit; text-decoration: none;">
                    <span class="material-symbols-outlined" style="vertical-align: middle;">arrow_back</span>
                </a>
                Knowledge Entry Details
            </h1>
        </div>

        <div class="card">
            <!-- Category Badge -->
            <div style="margin-bottom: 16px;">
                <span style="background: #e0e7ff; color: #4338ca; padding: 6px 16px; border-radius: 16px; font-size: 14px; font-weight: 500;">
                    <?php echo htmlspecialchars($entry['category']); ?>
                </span>
            </div>

            <!-- Question -->
            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 22px; font-weight: 600; margin-bottom: 8px; color: var(--text-primary);">
                    Question
                </h2>
                <p style="font-size: 16px; line-height: 1.6; color: var(--text-primary);">
                    <?php echo nl2br(htmlspecialchars($entry['question'])); ?>
                </p>
            </div>

            <!-- Answer -->
            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 22px; font-weight: 600; margin-bottom: 8px; color: var(--text-primary);">
                    Answer
                </h2>
                <p style="font-size: 16px; line-height: 1.6; color: var(--text-primary);">
                    <?php echo nl2br(htmlspecialchars($entry['answer'])); ?>
                </p>
            </div>

            <!-- Metadata -->
            <div style="padding-top: 24px; border-top: 1px solid var(--border-color); display: flex; gap: 24px; flex-wrap: wrap; color: var(--text-secondary); font-size: 14px;">
                <div>
                    <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 18px;">person</span>
                    Created by <strong><?php echo htmlspecialchars($entry['creator_name']); ?></strong>
                </div>
                <div>
                    <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 18px;">calendar_today</span>
                    <?php echo date('F j, Y', strtotime($entry['created_at'])); ?>
                </div>
                <?php if ($entry['updated_at'] !== $entry['created_at']): ?>
                    <div>
                        <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 18px;">update</span>
                        Updated <?php echo date('F j, Y', strtotime($entry['updated_at'])); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <?php if ($entry['created_by'] == $user_id): ?>
                <div style="margin-top: 24px; display: flex; gap: 12px;">
                    <a href="<?php echo BASE_PATH; ?>pages/knowledge_form.php?id=<?php echo $entry['id']; ?>" class="btn btn-primary">
                        <span class="material-symbols-outlined">edit</span>
                        Edit Entry
                    </a>
                    <form method="POST" action="<?php echo BASE_PATH; ?>pages/manage.php" style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to delete this entry?');">
                        <input type="hidden" name="action" value="delete_knowledge">
                        <input type="hidden" name="knowledge_id" value="<?php echo $entry['id']; ?>">
                        <button type="submit" class="btn btn-danger">
                            <span class="material-symbols-outlined">delete</span>
                            Delete Entry
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
