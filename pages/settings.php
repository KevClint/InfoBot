<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/preferences.php';

requireLogin();

$user_id = getCurrentUserId();
$username = getCurrentUsername();
$is_admin = isAdmin();
$prefs = getUserPreferences($user_id);

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
    <link rel="icon" href="<?php echo BASE_PATH; ?>assets/icons/logo-robot-64px.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/premium-ui.css">
    <script src="<?php echo BASE_PATH; ?>assets/js/theme-init.js"></script>
    <style>
        .app-shell {
            min-height: 100vh;
            display: block;
        }

        .side-panel {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 288px;
            min-width: 288px;
            background: linear-gradient(180deg, #020617 0%, #020b2a 100%);
            color: #e5e7eb;
            border-right: 1px solid rgba(148, 163, 184, .22);
            box-shadow: inset -1px 0 0 rgba(255, 255, 255, .03);
            display: flex;
            flex-direction: column;
            z-index: 40;
            overflow-y: auto;
        }

        .side-head {
            padding: 16px 14px 12px;
            border-bottom: 1px solid rgba(148, 163, 184, .18);
        }

        .side-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #f8fafc;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 12px;
        }

        .side-links {
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .side-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: #cbd5e1;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .side-link:hover {
            background: rgba(30, 41, 59, .75);
            color: #f8fafc;
            border-color: rgba(148, 163, 184, .34);
        }

        .side-link.active {
            background: rgba(30, 41, 59, .95);
            color: #f8fafc;
            border-color: rgba(99, 102, 241, .55);
        }

        .side-foot {
            border-top: 1px solid rgba(148, 163, 184, .2);
            padding: 10px;
            background: rgba(2, 6, 23, .45);
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            opacity: 0;
            pointer-events: none;
            transition: opacity .15s;
            z-index: 35;
        }

        .overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        .mobile-top {
            display: none;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--line);
            padding: 10px 14px;
            background: rgba(248, 250, 252, 0.9);
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(8px);
        }

        .menu-btn {
            width: 36px;
            height: 36px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .page-main {
            margin-left: 288px;
            min-height: 100vh;
        }

        .page-main .ui-container {
            max-width: 1120px;
            padding-top: 24px;
        }

        @media (max-width: 960px) {
            .mobile-top {
                display: flex;
            }

            .side-panel {
                transform: translateX(-100%);
                transition: transform .18s;
                box-shadow: 0 20px 40px rgba(2, 6, 23, .45);
            }

            .side-panel.open {
                transform: translateX(0);
            }

            .page-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <div class="overlay" id="overlay" aria-hidden="true"></div>
    <aside class="side-panel">
        <div class="side-head">
            <a href="<?php echo BASE_PATH; ?>pages/chat.php" class="side-brand">
                <span class="material-symbols-rounded">smart_toy</span>
                <span>InfoBot</span>
            </a>
        </div>
        <nav class="side-links">
            <a class="side-link" href="<?php echo BASE_PATH; ?>pages/chat.php"><span class="material-symbols-rounded">chat</span><span>Chat</span></a>
            <?php if ($is_admin): ?>
                <a class="side-link" href="<?php echo BASE_PATH; ?>pages/admin/index.php"><span class="material-symbols-rounded">admin_panel_settings</span><span>Admin</span></a>
            <?php endif; ?>
        </nav>
        <div class="side-foot">
            <a class="side-link" href="<?php echo BASE_PATH; ?>pages/manage.php"><span class="material-symbols-rounded">folder</span><span>Manage</span></a>
            <a class="side-link active" href="<?php echo BASE_PATH; ?>pages/settings.php"><span class="material-symbols-rounded">settings</span><span>Settings</span></a>
            <a class="side-link" href="<?php echo BASE_PATH; ?>pages/logout.php"><span class="material-symbols-rounded">logout</span><span>Logout</span></a>
        </div>
    </aside>

    <main class="page-main">
    <div class="mobile-top">
        <button class="menu-btn" id="menuBtn" type="button" aria-label="Open navigation"><span class="material-symbols-rounded">menu</span></button>
        <strong>Settings</strong>
    </div>
    <div class="ui-container">
        <section class="ui-page-head">
            <h1 class="ui-title">Settings</h1>
            <p class="ui-subtitle">Customize appearance and manage conversation data.</p>
        </section>

        <section class="ui-grid cols-2">
            <article class="ui-card">
                <div class="ui-card-header"><h3>Appearance</h3></div>
                <div class="ui-actions" style="justify-content:space-between;">
                    <div>
                        <div style="font-weight:600; font-size:14px;">Dark Mode</div>
                        <div class="ui-muted" style="font-size:13px;">Toggle low-light reading mode.</div>
                    </div>
                    <label class="ui-switch">
                        <input type="checkbox" id="darkMode" <?php echo $prefs['dark_mode'] ? 'checked' : ''; ?>>
                        <span class="ui-switch-slider"></span>
                    </label>
                </div>
            </article>

            <article class="ui-card">
                <div class="ui-card-header"><h3>Text Size</h3></div>
                <div class="ui-option-row">
                    <button class="ui-option-btn <?php echo $prefs['font_size'] === 'small' ? 'active' : ''; ?>" onclick="changeFontSize('small', this)">Small</button>
                    <button class="ui-option-btn <?php echo $prefs['font_size'] === 'medium' ? 'active' : ''; ?>" onclick="changeFontSize('medium', this)">Medium</button>
                    <button class="ui-option-btn <?php echo $prefs['font_size'] === 'large' ? 'active' : ''; ?>" onclick="changeFontSize('large', this)">Large</button>
                </div>
            </article>

            <article class="ui-card">
                <div class="ui-card-header"><h3>Theme Accent</h3></div>
                <div class="ui-option-row">
                    <button class="ui-option-btn <?php echo $prefs['theme_color'] === 'blue' ? 'active' : ''; ?>" onclick="changeThemeColor('blue', this)">Blue</button>
                    <button class="ui-option-btn <?php echo $prefs['theme_color'] === 'green' ? 'active' : ''; ?>" onclick="changeThemeColor('green', this)">Green</button>
                    <button class="ui-option-btn <?php echo $prefs['theme_color'] === 'purple' ? 'active' : ''; ?>" onclick="changeThemeColor('purple', this)">Purple</button>
                    <button class="ui-option-btn <?php echo $prefs['theme_color'] === 'orange' ? 'active' : ''; ?>" onclick="changeThemeColor('orange', this)">Orange</button>
                    <button class="ui-option-btn <?php echo $prefs['theme_color'] === 'cyan' ? 'active' : ''; ?>" onclick="changeThemeColor('cyan', this)">Cyan</button>
                </div>
            </article>

            <article class="ui-card">
                <div class="ui-card-header">
                    <h3>Delete All History</h3>
                    <button class="ui-btn danger" onclick="deleteAllHistory()"><span class="material-symbols-rounded">delete_forever</span>Delete All</button>
                </div>
                <p class="ui-muted" style="font-size:13px;">This permanently removes all your conversations and messages.</p>
            </article>
        </section>

        <section class="ui-card" style="margin-top:14px;">
            <div class="ui-card-header"><h3>Your Conversations</h3></div>
            <?php if (empty($conversations)): ?>
                <div class="ui-empty">No conversations yet.</div>
            <?php else: ?>
                <div class="ui-table-wrap">
                    <table class="ui-table">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($conversations as $conv): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($conv['title']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($conv['created_at'])); ?></td>
                                <td>
                                    <button class="ui-btn danger sm" onclick="deleteConversation(<?php echo (int)$conv['id']; ?>, '<?php echo htmlspecialchars($conv['title'], ENT_QUOTES); ?>')">
                                        <span class="material-symbols-rounded">delete</span>Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <div id="saveConfirmation" class="ui-alert success" style="display:none; margin-top:12px;">Settings saved.</div>
    </div>
    </main>
</div>

<script>
const basePath = '<?php echo BASE_PATH; ?>';
const sidebar = document.querySelector('.side-panel');
const overlay = document.getElementById('overlay');
const menuBtn = document.getElementById('menuBtn');
let currentDarkMode = <?php echo $prefs['dark_mode'] ? 'true' : 'false'; ?>;
let currentFontSize = '<?php echo $prefs['font_size']; ?>';
let currentThemeColor = '<?php echo $prefs['theme_color']; ?>';

function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('open');
}

function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
}

function applyTheme(darkMode, fontSize, themeColor) {
    const root = document.documentElement;
    const colorMap = { blue: '#3b82f6', green: '#10b981', purple: '#8b5cf6', orange: '#f97316', cyan: '#06b6d4' };
    const accent = colorMap[themeColor] || '#4f46e5';
    const sizeMap = { small: '14px', medium: '15px', large: '17px' };

    root.style.setProperty('--font-size-base', sizeMap[fontSize] || '15px');
    root.style.setProperty('--accent', accent);
    root.style.setProperty('--accent-hover', accent);
    root.style.setProperty('--accent-soft', hexToRgba(accent, 0.14));

    document.documentElement.classList.toggle('dark-mode', !!darkMode);
    document.body.classList.toggle('dark-mode', !!darkMode);
    localStorage.setItem('darkMode', darkMode ? 'true' : 'false');
    localStorage.setItem('fontSize', fontSize);
    localStorage.setItem('themeColor', themeColor);
}

function savePreferences(darkMode, fontSize, themeColor) {
    fetch(basePath + 'api/save_preferences.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ dark_mode: darkMode, font_size: fontSize, theme_color: themeColor })
    })
    .then(r => r.json())
    .then(data => { if (data.success) showSaveConfirmation(); });
}

function showSaveConfirmation() {
    const el = document.getElementById('saveConfirmation');
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 2200);
}

document.getElementById('darkMode').addEventListener('change', function() {
    currentDarkMode = this.checked;
    savePreferences(currentDarkMode, currentFontSize, currentThemeColor);
    applyTheme(currentDarkMode, currentFontSize, currentThemeColor);
});

function changeFontSize(size, btn) {
    currentFontSize = size;
    document.querySelectorAll('.ui-option-btn').forEach(x => {
        if (x.textContent.toLowerCase() === 'small' || x.textContent.toLowerCase() === 'medium' || x.textContent.toLowerCase() === 'large') x.classList.remove('active');
    });
    btn.classList.add('active');
    savePreferences(currentDarkMode, currentFontSize, currentThemeColor);
    applyTheme(currentDarkMode, currentFontSize, currentThemeColor);
}

function changeThemeColor(color, btn) {
    currentThemeColor = color;
    document.querySelectorAll('.ui-option-btn').forEach(x => {
        const t = x.textContent.toLowerCase();
        if (['blue','green','purple','orange','cyan'].includes(t)) x.classList.remove('active');
    });
    btn.classList.add('active');
    savePreferences(currentDarkMode, currentFontSize, currentThemeColor);
    applyTheme(currentDarkMode, currentFontSize, currentThemeColor);
}

function hexToRgba(hex, alpha) {
    const clean = (hex || '').replace('#', '');
    if (clean.length !== 6) return 'rgba(79, 70, 229, ' + alpha + ')';
    const r = parseInt(clean.substring(0, 2), 16);
    const g = parseInt(clean.substring(2, 4), 16);
    const b = parseInt(clean.substring(4, 6), 16);
    return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
}

function deleteAllHistory() {
    if (!confirm('Delete ALL conversations and messages permanently?')) return;
    if (!confirm('Final confirmation: continue deleting all history?')) return;

    fetch(basePath + 'api/delete_all_conversations.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('All conversations deleted.');
            window.location.href = basePath + 'pages/chat.php';
        } else {
            alert('Error: ' + (data.error || 'Failed to delete history'));
        }
    })
    .catch(() => alert('An error occurred while deleting history.'));
}

function deleteConversation(convId, convTitle) {
    if (!confirm('Delete conversation "' + convTitle + '"?')) return;

    fetch(basePath + 'api/delete_conversation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ conversation_id: convId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Error: ' + (data.error || 'Failed to delete conversation'));
    })
    .catch(() => alert('An error occurred while deleting the conversation.'));
}

if (menuBtn) menuBtn.addEventListener('click', openSidebar);
if (overlay) overlay.addEventListener('click', closeSidebar);
window.addEventListener('resize', () => {
    if (window.innerWidth > 960) closeSidebar();
});

applyTheme(currentDarkMode, currentFontSize, currentThemeColor);
</script>
</body>
</html>

