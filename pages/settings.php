<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/preferences.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireLogin();

$user_id = getCurrentUserId();
$username = getCurrentUsername();
$user_role = getCurrentUserRole();
$prefs = getUserPreferences($user_id);

$conn = getDatabaseConnection();
$conversations = [];
$stmt = $conn->prepare("SELECT id, title, created_at, updated_at FROM conversations WHERE user_id = ? ORDER BY updated_at DESC");
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

        .sidebar {
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

        .side-top {
            padding: 16px 14px 12px;
            border-bottom: 1px solid rgba(148, 163, 184, .18);
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: calc(16px * var(--font-scale, 1));
            font-weight: 700;
            margin-bottom: 12px;
        }

        .brand-actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: space-between;
        }

        .brand-collapse {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(148, 163, 184, .36);
            border-radius: 999px;
            background: rgba(15, 23, 42, .45);
            color: #e2e8f0;
            cursor: pointer;
            transition: .16s;
        }

        .brand-collapse:hover {
            background: rgba(30, 41, 59, .8);
            border-color: rgba(148, 163, 184, .56);
            transform: translateY(-1px);
        }

        .brand-collapse .material-symbols-rounded {
            font-size: calc(18px * var(--font-scale, 1));
        }

        .brand-github {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(148, 163, 184, .36);
            border-radius: 999px;
            background: rgba(15, 23, 42, .45);
            color: #e2e8f0;
            text-decoration: none;
            transition: .16s;
        }

        .brand-github:hover {
            background: rgba(30, 41, 59, .8);
            border-color: rgba(148, 163, 184, .56);
            transform: translateY(-1px);
        }

        .brand-github svg {
            width: 15px;
            height: 15px;
            fill: currentColor;
        }

        .ghost {
            width: 100%;
            border: 1px solid rgba(148, 163, 184, .32);
            background: rgba(15, 23, 42, .45);
            color: #f8fafc;
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }

        .ghost:hover {
            background: rgba(30, 41, 59, .75);
            border-color: rgba(148, 163, 184, .46);
        }

        .conv-head {
            padding: 2px 8px 10px;
            font-size: calc(11px * var(--font-scale, 1));
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #93a3be;
        }

        .conv-list {
            flex: 1;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, .52) transparent;
            padding: 10px 10px 12px;
        }

        .conv-list::-webkit-scrollbar {
            width: 11px;
        }

        .conv-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .conv-list::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .48);
            border-radius: 999px;
            border: 3px solid transparent;
            background-clip: content-box;
        }

        .conv-list::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, .7);
            background-clip: content-box;
        }

        .conv {
            width: 100%;
            text-align: left;
            border: 1px solid transparent;
            background: rgba(15, 23, 42, .3);
            color: #e5e7eb;
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            margin-bottom: 8px;
            position: relative;
            transition: .16s;
            text-decoration: none;
            display: block;
        }

        .conv::before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: transparent;
            border-radius: 2px;
        }

        .conv:hover {
            background: rgba(30, 41, 59, .72);
            border-color: rgba(148, 163, 184, .34);
        }

        .conv.active {
            background: rgba(30, 41, 59, .95);
            border-color: color-mix(in srgb, var(--accent) 55%, transparent);
        }

        .conv.active::before {
            background: var(--accent);
        }

        .conv-title {
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conv-time {
            font-size: calc(12px * var(--font-scale, 1));
            color: #9ca3af;
            margin-top: 2px;
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
            font-size: calc(14px * var(--font-scale, 1));
        }

        .side-link:hover {
            background: rgba(30, 41, 59, .75);
            color: #f8fafc;
            border-color: rgba(148, 163, 184, .34);
        }

        .side-link.active {
            background: rgba(30, 41, 59, .95);
            color: #f8fafc;
            border-color: color-mix(in srgb, var(--accent) 55%, transparent);
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
            min-width: 0;
            overflow-x: clip;
        }

        body.sidebar-collapsed .sidebar {
            width: 76px;
            min-width: 76px;
        }

        body.sidebar-collapsed .page-main {
            margin-left: 76px;
        }

        body.sidebar-collapsed .side-top {
            padding: 12px 8px;
        }

        body.sidebar-collapsed .brand {
            justify-content: center;
            margin-bottom: 0;
        }

        body.sidebar-collapsed .ghost,
        body.sidebar-collapsed .conv-list {
            display: none;
        }

        body.sidebar-collapsed .brand-actions .brand-github {
            display: none;
        }

        body.sidebar-collapsed .brand-actions {
            width: auto;
        }

        body.sidebar-collapsed .side-foot {
            padding: 10px 8px;
        }

        body.sidebar-collapsed .side-link {
            justify-content: center;
            padding: 10px;
        }

        body.sidebar-collapsed .side-link span:last-child {
            display: none;
        }

        .page-main .ui-container {
            max-width: 1120px;
            padding-top: 24px;
        }

        .settings-layout {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .setting-block {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .setting-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-width: 0;
        }

        .setting-row-left {
            min-width: 0;
            flex: 1;
        }

        .setting-kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 9px;
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--text-muted);
            font-size: calc(12px * var(--font-scale, 1));
            margin-bottom: 8px;
            background: var(--surface-muted);
        }

        .setting-title {
            font-size: calc(15px * var(--font-scale, 1));
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: 4px;
        }

        .setting-desc {
            color: var(--text-muted);
            font-size: calc(13px * var(--font-scale, 1));
            line-height: 1.45;
        }

        .text-size-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            width: 100%;
        }

        .text-size-grid .ui-option-btn {
            width: 100%;
            text-align: center;
            padding: 9px 8px;
            font-weight: 600;
        }

        .accent-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 8px;
            width: 100%;
        }

        .accent-swatch {
            width: 100%;
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            padding: 8px 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: calc(13px * var(--font-scale, 1));
            cursor: pointer;
            color: var(--text);
            transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease;
        }

        .accent-swatch:hover {
            border-color: color-mix(in srgb, var(--accent) 45%, var(--line));
            transform: translateY(-1px);
        }

        .accent-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            flex: 0 0 auto;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, .8), 0 0 0 3px rgba(15, 23, 42, .08);
        }

        .accent-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 600;
        }

        .accent-swatch.active {
            border-color: color-mix(in srgb, var(--accent) 58%, transparent);
            background: var(--accent-soft, #eef2ff);
            color: var(--accent);
            box-shadow: 0 0 0 1px color-mix(in srgb, var(--accent) 22%, transparent);
        }

        .danger-zone {
            border-color: color-mix(in srgb, var(--danger) 26%, var(--line));
            background: linear-gradient(180deg, color-mix(in srgb, var(--danger) 7%, var(--surface)) 0%, var(--surface) 100%);
        }

        .danger-note {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid color-mix(in srgb, var(--danger) 28%, var(--line));
            color: color-mix(in srgb, var(--danger) 78%, var(--text));
            background: color-mix(in srgb, var(--danger) 8%, var(--surface));
            border-radius: 999px;
            padding: 5px 10px;
            font-size: calc(12px * var(--font-scale, 1));
            margin-bottom: 8px;
        }

        .conversation-card {
            margin-top: 14px;
        }

        .conversation-meta {
            color: var(--text-muted);
            font-size: calc(13px * var(--font-scale, 1));
            margin-bottom: 10px;
        }

        .ui-table {
            min-width: 0;
            table-layout: fixed;
        }

        .ui-table th:first-child,
        .ui-table td:first-child {
            width: 48%;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .ui-table th:nth-child(2),
        .ui-table td:nth-child(2) {
            width: 32%;
        }

        .ui-table th:last-child,
        .ui-table td:last-child {
            width: 20%;
            text-align: right;
        }

        .ui-table td .ui-btn {
            white-space: nowrap;
        }

        .table-mobile {
            display: none;
        }

        @media (max-width: 960px) {
            .mobile-top {
                display: flex;
            }

            .brand-collapse {
                display: none;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform .18s;
                box-shadow: 0 20px 40px rgba(2, 6, 23, .45);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .page-main {
                margin-left: 0;
            }

            body.sidebar-collapsed .sidebar {
                width: 288px;
                min-width: 288px;
            }

            body.sidebar-collapsed .page-main {
                margin-left: 0;
            }

            .settings-layout {
                grid-template-columns: 1fr;
            }

            .setting-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .setting-row .ui-switch {
                margin-top: 2px;
            }

            .accent-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .ui-table th:last-child,
            .ui-table td:last-child {
                text-align: left;
            }
        }

        @media (max-width: 700px) {
            .ui-table-wrap {
                display: none;
            }

            .table-mobile {
                display: grid;
                gap: 10px;
            }

            .table-mobile-item {
                border: 1px solid var(--line);
                border-radius: 12px;
                background: #fff;
                padding: 12px;
                display: grid;
                gap: 8px;
            }

            .table-mobile-title {
                font-size: calc(14px * var(--font-scale, 1));
                font-weight: 600;
                overflow-wrap: anywhere;
                word-break: break-word;
            }

            .table-mobile-meta {
                color: var(--text-muted);
                font-size: calc(13px * var(--font-scale, 1));
            }

            .table-mobile-actions {
                display: flex;
                justify-content: flex-start;
            }

            .text-size-grid {
                grid-template-columns: 1fr;
            }

            .accent-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        html.dark-mode .accent-swatch,
        body.dark-mode .accent-swatch,
        html.dark-mode .table-mobile-item,
        body.dark-mode .table-mobile-item {
            background: var(--surface);
            border-color: var(--line);
            color: var(--text);
        }

        html.dark-mode .danger-zone,
        body.dark-mode .danger-zone {
            background: linear-gradient(180deg, color-mix(in srgb, var(--danger) 12%, var(--surface)) 0%, var(--surface) 100%);
        }
    </style>
</head>
<body>
<div class="app-shell">
    <div class="overlay" id="overlay" aria-hidden="true"></div>
    <?php
    renderAppSidebar([
        'base_path' => BASE_PATH,
        'conversations' => $conversations,
        'current_page' => 'settings',
        'user_role' => $user_role,
        'show_recent' => true,
        'show_new_chat' => true
    ]);
    ?>

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

        <section class="settings-layout">
            <article class="ui-card setting-block">
                <div class="setting-kicker"><span class="material-symbols-rounded" style="font-size:16px;">palette</span>Appearance</div>
                <div class="setting-row">
                    <div class="setting-row-left">
                        <div class="setting-title">Dark mode</div>
                        <div class="setting-desc">Reduce eye strain in low-light environments across chat and settings.</div>
                    </div>
                    <label class="ui-switch" aria-label="Toggle dark mode">
                        <input type="checkbox" id="darkMode" <?php echo $prefs['dark_mode'] ? 'checked' : ''; ?>>
                        <span class="ui-switch-slider"></span>
                    </label>
                </div>
            </article>

            <article class="ui-card setting-block">
                <div class="setting-kicker"><span class="material-symbols-rounded" style="font-size:16px;">format_size</span>Text Size</div>
                <div class="setting-row-left">
                    <div class="setting-title">Reading scale</div>
                    <div class="setting-desc">Choose your default content size for all screens.</div>
                </div>
                <div class="text-size-grid">
                    <button class="ui-option-btn <?php echo $prefs['font_size'] === 'small' ? 'active' : ''; ?>" data-group="font-size" data-value="small" onclick="changeFontSize('small', this)">Small</button>
                    <button class="ui-option-btn <?php echo $prefs['font_size'] === 'medium' ? 'active' : ''; ?>" data-group="font-size" data-value="medium" onclick="changeFontSize('medium', this)">Medium</button>
                    <button class="ui-option-btn <?php echo $prefs['font_size'] === 'large' ? 'active' : ''; ?>" data-group="font-size" data-value="large" onclick="changeFontSize('large', this)">Large</button>
                </div>
            </article>

            <article class="ui-card setting-block">
                <div class="setting-kicker"><span class="material-symbols-rounded" style="font-size:16px;">colors</span>Theme Accent</div>
                <div class="setting-row-left">
                    <div class="setting-title">Accent color</div>
                    <div class="setting-desc">This color is used for active states, focus, and highlights in the app.</div>
                </div>
                <div class="accent-grid">
                    <button class="accent-swatch <?php echo $prefs['theme_color'] === 'blue' ? 'active' : ''; ?>" data-group="theme-color" data-value="blue" onclick="changeThemeColor('blue', this)">
                        <span class="accent-dot" style="background:#3b82f6;"></span><span class="accent-label">Blue</span>
                    </button>
                    <button class="accent-swatch <?php echo $prefs['theme_color'] === 'green' ? 'active' : ''; ?>" data-group="theme-color" data-value="green" onclick="changeThemeColor('green', this)">
                        <span class="accent-dot" style="background:#10b981;"></span><span class="accent-label">Green</span>
                    </button>
                    <button class="accent-swatch <?php echo $prefs['theme_color'] === 'purple' ? 'active' : ''; ?>" data-group="theme-color" data-value="purple" onclick="changeThemeColor('purple', this)">
                        <span class="accent-dot" style="background:#8b5cf6;"></span><span class="accent-label">Purple</span>
                    </button>
                    <button class="accent-swatch <?php echo $prefs['theme_color'] === 'orange' ? 'active' : ''; ?>" data-group="theme-color" data-value="orange" onclick="changeThemeColor('orange', this)">
                        <span class="accent-dot" style="background:#f97316;"></span><span class="accent-label">Orange</span>
                    </button>
                    <button class="accent-swatch <?php echo $prefs['theme_color'] === 'cyan' ? 'active' : ''; ?>" data-group="theme-color" data-value="cyan" onclick="changeThemeColor('cyan', this)">
                        <span class="accent-dot" style="background:#06b6d4;"></span><span class="accent-label">Cyan</span>
                    </button>
                </div>
            </article>

            <article class="ui-card danger-zone setting-block">
                <div class="danger-note"><span class="material-symbols-rounded" style="font-size:16px;">warning</span>Destructive action</div>
                <div class="setting-row">
                    <div class="setting-row-left">
                        <div class="setting-title">Delete all history</div>
                        <div class="setting-desc">Permanently removes all conversations and messages from your account.</div>
                    </div>
                    <button class="ui-btn danger" onclick="deleteAllHistory()"><span class="material-symbols-rounded">delete_forever</span>Delete All</button>
                </div>
            </article>
        </section>

        <section class="ui-card conversation-card">
            <div class="ui-card-header"><h3>Your Conversations</h3></div>
            <div class="conversation-meta"><?php echo count($conversations); ?> total conversation<?php echo count($conversations) === 1 ? '' : 's'; ?></div>
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
                <div class="table-mobile">
                    <?php foreach ($conversations as $conv): ?>
                        <article class="table-mobile-item">
                            <div class="table-mobile-title"><?php echo htmlspecialchars($conv['title']); ?></div>
                            <div class="table-mobile-meta">Created: <?php echo date('M j, Y g:i A', strtotime($conv['created_at'])); ?></div>
                            <div class="table-mobile-actions">
                                <button class="ui-btn danger sm" onclick="deleteConversation(<?php echo (int)$conv['id']; ?>, '<?php echo htmlspecialchars($conv['title'], ENT_QUOTES); ?>')">
                                    <span class="material-symbols-rounded">delete</span>Delete
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <div id="saveConfirmation" class="ui-alert success" style="display:none; margin-top:12px;">Settings saved.</div>
    </div>
    </main>
</div>

<script>
const basePath = '<?php echo BASE_PATH; ?>';
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const menuBtn = document.getElementById('menuBtn');
const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
const sidebarCollapseIcon = document.getElementById('sidebarCollapseIcon');
const validFontSizes = ['small', 'medium', 'large'];
const validThemeColors = ['blue', 'green', 'purple', 'orange', 'cyan'];
const SIDEBAR_COLLAPSE_KEY = 'infobot_sidebar_collapsed';

let currentDarkMode = <?php echo $prefs['dark_mode'] ? 'true' : 'false'; ?>;
let currentFontSize = '<?php echo $prefs['font_size']; ?>';
let currentThemeColor = '<?php echo $prefs['theme_color']; ?>';

const lsDarkMode = localStorage.getItem('darkMode');
const lsFontSize = localStorage.getItem('fontSize');
const lsThemeColor = localStorage.getItem('themeColor');

if (lsDarkMode === 'true' || lsDarkMode === 'false') {
    currentDarkMode = lsDarkMode === 'true';
}
if (lsFontSize && validFontSizes.includes(lsFontSize)) {
    currentFontSize = lsFontSize;
}
if (lsThemeColor && validThemeColors.includes(lsThemeColor)) {
    currentThemeColor = lsThemeColor;
}

function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('open');
}

function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
}

function setSidebarCollapsed(collapsed) {
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    if (sidebarCollapseIcon) {
        sidebarCollapseIcon.textContent = collapsed ? 'left_panel_open' : 'left_panel_close';
    }
    if (sidebarCollapseBtn) {
        sidebarCollapseBtn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
    }
    localStorage.setItem(SIDEBAR_COLLAPSE_KEY, collapsed ? 'true' : 'false');
}

function restoreSidebarCollapsed() {
    const saved = localStorage.getItem(SIDEBAR_COLLAPSE_KEY);
    setSidebarCollapsed(saved === 'true');
}

function toggleSidebarCollapsed() {
    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
    setSidebarCollapsed(!isCollapsed);
}

function applyTheme(darkMode, fontSize, themeColor) {
    const root = document.documentElement;
    const colorMap = { blue: '#3b82f6', green: '#10b981', purple: '#8b5cf6', orange: '#f97316', cyan: '#06b6d4' };
    const accent = colorMap[themeColor] || '#4f46e5';
    const sizeMap = { small: '14px', medium: '15px', large: '17px' };
    const scaleMap = { small: '0.933333', medium: '1', large: '1.133333' };

    root.style.setProperty('--font-size-base', sizeMap[fontSize] || '15px');
    root.style.setProperty('--font-scale', scaleMap[fontSize] || '1');
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

function syncSettingsControls() {
    const darkModeInput = document.getElementById('darkMode');
    if (darkModeInput) darkModeInput.checked = !!currentDarkMode;

    document.querySelectorAll('[data-group="font-size"]').forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.value === currentFontSize);
    });
    document.querySelectorAll('[data-group="theme-color"]').forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.value === currentThemeColor);
    });
}

document.getElementById('darkMode').addEventListener('change', function() {
    currentDarkMode = this.checked;
    savePreferences(currentDarkMode, currentFontSize, currentThemeColor);
    applyTheme(currentDarkMode, currentFontSize, currentThemeColor);
});

function changeFontSize(size, btn) {
    currentFontSize = size;
    document.querySelectorAll('[data-group="font-size"]').forEach((x) => x.classList.remove('active'));
    if (btn) btn.classList.add('active');
    savePreferences(currentDarkMode, currentFontSize, currentThemeColor);
    applyTheme(currentDarkMode, currentFontSize, currentThemeColor);
}

function changeThemeColor(color, btn) {
    currentThemeColor = color;
    document.querySelectorAll('[data-group="theme-color"]').forEach((x) => x.classList.remove('active'));
    if (btn) btn.classList.add('active');
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
if (sidebarCollapseBtn) sidebarCollapseBtn.addEventListener('click', toggleSidebarCollapsed);
if (overlay) overlay.addEventListener('click', closeSidebar);
window.addEventListener('resize', () => {
    if (window.innerWidth > 960) closeSidebar();
});

restoreSidebarCollapsed();
applyTheme(currentDarkMode, currentFontSize, currentThemeColor);
syncSettingsControls();
</script>
</body>
</html>

