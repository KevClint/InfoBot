<?php
/**
 * KNOWLEDGE BASE MANAGEMENT
 * 
 * Admin panel for managing chatbot knowledge base entries (CRUD operations).
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAdmin();

$user_id = getCurrentUserId();
$message = '';
$error = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $kb_id = intval($_POST['kb_id'] ?? 0);
    
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("DELETE FROM knowledge_base WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ii", $kb_id, $user_id);
    
    if ($stmt->execute()) {
        $message = "Knowledge entry deleted successfully!";
    } else {
        $error = "Failed to delete entry";
    }
    
    $stmt->close();
    closeDatabaseConnection($conn);
}

// Handle toggle active status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    $kb_id = intval($_POST['kb_id'] ?? 0);
    
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("UPDATE knowledge_base SET is_active = !is_active WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ii", $kb_id, $user_id);
    
    if ($stmt->execute()) {
        $message = "Knowledge entry status updated!";
    } else {
        $error = "Failed to update entry";
    }
    
    $stmt->close();
    closeDatabaseConnection($conn);
}

// Get all knowledge base entries
$conn = getDatabaseConnection();
$stmt = $conn->prepare("SELECT id, question, answer, category, is_active, created_at FROM knowledge_base WHERE created_by = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$kb_entries = array();
while ($row = $result->fetch_assoc()) {
    $kb_entries[] = $row;
}
$stmt->close();
closeDatabaseConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Base Management - InfoBot Admin</title>
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
                    <a href="/infobot/pages/admin/knowledge.php" class="nav-link active">
                        <span class="material-symbols-outlined">school</span>
                        <span>Knowledge Base</span>
                    </a>
                    <a href="/infobot/pages/admin/index.php" class="nav-link">
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
            <h1>Knowledge Base Management</h1>
            <button class="btn btn-primary" onclick="openAddModal()">
                <span class="material-symbols-outlined">add</span>
                Add New Entry
            </button>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Knowledge Base Table -->
        <div class="kb-table-container">
            <?php if (empty($kb_entries)): ?>
                <p class="text-muted">No knowledge entries yet. <a href="#" onclick="openAddModal(); return false;">Create one now</a>.</p>
            <?php else: ?>
                <table class="kb-table">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kb_entries as $entry): ?>
                            <tr>
                                <td class="kb-question"><?php echo htmlspecialchars(substr($entry['question'], 0, 50)); ?></td>
                                <td><?php echo htmlspecialchars($entry['category']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $entry['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $entry['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($entry['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-small btn-secondary" onclick="editEntry(<?php echo $entry['id']; ?>, '<?php echo htmlspecialchars($entry['question'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($entry['answer'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($entry['category']); ?>')">
                                        <span class="material-symbols-outlined">edit</span>
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this entry?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="kb_id" value="<?php echo $entry['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="kbModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add Knowledge Entry</h2>
                <button class="close-button" onclick="closeModal()">×</button>
            </div>
            <form method="POST" id="kbForm" action="/infobot/api/save_knowledge.php">
                <input type="hidden" id="kbId" name="kb_id" value="">
                
                <div class="form-group">
                    <label for="question">Question *</label>
                    <input type="text" id="question" name="question" required placeholder="Enter the question" maxlength="500">
                </div>

                <div class="form-group">
                    <label for="answer">Answer *</label>
                    <textarea id="answer" name="answer" required placeholder="Enter the answer" rows="6"></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="General">General</option>
                        <option value="Technical">Technical</option>
                        <option value="Usage">Usage</option>
                        <option value="Features">Features</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('kbModal');

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Knowledge Entry';
            document.getElementById('kbForm').reset();
            document.getElementById('kbId').value = '';
            modal.style.display = 'block';
        }

        function editEntry(id, question, answer, category) {
            document.getElementById('modalTitle').textContent = 'Edit Knowledge Entry';
            document.getElementById('kbId').value = id;
            document.getElementById('question').value = question;
            document.getElementById('answer').value = answer;
            document.getElementById('category').value = category;
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
            document.getElementById('kbForm').reset();
        }

        window.onclick = function(event) {
            if (event.target === modal) {
                closeModal();
            }
        }

        // Reload page and scroll to top after submission
        document.getElementById('kbForm').addEventListener('submit', function() {
            setTimeout(() => {
                location.reload();
            }, 500);
        });
    </script>
</body>
</html>
