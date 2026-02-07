<?php
/**
 * KNOWLEDGE BASE FORM PAGE
 * 
 * This page allows users to add new or edit existing
 * knowledge base entries (CRUD - Create & Update).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Require user to be logged in
requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';

// Check if editing existing entry
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
        header('Location: /infotbot/pages/manage.php');
        exit();
    }
    
    $stmt->close();
    closeDatabaseConnection($conn);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = sanitizeInput($_POST['question'] ?? '');
    $answer = sanitizeInput($_POST['answer'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? 'General');
    
    // Validate inputs
    if (empty($question) || empty($answer)) {
        $error = 'Question and answer are required.';
    } else {
        $conn = getDatabaseConnection();
        
        if ($editing) {
            // Update existing entry
            $kb_id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE knowledge_base SET question = ?, answer = ?, category = ? WHERE id = ? AND created_by = ?");
            $stmt->bind_param("sssii", $question, $answer, $category, $kb_id, $user_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                closeDatabaseConnection($conn);
                header('Location: /infotbot/pages/manage.php?success=knowledge_updated');
                exit();
            } else {
                $error = 'Failed to update entry.';
            }
        } else {
            // Create new entry
            $stmt = $conn->prepare("INSERT INTO knowledge_base (question, answer, category, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $question, $answer, $category, $user_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                closeDatabaseConnection($conn);
                header('Location: /infotbot/pages/manage.php?success=knowledge_added');
                exit();
            } else {
                $error = 'Failed to create entry.';
            }
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
    <title><?php echo $editing ? 'Edit' : 'Add'; ?> Knowledge Entry - AI Chatbot</title>
    <link rel="stylesheet" href="/infotbot/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/infotbot/pages/chat.php" class="logo">
                    <span class="material-symbols-outlined">smart_toy</span>
                    AI Chatbot
                </a>
                <nav class="nav">
                    <a href="/infotbot/pages/chat.php" class="nav-link">
                        <span class="material-symbols-outlined">chat</span>
                        <span>Chat</span>
                    </a>
                    <a href="/infotbot/pages/manage.php" class="nav-link active">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Manage</span>
                    </a>
                    <a href="/infotbot/pages/logout.php" class="nav-link">
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
                <a href="/infotbot/pages/manage.php" style="color: inherit; text-decoration: none;">
                    <span class="material-symbols-outlined" style="vertical-align: middle;">arrow_back</span>
                </a>
                <?php echo $editing ? 'Edit' : 'Add New'; ?> Knowledge Entry
            </h1>
            <p class="page-subtitle">
                <?php echo $editing ? 'Update the question and answer below' : 'Add a new Q&A pair to the knowledge base'; ?>
            </p>
        </div>

        <div class="card">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="material-symbols-outlined">error</span>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php if ($editing): ?>
                    <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="question">Question</label>
                    <input 
                        type="text" 
                        id="question" 
                        name="question" 
                        class="form-input" 
                        placeholder="Enter the question..."
                        value="<?php echo htmlspecialchars($entry['question'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="answer">Answer</label>
                    <textarea 
                        id="answer" 
                        name="answer" 
                        class="form-input" 
                        rows="6"
                        placeholder="Enter the answer..."
                        required
                    ><?php echo htmlspecialchars($entry['answer'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="category">Category</label>
                    <input 
                        type="text" 
                        id="category" 
                        name="category" 
                        class="form-input" 
                        placeholder="e.g., General, Technical, FAQ"
                        value="<?php echo htmlspecialchars($entry['category'] ?? 'General'); ?>"
                    >
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="/infotbot/pages/manage.php" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-symbols-outlined">
                            <?php echo $editing ? 'save' : 'add'; ?>
                        </span>
                        <?php echo $editing ? 'Update' : 'Create'; ?> Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
