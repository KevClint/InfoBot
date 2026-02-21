<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/chatbot.php';

requireLogin();

$user_id = getCurrentUserId();
$username = getCurrentUsername();
$user_role = getCurrentUserRole();

$conversations = getUserConversations($user_id);
$current_conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : null;
$create_new = isset($_GET['new']) && $_GET['new'] === 'true';

if (!$current_conversation_id) {
    if ($create_new || empty($conversations)) {
        $current_conversation_id = createConversation($user_id);
        header('Location: ' . BASE_PATH . 'pages/chat.php?conversation_id=' . $current_conversation_id);
        exit();
    }

    $current_conversation_id = $conversations[0]['id'];
    header('Location: ' . BASE_PATH . 'pages/chat.php?conversation_id=' . $current_conversation_id);
    exit();
}

$messages = getConversationMessages($current_conversation_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoBot</title>
    <link rel="icon" href="<?php echo BASE_PATH; ?>assets/icons/logo-robot-64px.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="<?php echo BASE_PATH; ?>assets/js/theme-init.js"></script>
    <style>
        :root {
            --bg: #f8fafc;
            --panel: #fff;
            --sidebar: #111827;
            --sidebar2: #1f2937;
            --user: #f3f4f6;
            --text: #0f172a;
            --sub: #475569;
            --muted: #94a3b8;
            --line: #e5e7eb;
            --accent: #4f46e5;
            --accent-h: #4338ca;
            --shadow: 0 8px 30px rgba(15, 23, 42, .06);
            --sidew: 288px;
            --stage: 860px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        html,
        body {
            width: 100%;
            height: 100%
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.55;
            font-size: var(--font-size-base, 15px)
        }

        .material-symbols-rounded {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            user-select: none
        }

        .app {
            display: block;
            min-height: 100vh
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            height: 100vh;
            width: var(--sidew);
            min-width: var(--sidew);
            background: linear-gradient(180deg, #020617 0%, #020b2a 100%);
            color: #e5e7eb;
            display: flex;
            flex-direction: column;
            z-index: 40;
            border-right: 1px solid rgba(148, 163, 184, .22);
            box-shadow: inset -1px 0 0 rgba(255, 255, 255, .03);
            overflow-y: auto
        }

        .side-top {
            padding: 16px 14px 12px;
            border-bottom: 1px solid rgba(148, 163, 184, .18)
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px
        }

        .brand-left {
            display: flex;
            align-items: center;
            gap: 10px
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
            transition: .16s
        }

        .brand-github:hover {
            background: rgba(30, 41, 59, .8);
            border-color: rgba(148, 163, 184, .56);
            transform: translateY(-1px)
        }

        .brand-github svg {
            width: 15px;
            height: 15px;
            fill: currentColor
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
            font-weight: 600
        }

        .ghost:hover {
            background: rgba(30, 41, 59, .75);
            border-color: rgba(148, 163, 184, .46)
        }

        .conv-head {
            padding: 2px 8px 10px;
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #93a3be
        }

        .conv-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px 10px 12px
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
            transition: .16s
        }

        .conv::before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: transparent;
            border-radius: 2px
        }

        .conv:hover {
            background: rgba(30, 41, 59, .72);
            border-color: rgba(148, 163, 184, .34)
        }

        .conv.active {
            background: rgba(30, 41, 59, .95);
            border-color: rgba(99, 102, 241, .55)
        }

        .conv.active::before {
            background: var(--accent)
        }

        .conv-title {
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .conv-time {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px
        }

        .side-foot {
            border-top: 1px solid rgba(148, 163, 184, .2);
            padding: 10px;
            background: rgba(2, 6, 23, .45)
        }

        .side-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px
        }

        .side-link:hover {
            background: rgba(30, 41, 59, .75);
            color: #f8fafc
        }

        .main {
            margin-left: var(--sidew);
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh
        }

        .top {
            height: 62px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background: rgba(248, 250, 252, .85);
            backdrop-filter: blur(6px);
            position: sticky;
            top: 0;
            z-index: 20
        }

        .top-left {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .menu {
            width: 36px;
            height: 36px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer
        }

        .title {
            font-size: 15px;
            font-weight: 600
        }

        .chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            padding: 5px 10px;
            font-size: 12px;
            color: var(--sub)
        }

        .scroll {
            flex: 1;
            overflow-y: auto;
            padding: 24px 20px 8px
        }

        .stage {
            max-width: var(--stage);
            margin: 0 auto
        }

        .empty {
            text-align: center;
            padding: 52px 0 20px
        }

        .empty h1 {
            font-size: 34px;
            font-weight: 600;
            letter-spacing: -.02em;
            margin-bottom: 10px
        }

        .empty p {
            color: var(--sub);
            margin-bottom: 26px
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            padding: 14px;
            cursor: pointer;
            text-align: left;
            transition: .15s
        }

        .card:hover {
            transform: translateY(-1px);
            border-color: #cbd5e1
        }

        .ctitle {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px
        }

        .csub {
            font-size: 12px;
            color: #64748b
        }

        .msgs {
            display: flex;
            flex-direction: column;
            gap: 18px;
            padding-bottom: 16px
        }

        .msg {
            display: flex;
            gap: 12px;
            align-items: flex-start
        }

        .msg.user {
            justify-content: flex-end
        }

        .avatar {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            flex: 0 0 auto
        }

        .wrap {
            max-width: min(84%, 720px)
        }

        .bubble {
            border-radius: 16px;
            padding: 11px 14px;
            font-size: 14px;
            white-space: pre-wrap;
            word-wrap: break-word;
            overflow-wrap: anywhere
        }

        .msg.user .bubble {
            background: var(--user);
            border: 1px solid #e5e7eb
        }

        .msg.assistant .bubble {
            background: transparent;
            border: 0;
            padding-left: 0;
            padding-right: 0
        }

        .meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 5px;
            font-size: 12px;
            color: #94a3b8
        }

        .icon {
            border: 0;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            padding: 0
        }

        .icon:hover {
            color: #64748b
        }

        .typing {
            display: inline-flex;
            gap: 5px;
            padding: 8px 0
        }

        .typing span {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #cbd5e1;
            animation: pulse 1.1s infinite ease-in-out
        }

        .typing span:nth-child(2) {
            animation-delay: .15s
        }

        .typing span:nth-child(3) {
            animation-delay: .3s
        }

        @keyframes pulse {

            0%,
            80%,
            100% {
                transform: translateY(0);
                opacity: .4
            }

            40% {
                transform: translateY(-2px);
                opacity: 1
            }
        }

        .composer-shell {
            position: sticky;
            bottom: 0;
            padding: 16px 20px 18px;
            background: linear-gradient(to top, rgba(248, 250, 252, 1) 64%, rgba(248, 250, 252, .7) 86%, rgba(248, 250, 252, 0))
        }

        .composer-stage {
            max-width: var(--stage);
            margin: 0 auto
        }

        .composer {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #fff;
            box-shadow: var(--shadow);
            padding: 10px;
            display: flex;
            gap: 10px;
            align-items: flex-end
        }

        .composer textarea {
            flex: 1;
            border: 0;
            background: transparent;
            resize: none;
            outline: none;
            min-height: 24px;
            max-height: 180px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            color: var(--text);
            line-height: 1.5;
            padding: 6px 4px
        }

        .composer textarea::placeholder {
            color: #94a3b8
        }

        .send {
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex: 0 0 auto
        }

        .send:hover {
            background: var(--accent-h)
        }

        .send:disabled {
            opacity: .45;
            cursor: not-allowed
        }

        .hint {
            margin-top: 8px;
            font-size: 12px;
            color: #94a3b8;
            text-align: center
        }

        pre,
        code {
            font-family: 'JetBrains Mono', monospace
        }

        .bubble pre {
            margin: 10px 0;
            padding: 10px 12px;
            border: 1px solid var(--line);
            background: rgba(2, 6, 23, .06);
            border-radius: 8px;
            overflow: auto
        }

        .bubble code {
            background: rgba(148, 163, 184, .2);
            padding: 1px 5px;
            border-radius: 6px
        }

        .bubble ul,
        .bubble ol {
            margin: 8px 0 8px 20px
        }

        .bubble li {
            margin: 3px 0
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            opacity: 0;
            pointer-events: none;
            transition: opacity .15s;
            z-index: 30
        }

        .overlay.open {
            opacity: 1;
            pointer-events: auto
        }

        @media (max-width:960px) {
            .menu {
                display: flex
            }

            .main {
                margin-left: 0
            }

            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
                transition: transform .18s;
                box-shadow: 0 20px 40px rgba(2, 6, 23, .45)
            }

            .sidebar.open {
                transform: translateX(0)
            }

            .cards {
                grid-template-columns: 1fr
            }
        }

        html.dark-mode {
            --bg: #0b1220;
            --panel: #0f172a;
            --sidebar: #020617;
            --sidebar2: #111827;
            --user: #1e293b;
            --text: #e2e8f0;
            --sub: #94a3b8;
            --muted: #64748b;
            --line: #334155;
            --shadow: 0 8px 30px rgba(2, 6, 23, .45)
        }

        html.dark-mode .top {
            background: rgba(11, 18, 32, .86);
            border-bottom-color: var(--line)
        }

        html.dark-mode .menu,
        html.dark-mode .chip,
        html.dark-mode .avatar,
        html.dark-mode .starter-card {
            background: var(--panel);
            color: var(--text);
            border-color: var(--line)
        }

        html.dark-mode .starter-title {
            color: var(--text)
        }

        html.dark-mode .starter-sub {
            color: var(--sub)
        }

        html.dark-mode .composer {
            background: var(--panel);
            border-color: var(--line)
        }

        html.dark-mode .composer-shell {
            background: linear-gradient(to top, rgba(11, 18, 32, 1) 64%, rgba(11, 18, 32, .78) 86%, rgba(11, 18, 32, 0))
        }

        html.dark-mode .composer textarea {
            color: var(--text)
        }

        html.dark-mode .composer textarea::placeholder {
            color: var(--sub)
        }

        html.dark-mode .conv:hover {
            background: rgba(30, 41, 59, .85)
        }

        html.dark-mode .bubble pre {
            background: rgba(2, 6, 23, .45)
        }

        html.dark-mode .bubble code {
            background: rgba(51, 65, 85, .55)
        }
    </style>
</head>

<body>
    <div class="overlay" id="overlay" aria-hidden="true"></div>
    <div class="app">
        <aside class="sidebar" id="sidebar">
            <div class="side-top">
                <div class="brand">
                    <div class="brand-left"><span class="material-symbols-rounded">smart_toy</span><span>InfoBot</span></div>
                    <a class="brand-github" href="https://github.com/KevClint" target="_blank" rel="noopener noreferrer" aria-label="Open GitHub profile">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 .5a12 12 0 0 0-3.79 23.39c.6.11.82-.26.82-.58l-.02-2.03c-3.34.73-4.04-1.41-4.04-1.41-.55-1.36-1.33-1.72-1.33-1.72-1.09-.73.09-.72.09-.72 1.2.08 1.83 1.22 1.83 1.22 1.08 1.82 2.83 1.29 3.52.98.11-.77.42-1.29.76-1.59-2.67-.3-5.47-1.31-5.47-5.86 0-1.3.47-2.36 1.23-3.19-.12-.3-.53-1.52.12-3.17 0 0 1-.32 3.3 1.22a11.55 11.55 0 0 1 6 0c2.3-1.54 3.3-1.22 3.3-1.22.65 1.65.24 2.87.12 3.17.77.83 1.23 1.89 1.23 3.19 0 4.56-2.8 5.55-5.48 5.85.43.37.81 1.09.81 2.21l-.01 3.28c0 .32.22.7.82.58A12 12 0 0 0 12 .5Z" />
                        </svg>
                    </a>
                </div>
                <button class="ghost" type="button" onclick="newConversation()"><span class="material-symbols-rounded">add</span>New Chat</button>
            </div>
            <div class="conv-list">
                <div class="conv-head">Recent Chats</div>
                <?php if (empty($conversations)): ?>
                    <div class="conv active" style="cursor:default;">
                        <div class="conv-title">Project kickoff questions</div>
                        <div class="conv-time">Demo conversation</div>
                    </div>
                    <div class="conv" style="cursor:default;">
                        <div class="conv-title">Marketing copy ideas</div>
                        <div class="conv-time">Demo conversation</div>
                    </div>
                    <div class="conv" style="cursor:default;">
                        <div class="conv-title">Build a 7-day study plan</div>
                        <div class="conv-time">Demo conversation</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <button class="conv <?php echo ((int)$conv['id'] === (int)$current_conversation_id) ? 'active' : ''; ?>" onclick="loadConversation(<?php echo (int)$conv['id']; ?>)">
                            <div class="conv-title"><?php echo htmlspecialchars($conv['title']); ?></div>
                            <div class="conv-time"><?php echo date('M j, g:i A', strtotime($conv['updated_at'])); ?></div>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="side-foot">
                <?php if ($user_role === 'admin'): ?>
                    <a class="side-link" href="<?php echo BASE_PATH; ?>pages/admin/index.php"><span class="material-symbols-rounded">admin_panel_settings</span><span>Admin</span></a>
                <?php endif; ?>
                <a class="side-link" href="<?php echo BASE_PATH; ?>pages/settings.php"><span class="material-symbols-rounded">settings</span><span>Settings</span></a>
                <a class="side-link" href="<?php echo BASE_PATH; ?>pages/logout.php"><span class="material-symbols-rounded">logout</span><span>Logout</span></a>
            </div>
        </aside>

        <main class="main">
            <header class="top">
                <div class="top-left">
                    <button class="menu" id="menuBtn" type="button" aria-label="Open conversations"><span class="material-symbols-rounded">menu</span></button>
                    <div class="title">Welcome, <?php echo htmlspecialchars($username); ?></div>
                </div>
                <div class="chip">Model: API</div>
            </header>

            <section class="scroll" id="chatScroll">
                <div class="stage">
                    <?php if (empty($messages)): ?>
                        <div class="empty" id="emptyState">
                            <h1>Welcome to InfoBot</h1>
                            <p>Ask anything. Draft, summarize, brainstorm, or debug in one place.</p>
                            <div class="cards">
                                <button class="card" type="button" onclick="insertStarterPrompt('Write a concise project kickoff brief with goals, scope, and risks.')">
                                    <div class="ctitle">Create a project brief</div>
                                    <div class="csub">Plan scope, timeline, and key deliverables.</div>
                                </button>
                                <button class="card" type="button" onclick="insertStarterPrompt('Summarize this meeting transcript into action items and owners.')">
                                    <div class="ctitle">Summarize a meeting</div>
                                    <div class="csub">Extract decisions, blockers, and action items.</div>
                                </button>
                                <button class="card" type="button" onclick="insertStarterPrompt('Help me debug a PHP error: Undefined array key in login flow.')">
                                    <div class="ctitle">Debug code issues</div>
                                    <div class="csub">Trace errors and suggest production-safe fixes.</div>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="msgs" id="messageList">
                        <?php foreach ($messages as $msg): ?>
                            <article class="msg <?php echo $msg['role'] === 'user' ? 'user' : 'assistant'; ?>">
                                <?php if ($msg['role'] !== 'user'): ?><div class="avatar" aria-hidden="true"><span class="material-symbols-rounded">smart_toy</span></div><?php endif; ?>
                                <div class="wrap">
                                    <div class="bubble"><?php echo nl2br(htmlspecialchars($msg['content'])); ?></div>
                                    <div class="meta">
                                        <time datetime="<?php echo htmlspecialchars($msg['created_at']); ?>"><?php echo date('g:i A', strtotime($msg['created_at'])); ?></time>
                                        <?php if ($msg['role'] !== 'user'): ?><button class="icon" type="button" onclick="copyMessage(this)" aria-label="Copy response"><span class="material-symbols-rounded" style="font-size:18px;">content_copy</span></button><?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div id="messageEnd" aria-hidden="true"></div>
                </div>
            </section>

            <div class="composer-shell">
                <div class="composer-stage">
                    <form class="composer" onsubmit="sendMessage(event)">
                        <textarea id="messageInput" rows="1" placeholder="Message InfoBot..." aria-label="Type a message"></textarea>
                        <button class="send" id="sendButton" type="submit" aria-label="Send"><span class="material-symbols-rounded">north_east</span></button>
                    </form>
                    <div class="hint">Enter to send Ã¢â‚¬Â¢ Shift + Enter for new line</div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const basePath = '<?php echo BASE_PATH; ?>';
        const conversationId = <?php echo (int)$current_conversation_id; ?>;
        const chatScroll = document.getElementById('chatScroll');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const menuBtn = document.getElementById('menuBtn');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const emptyState = document.getElementById('emptyState');
        const messageList = document.getElementById('messageList');
const messageEnd = document.getElementById('messageEnd');
        const DRAFT_KEY = `infobot_draft_${conversationId}`;

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('open')
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open')
        }
        if (menuBtn) menuBtn.addEventListener('click', openSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
        window.addEventListener('resize', () => {
            if (window.innerWidth > 960) closeSidebar();
        });

        function scrollToBottom() {
            chatScroll.scrollTop = chatScroll.scrollHeight;
        }

        let bottomFrame = null;

        function ensureBottom() {
            if (bottomFrame !== null) return;
            bottomFrame = requestAnimationFrame(() => {
                bottomFrame = null;
                chatScroll.scrollTop = chatScroll.scrollHeight;
            });
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }

        function getTime() {
            return new Date().toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit'
            });
        }

        function removeEmptyState() {
            if (emptyState && emptyState.parentNode) emptyState.remove();
        }

        function renderAssistantHtml(text) {
            let safe = escapeHtml(text || '').replace(/\r\n/g, '\n');

            const blocks = [];
            safe = safe.replace(/```([\s\S]*?)```/g, function(_, code) {
                const token = '__CODEBLOCK_' + blocks.length + '__';
                blocks.push('<pre><code>' + code.trim() + '</code></pre>');
                return token;
            });

            safe = safe.replace(/`([^`\n]+)`/g, '<code>$1</code>');
            safe = safe.replace(/\*\*\*([^*]+)\*\*\*/g, '<strong><em>$1</em></strong>');
            safe = safe.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            safe = safe.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');

            const lines = safe.split('\n');
            let html = '';
            let inUl = false;
            let inOl = false;

            for (const lineRaw of lines) {
                const line = lineRaw.trim();
                const ulMatch = line.match(/^[-*]\s+(.+)/);
                const olMatch = line.match(/^\d+\.\s+(.+)/);

                if (ulMatch) {
                    if (inOl) {
                        html += '</ol>';
                        inOl = false;
                    }
                    if (!inUl) {
                        html += '<ul>';
                        inUl = true;
                    }
                    html += '<li>' + ulMatch[1] + '</li>';
                    continue;
                }

                if (olMatch) {
                    if (inUl) {
                        html += '</ul>';
                        inUl = false;
                    }
                    if (!inOl) {
                        html += '<ol>';
                        inOl = true;
                    }
                    html += '<li>' + olMatch[1] + '</li>';
                    continue;
                }

                if (inUl) {
                    html += '</ul>';
                    inUl = false;
                }
                if (inOl) {
                    html += '</ol>';
                    inOl = false;
                }

                html += line === '' ? '<br>' : (line + '<br>');
            }

            if (inUl) html += '</ul>';
            if (inOl) html += '</ol>';

            html = html.replace(/(<br>)+$/, '');
            blocks.forEach((block, i) => {
                html = html.replace('__CODEBLOCK_' + i + '__', block);
            });

            return html;
        }

        function createMsg(role, content) {
            const el = document.createElement('article');
            el.className = `msg ${role}`;
            const nowIso = new Date().toISOString();
            const safe = escapeHtml(content).replace(/\n/g, '<br>');
            if (role === 'assistant') {
                el.innerHTML = `<div class="avatar" aria-hidden="true"><span class="material-symbols-rounded">smart_toy</span></div><div class="wrap"><div class="bubble">${renderAssistantHtml(content)}</div><div class="meta"><time datetime="${nowIso}">${getTime()}</time><button class="icon" type="button" onclick="copyMessage(this)" aria-label="Copy response"><span class="material-symbols-rounded" style="font-size:18px;">content_copy</span></button></div></div>`;
            } else {
                el.innerHTML = `<div class="wrap"><div class="bubble">${safe}</div><div class="meta"><time datetime="${nowIso}">${getTime()}</time></div></div>`;
            }
            return el;
        }

        function addMessage(role, content) {
            removeEmptyState();
            const el = createMsg(role, content);
            messageList.appendChild(el);
            if (role === 'assistant') {
                ensureBottom();
            }
            return el;
        }

        function showTyping() {
            removeEmptyState();
            const el = document.createElement('article');
            el.className = 'msg assistant';
            el.id = 'typingIndicator';
            el.innerHTML = '<div class="avatar" aria-hidden="true"><span class="material-symbols-rounded">smart_toy</span></div><div class="wrap"><div class="bubble"><div class="typing"><span></span><span></span><span></span></div></div></div>';
            messageList.appendChild(el);
            ensureBottom();
            return el;
        }

        function copyMessage(btn) {
            const bubble = btn.closest('.wrap').querySelector('.bubble');
            const text = bubble.textContent || '';
            navigator.clipboard.writeText(text).then(() => {
                const icon = btn.querySelector('.material-symbols-rounded');
                const prev = icon.textContent;
                icon.textContent = 'check';
                setTimeout(() => {
                    icon.textContent = prev;
                }, 1100);
            }).catch(() => {});
        }

        function autoResize() {
            messageInput.style.height = 'auto';
            messageInput.style.height = Math.min(messageInput.scrollHeight, 180) + 'px';
        }

        function saveDraft() {
            const t = messageInput.value.trim();
            if (t) localStorage.setItem(DRAFT_KEY, messageInput.value);
            else localStorage.removeItem(DRAFT_KEY);
        }

        function restoreDraft() {
            const d = localStorage.getItem(DRAFT_KEY);
            if (d) {
                messageInput.value = d;
                autoResize();
            }
        }

        function insertStarterPrompt(text) {
            messageInput.value = text;
            autoResize();
            saveDraft();
            messageInput.focus();
        }

        async function sendMessage(event) {
            event.preventDefault();
            const content = messageInput.value.trim();
            if (!content) return;

            sendButton.disabled = true;
            messageInput.disabled = true;
            addMessage('user', content);
            messageInput.value = '';
            autoResize();
            localStorage.removeItem(DRAFT_KEY);

            const typing = showTyping();

            try {
                const response = await fetch(basePath + 'api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        message: content
                    })
                });
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();
                if (typing && typing.parentNode) typing.remove();
                if (data.success) {
                    addMessage('assistant', data.message || '');
                } else {
                    addMessage('assistant', 'Error: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                if (typing && typing.parentNode) typing.remove();
                addMessage('assistant', 'Connection issue. Please try again.');
                console.error(error);
            }

            messageInput.disabled = false;
            sendButton.disabled = false;
            messageInput.focus();
        }

        function loadConversation(convId) {
            window.location.href = basePath + 'pages/chat.php?conversation_id=' + convId;
        }

        function newConversation() {
            window.location.href = basePath + 'pages/chat.php?new=true';
        }

        messageInput.addEventListener('input', () => {
            autoResize();
            saveDraft();
        });
        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(e);
            }
        });
        window.addEventListener('beforeunload', saveDraft);
        restoreDraft();
        autoResize();
        ensureBottom();
        document.querySelectorAll('.msg.assistant .bubble').forEach((bubble) => {
            bubble.innerHTML = renderAssistantHtml(bubble.textContent || '');
        });
    </script>
</body>

</html>

