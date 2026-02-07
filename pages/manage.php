<?php
/**
 * MANAGEMENT PAGE
 * 
 * This page allows users to manage their conversations and
 * view/edit knowledge base entries (CRUD operations).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Require user to be logged in
requireLogin();

$user_id = getCurrentUserId();
$username = getCurrentUsername();

// Handle delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $conn = getDatabaseConnection();
    
    if ($_POST['action'] === 'delete_conversation') {
        $conv_id = intval($_POST['conversation_id']);
        $stmt = $conn->prepare("DELETE FROM conversations WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $conv_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header('Location: /chatbot_project/pages/manage.php?success=conversation_deleted');
        exit();
    }
    
    if ($_POST['action'] === 'delete_knowledge') {
        $kb_id = intval($_POST['knowledge_id']);
        $stmt = $conn->prepare("DELETE FROM knowledge_base WHERE id = ? AND created_by = ?");
        $stmt->bind_param("ii", $kb_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header('Location: /chatbot_project/pages/manage.php?success=knowledge_deleted');
        exit();
    }
    
    closeDatabaseConnection($conn);
}

// Get user's conversations with message count
$conn = getDatabaseConnection();
$stmt = $conn->prepare("
    SELECT c.id, c.title, c.created_at, c.updated_at, 
           COUNT(m.id) as message_count
    FROM conversations c
    LEFT JOIN messages m ON c.id = m.conversation_id
    WHERE c.user_id = ?
    GROUP BY c.id
    ORDER BY c.updated_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$conversations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get knowledge base entries
$stmt = $conn->prepare("
    SELECT kb.*, u.username as creator_name
    FROM knowledge_base kb
    JOIN users u ON kb.created_by = u.id
    ORDER BY kb.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
$knowledge_base = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

closeDatabaseConnection($conn);

$success_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'conversation_deleted':
            $success_message = 'Conversation deleted successfully!';
            break;
        case 'knowledge_deleted':
            $success_message = 'Knowledge entry deleted successfully!';
            break;
        case 'knowledge_added':
            $success_message = 'Knowledge entry added successfully!';
            break;
        case 'knowledge_updated':
            $success_message = 'Knowledge entry updated successfully!';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage - AI Chatbot</title>
    <link rel="stylesheet" href="/chatbot_project/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/chatbot_project/pages/chat.php" class="logo">
                    <span class="material-symbols-outlined">smart_toy</span>
                    AI Chatbot
                </a>
                <nav class="nav">
                    <a href="/chatbot_project/pages/chat.php" class="nav-link">
                        <span class="material-symbols-outlined">chat</span>
                        <span>Chat</span>
                    </a>
                    <a href="/chatbot_project/pages/manage.php" class="nav-link active">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Manage</span>
                    </a>
                    <a href="/chatbot_project/pages/logout.php" class="nav-link">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container" style="padding-top: 32px; padding-bottom: 32px;">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Management Dashboard</h1>
            <p class="page-subtitle">Manage your conversations and knowledge base</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <span class="material-symbols-outlined">check_circle</span>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Conversations Section -->
        <div class="card mb-4">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="font-size: 20px; font-weight: 600;">My Conversations</h2>
                <a href="/chatbot_project/pages/chat.php" class="btn btn-primary btn-sm">
                    <span class="material-symbols-outlined">add</span>
                    New Chat
                </a>
            </div>

            <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <span class="material-symbols-outlined">chat_bubble_outline</span>
                    <p>No conversations yet</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Messages</th>
                            <th>Created</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conversations as $conv): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($conv['title']); ?></strong>
                                </td>
                                <td><?php echo $conv['message_count']; ?> messages</td>
                                <td><?php echo date('M j, Y', strtotime($conv['created_at'])); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($conv['updated_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="/chatbot_project/pages/chat.php?conversation_id=<?php echo $conv['id']; ?>" 
                                           class="btn btn-sm btn-secondary" 
                                           title="View">
                                            <span class="material-symbols-outlined">visibility</span>
                                        </a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this conversation?');">
                                            <input type="hidden" name="action" value="delete_conversation">
                                            <input type="hidden" name="conversation_id" value="<?php echo $conv['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <span class="material-symbols-outlined">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Knowledge Base Section -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="font-size: 20px; font-weight: 600;">Knowledge Base</h2>
                <a href="/chatbot_project/pages/knowledge_form.php" class="btn btn-primary btn-sm">
                    <span class="material-symbols-outlined">add</span>
                    Add Entry
                </a>
            </div>

            <?php if (empty($knowledge_base)): ?>
                <div class="empty-state">
                    <span class="material-symbols-outlined">lightbulb</span>
                    <p>No knowledge entries yet</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Category</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($knowledge_base as $kb): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars(substr($kb['question'], 0, 50)) . (strlen($kb['question']) > 50 ? '...' : ''); ?></strong>
                                </td>
                                <td>
                                    <span style="background: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 12px; font-size: 13px;">
                                        <?php echo htmlspecialchars($kb['category']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($kb['creator_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($kb['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="/chatbot_project/pages/knowledge_view.php?id=<?php echo $kb['id']; ?>" 
                                           class="btn btn-sm btn-secondary" 
                                           title="View">
                                            <span class="material-symbols-outlined">visibility</span>
                                        </a>
                                        <?php if ($kb['created_by'] == $user_id): ?>
                                            <a href="/chatbot_project/pages/knowledge_form.php?id=<?php echo $kb['id']; ?>" 
                                               class="btn btn-sm btn-secondary" 
                                               title="Edit">
                                                <span class="material-symbols-outlined">edit</span>
                                            </a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                                <input type="hidden" name="action" value="delete_knowledge">
                                                <input type="hidden" name="knowledge_id" value="<?php echo $kb['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
