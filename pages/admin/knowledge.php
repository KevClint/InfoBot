<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAdmin();

$user_id = getCurrentUserId();
$message = '';
$error = '';

if (isset($_GET['saved']) && $_GET['saved'] === '1') {
    $message = 'Knowledge entry saved successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $kb_id = intval($_POST['kb_id'] ?? 0);
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("DELETE FROM knowledge_base WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ii", $kb_id, $user_id);
    if ($stmt->execute()) $message = 'Knowledge entry deleted successfully.';
    else $error = 'Failed to delete entry.';
    $stmt->close();
    closeDatabaseConnection($conn);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    $kb_id = intval($_POST['kb_id'] ?? 0);
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("UPDATE knowledge_base SET is_active = !is_active WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ii", $kb_id, $user_id);
    if ($stmt->execute()) $message = 'Knowledge entry status updated.';
    else $error = 'Failed to update entry.';
    $stmt->close();
    closeDatabaseConnection($conn);
}

$conn = getDatabaseConnection();
$stmt = $conn->prepare("SELECT id, question, answer, category, is_active, created_at FROM knowledge_base WHERE created_by = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$kb_entries = [];
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
    <title>Knowledge Base - Admin</title>
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
            <a class="ui-nav-link active" href="<?php echo BASE_PATH; ?>pages/admin/knowledge.php"><span class="material-symbols-rounded">school</span>Knowledge</a>
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/admin/index.php"><span class="material-symbols-rounded">dashboard</span>Dashboard</a>
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/logout.php"><span class="material-symbols-rounded">logout</span>Logout</a>
        </nav>
    </div>
</header>

<main class="ui-container">
    <section class="ui-page-head" style="display:flex; justify-content:space-between; align-items:flex-end; gap:10px; flex-wrap:wrap;">
        <div>
            <h1 class="ui-title">Knowledge Base Management</h1>
            <p class="ui-subtitle">Create and maintain promptable Q&A entries.</p>
        </div>
        <button class="ui-btn primary" onclick="openAddModal()"><span class="material-symbols-rounded">add</span>Add Entry</button>
    </section>

    <?php if ($message): ?><div class="ui-alert success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="ui-alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <section class="ui-card">
        <?php if (empty($kb_entries)): ?>
            <div class="ui-empty">No knowledge entries yet. <a href="#" onclick="openAddModal(); return false;">Create one now</a>.</div>
        <?php else: ?>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead><tr><th>Question</th><th>Category</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($kb_entries as $entry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($entry['question'], 0, 65)); ?></td>
                            <td><?php echo htmlspecialchars($entry['category']); ?></td>
                            <td><span class="ui-badge" style="background: <?php echo $entry['is_active'] ? '#dcfce7' : '#f1f5f9'; ?>; color: <?php echo $entry['is_active'] ? '#166534' : '#475569'; ?>;"><?php echo $entry['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                            <td><?php echo date('M j, Y', strtotime($entry['created_at'])); ?></td>
                            <td>
                                <div class="ui-actions">
                                    <button class="ui-btn secondary sm" onclick='editEntry(<?php echo (int)$entry["id"]; ?>, <?php echo json_encode($entry["question"]); ?>, <?php echo json_encode($entry["answer"]); ?>, <?php echo json_encode($entry["category"]); ?>)'><span class="material-symbols-rounded">edit</span></button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="kb_id" value="<?php echo (int)$entry['id']; ?>">
                                        <button type="submit" class="ui-btn secondary sm"><span class="material-symbols-rounded">toggle_on</span></button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this entry?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="kb_id" value="<?php echo (int)$entry['id']; ?>">
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
</main>

<div id="kbModal" class="ui-modal">
    <div class="ui-modal-content">
        <div class="ui-card-header">
            <h3 id="modalTitle">Add Knowledge Entry</h3>
            <button class="ui-btn secondary sm" type="button" onclick="closeModal()"><span class="material-symbols-rounded">close</span></button>
        </div>

        <form method="POST" id="kbForm" action="<?php echo BASE_PATH; ?>api/save_knowledge.php">
            <input type="hidden" id="kbId" name="kb_id" value="">
            <div class="ui-form-group">
                <label class="ui-form-label" for="question">Question *</label>
                <input class="ui-input" type="text" id="question" name="question" required maxlength="500">
            </div>
            <div class="ui-form-group">
                <label class="ui-form-label" for="answer">Answer *</label>
                <textarea class="ui-textarea" id="answer" name="answer" required rows="6"></textarea>
            </div>
            <div class="ui-form-group">
                <label class="ui-form-label" for="category">Category</label>
                <select class="ui-select" id="category" name="category">
                    <option value="General">General</option>
                    <option value="Technical">Technical</option>
                    <option value="Usage">Usage</option>
                    <option value="Features">Features</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="ui-actions" style="justify-content:flex-end;">
                <button type="button" class="ui-btn secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="ui-btn primary">Save Entry</button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('kbModal');
const kbForm = document.getElementById('kbForm');

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Knowledge Entry';
    document.getElementById('kbForm').reset();
    document.getElementById('kbId').value = '';
    modal.classList.add('open');
}

function editEntry(id, question, answer, category) {
    document.getElementById('modalTitle').textContent = 'Edit Knowledge Entry';
    document.getElementById('kbId').value = id;
    document.getElementById('question').value = question;
    document.getElementById('answer').value = answer;
    document.getElementById('category').value = category;
    modal.classList.add('open');
}

function closeModal() {
    modal.classList.remove('open');
}

window.addEventListener('click', (event) => {
    if (event.target === modal) closeModal();
});

kbForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const submitBtn = kbForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    try {
        const formData = new FormData(kbForm);
        const response = await fetch(kbForm.action, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            closeModal();
            window.location.href = '<?php echo BASE_PATH; ?>pages/admin/knowledge.php?saved=1';
        } else {
            alert(data.error || 'Failed to save knowledge entry.');
        }
    } catch (error) {
        alert('Failed to save knowledge entry.');
        console.error(error);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});
</script>
</body>
</html>

