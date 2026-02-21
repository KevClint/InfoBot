<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$user_id = getCurrentUserId();
$error = '';
$editing = false;
$entry = null;

if (isset($_GET['id'])) {
    $kb_id = intval($_GET['id']);
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT * FROM knowledge_base WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ii", $kb_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $entry = $result->fetch_assoc();
        $editing = true;
    } else {
        header('Location: ' . BASE_PATH . 'pages/manage.php');
        exit();
    }

    $stmt->close();
    closeDatabaseConnection($conn);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = sanitizeInput($_POST['question'] ?? '');
    $answer = sanitizeInput($_POST['answer'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? 'General');

    if (empty($question) || empty($answer)) {
        $error = 'Question and answer are required.';
    } else {
        $conn = getDatabaseConnection();

        if ($editing) {
            $kb_id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE knowledge_base SET question = ?, answer = ?, category = ? WHERE id = ? AND created_by = ?");
            $stmt->bind_param("sssii", $question, $answer, $category, $kb_id, $user_id);
            if ($stmt->execute()) {
                $stmt->close();
                closeDatabaseConnection($conn);
                header('Location: ' . BASE_PATH . 'pages/manage.php?success=knowledge_updated');
                exit();
            }
            $error = 'Failed to update entry.';
        } else {
            $stmt = $conn->prepare("INSERT INTO knowledge_base (question, answer, category, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $question, $answer, $category, $user_id);
            if ($stmt->execute()) {
                $stmt->close();
                closeDatabaseConnection($conn);
                header('Location: ' . BASE_PATH . 'pages/manage.php?success=knowledge_added');
                exit();
            }
            $error = 'Failed to create entry.';
        }

        $stmt->close();
        closeDatabaseConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editing ? 'Edit' : 'Add'; ?> Knowledge Entry - InfoBot</title>
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
            <a class="ui-nav-link" href="<?php echo BASE_PATH; ?>pages/logout.php"><span class="material-symbols-rounded">logout</span>Logout</a>
        </nav>
    </div>
</header>

<main class="ui-container" style="max-width:860px;">
    <section class="ui-page-head">
        <h1 class="ui-title"><?php echo $editing ? 'Edit' : 'Add New'; ?> Knowledge Entry</h1>
        <p class="ui-subtitle"><?php echo $editing ? 'Update the existing question and answer.' : 'Create a new question and answer pair for the bot.'; ?></p>
    </section>

    <section class="ui-card">
        <?php if ($error): ?>
            <div class="ui-alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?php echo (int)$entry['id']; ?>">
            <?php endif; ?>

            <div class="ui-form-group">
                <label class="ui-form-label" for="question">Question</label>
                <input class="ui-input" type="text" id="question" name="question" required value="<?php echo htmlspecialchars($entry['question'] ?? ''); ?>" placeholder="Enter the question">
            </div>

            <div class="ui-form-group">
                <label class="ui-form-label" for="answer">Answer</label>
                <textarea class="ui-textarea" id="answer" name="answer" required placeholder="Enter the answer"><?php echo htmlspecialchars($entry['answer'] ?? ''); ?></textarea>
            </div>

            <div class="ui-form-group">
                <label class="ui-form-label" for="category">Category</label>
                <input class="ui-input" type="text" id="category" name="category" value="<?php echo htmlspecialchars($entry['category'] ?? 'General'); ?>" placeholder="General, Technical, FAQ...">
            </div>

            <div class="ui-actions" style="justify-content:flex-end;">
                <a href="<?php echo BASE_PATH; ?>pages/manage.php" class="ui-btn secondary">Cancel</a>
                <button type="submit" class="ui-btn primary"><span class="material-symbols-rounded"><?php echo $editing ? 'save' : 'add'; ?></span><?php echo $editing ? 'Update' : 'Create'; ?> Entry</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>

