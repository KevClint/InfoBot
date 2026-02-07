<?php
/**
 * USER SETTINGS PAGE
 * 
 * Allows users to customize their experience with dark mode,
 * font size, and theme color preferences.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/preferences.php';

requireLogin();

$user_id = getCurrentUserId();
$username = getCurrentUsername();
$prefs = getUserPreferences($user_id);

// Get user's conversations
$conn = getDatabaseConnection();
$conversations = [];
$stmt = $conn->prepare("SELECT id, title, created_at FROM conversations WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
}
$stmt->close();
closeDatabaseConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - InfoBot</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo BASE_PATH; ?>pages/chat.php" class="logo">
                    <span class="material-symbols-outlined">smart_toy</span>
                    InfoBot
                </a>
                <nav class="nav">
                    <a href="<?php echo BASE_PATH; ?>pages/chat.php" class="nav-link">
                        <span class="material-symbols-outlined">chat</span>
                        <span>Chat</span>
                    </a>
                    <a href="<?php echo BASE_PATH; ?>pages/settings.php" class="nav-link active">
                        <span class="material-symbols-outlined">settings</span>
                        <span>Settings</span>
                    </a>
                    <a href="<?php echo BASE_PATH; ?>pages/logout.php" class="nav-link">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Settings Container -->
    <div class="settings-container">
        <div class="settings-header">
            <h1>Settings</h1>
            <p>Customize your experience</p>
        </div>

        <div class="settings-grid">
            <!-- Dark Mode -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3>Appearance</h3>
                </div>
                <div class="settings-card-body">
                    <div class="settings-item">
                        <div class="settings-label">
                            <label for="darkMode">Dark Mode</label>
                            <p class="settings-description">Easier on the eyes in low light</p>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" id="darkMode" <?php echo $prefs['dark_mode'] ? 'checked' : ''; ?>>
                            <label for="darkMode" class="toggle-label"></label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Font Size -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3>Text Size</h3>
                </div>
                <div class="settings-card-body">
                    <div class="settings-item">
                        <label>Choose your preferred text size</label>
                        <div class="font-size-options">
                            <button class="font-size-btn <?php echo $prefs['font_size'] === 'small' ? 'active' : ''; ?>" 
                                    onclick="changeFontSize('small')" value="small">
                                A<small>Small</small>
                            </button>
                            <button class="font-size-btn <?php echo $prefs['font_size'] === 'medium' ? 'active' : ''; ?>" 
                                    onclick="changeFontSize('medium')" value="medium">
                                A<span>Medium</span>
                            </button>
                            <button class="font-size-btn <?php echo $prefs['font_size'] === 'large' ? 'active' : ''; ?>" 
                                    onclick="changeFontSize('large')" value="large">
                                A<big>Large</big>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme Color -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3>Theme Color</h3>
                </div>
                <div class="settings-card-body">
                    <div class="settings-item">
                        <label>Choose your preferred theme color</label>
                        <div class="theme-color-options">
                            <button class="color-btn blue <?php echo $prefs['theme_color'] === 'blue' ? 'active' : ''; ?>" 
                                    onclick="changeThemeColor('blue')" title="Blue">
                                <span class="color-circle" style="background-color: #3b82f6;"></span>
                            </button>
                            <button class="color-btn green <?php echo $prefs['theme_color'] === 'green' ? 'active' : ''; ?>" 
                                    onclick="changeThemeColor('green')" title="Green">
                                <span class="color-circle" style="background-color: #10b981;"></span>
                            </button>
                            <button class="color-btn purple <?php echo $prefs['theme_color'] === 'purple' ? 'active' : ''; ?>" 
                                    onclick="changeThemeColor('purple')" title="Purple">
                                <span class="color-circle" style="background-color: #8b5cf6;"></span>
                            </button>
                            <button class="color-btn orange <?php echo $prefs['theme_color'] === 'orange' ? 'active' : ''; ?>" 
                                    onclick="changeThemeColor('orange')" title="Orange">
                                <span class="color-circle" style="background-color: #f97316;"></span>
                            </button>
                            <button class="color-btn cyan <?php echo $prefs['theme_color'] === 'cyan' ? 'active' : ''; ?>" 
                                    onclick="changeThemeColor('cyan')" title="Cyan">
                                <span class="color-circle" style="background-color: #06b6d4;"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data & Privacy -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3>Manage Conversations</h3>
                </div>
                <div class="settings-card-body">
                    <?php if (empty($conversations)): ?>
                        <p class="settings-description">You have no conversations yet. Start a new chat!</p>
                    <?php else: ?>
                        <div class="conversations-list">
                            <?php foreach ($conversations as $conv): ?>
                                <div class="conversation-item-settings">
                                    <div class="conversation-info">
                                        <h4><?php echo htmlspecialchars($conv['title']); ?></h4>
                                        <p class="text-muted"><?php echo date('M j, Y g:i A', strtotime($conv['created_at'])); ?></p>
                                    </div>
                                    <button class="btn btn-danger btn-sm" onclick="deleteConversation(<?php echo $conv['id']; ?>, '<?php echo htmlspecialchars($conv['title']); ?>')">
                                        <span class="material-symbols-outlined">delete</span>
                                        Delete
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Data & Privacy -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h3>Delete All</h3>
                </div>
                <div class="settings-card-body">
                    <div class="settings-item">
                        <div class="settings-label">
                            <label>Delete All Conversations</label>
                            <p class="settings-description">Permanently delete all conversations and messages</p>
                        </div>
                        <button class="btn btn-danger" onclick="deleteAllHistory()">
                            <span class="material-symbols-outlined">delete_forever</span>
                            Delete All History
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Confirmation -->
        <div id="saveConfirmation" class="save-confirmation" style="display: none;">
            <p>✓ Settings saved successfully</p>
        </div>
    </div>

    <script>
        const basePath = '<?php echo BASE_PATH; ?>';
        const currentDarkMode = <?php echo $prefs['dark_mode'] ? 'true' : 'false'; ?>;
        const currentFontSize = '<?php echo $prefs['font_size']; ?>';
        const currentThemeColor = '<?php echo $prefs['theme_color']; ?>';

        // Initialize theme on page load
        function initializeTheme() {
            applyTheme(currentDarkMode, currentFontSize, currentThemeColor);
        }

        // Apply theme
        function applyTheme(darkMode, fontSize, themeColor) {
            const root = document.documentElement;
            
            // Dark mode
            if (darkMode) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'true');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'false');
            }

            // Font size
            root.style.setProperty('--font-size-multiplier', 
                fontSize === 'small' ? '0.9' : fontSize === 'large' ? '1.1' : '1');
            localStorage.setItem('fontSize', fontSize);

            // Theme color
            const colorMap = {
                'blue': '#3b82f6',
                'green': '#10b981',
                'purple': '#8b5cf6',
                'orange': '#f97316',
                'cyan': '#06b6d4'
            };
            root.style.setProperty('--color-primary', colorMap[themeColor]);
            localStorage.setItem('themeColor', themeColor);
        }

        // Toggle dark mode
        document.getElementById('darkMode').addEventListener('change', function() {
            savePreferences(this.checked, currentFontSize, currentThemeColor);
            applyTheme(this.checked, currentFontSize, currentThemeColor);
        });

        // Change font size
        function changeFontSize(size) {
            savePreferences(currentDarkMode, size, currentThemeColor);
            applyTheme(currentDarkMode, size, currentThemeColor);
            document.querySelectorAll('.font-size-btn').forEach(btn => btn.classList.remove('active'));
            event.target.closest('.font-size-btn').classList.add('active');
        }

        // Change theme color
        function changeThemeColor(color) {
            savePreferences(currentDarkMode, currentFontSize, color);
            applyTheme(currentDarkMode, currentFontSize, color);
            document.querySelectorAll('.color-btn').forEach(btn => btn.classList.remove('active'));
            event.target.closest('.color-btn').classList.add('active');
        }

        // Save preferences to server
        function savePreferences(darkMode, fontSize, themeColor) {
            fetch(basePath + 'api/save_preferences.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    dark_mode: darkMode,
                    font_size: fontSize,
                    theme_color: themeColor
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSaveConfirmation();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Show save confirmation
        function showSaveConfirmation() {
            const confirmation = document.getElementById('saveConfirmation');
            confirmation.style.display = 'block';
            setTimeout(() => {
                confirmation.style.display = 'none';
            }, 3000);
        }

        // Delete all message history
        function deleteAllHistory() {
            if (!confirm('Are you sure you want to delete ALL conversations and messages? This action cannot be undone.')) {
                return;
            }

            if (!confirm('This is your final warning. All data will be permanently deleted. Continue?')) {
                return;
            }

            fetch(basePath + 'api/delete_all_conversations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('All conversations and messages have been deleted.');
                    window.location.href = basePath + 'pages/chat.php';
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete history'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting history.');
            });
        }

        // Delete single conversation
        function deleteConversation(convId, convTitle) {
            if (!confirm(`Are you sure you want to delete the conversation "${convTitle}"? This action cannot be undone.`)) {
                return;
            }

            fetch(basePath + 'api/delete_conversation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: convId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Conversation deleted successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete conversation'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the conversation.');
            });
        }

        // Initialize on page load
        initializeTheme();
    </script>

    <style>
        .conversations-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 400px;
            overflow-y: auto;
        }

        .conversation-item-settings {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--bg-secondary);
            border-radius: 8px;
            border: 1px solid var(--border-color, #e5e7eb);
        }

        .conversation-item-settings:hover {
            background: var(--bg-tertiary);
        }

        .conversation-info {
            flex: 1;
        }

        .conversation-info h4 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            word-break: break-word;
        }

        .conversation-info p {
            margin: 0;
            font-size: 12px;
            color: var(--text-secondary, #666);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
            min-width: auto;
            white-space: nowrap;
            margin-left: 12px;
        }

        .btn-sm .material-symbols-outlined {
            font-size: 18px;
        }

        .text-muted {
            color: var(--text-secondary, #999);
        }
    </style>
</body>
</html>
