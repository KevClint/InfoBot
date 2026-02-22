<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/chatbot.php';
require_once __DIR__ . '/../includes/sidebar.php';

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
$current_conversation_title = 'Conversation';
foreach ($conversations as $conv) {
    if ((int)($conv['id'] ?? 0) === (int)$current_conversation_id) {
        $current_conversation_title = (string)($conv['title'] ?? 'Conversation');
        break;
    }
}
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
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
            font-size: calc(16px * var(--font-scale, 1));
            font-weight: 700;
            margin-bottom: 12px
        }

        .brand-actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: space-between
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
            transition: .16s
        }

        .brand-collapse:hover {
            background: rgba(30, 41, 59, .8);
            border-color: rgba(148, 163, 184, .56);
            transform: translateY(-1px)
        }

        .brand-collapse .material-symbols-rounded {
            font-size: calc(18px * var(--font-scale, 1))
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
            font-weight: 600;
            text-decoration: none
        }

        .ghost:hover {
            background: rgba(30, 41, 59, .75);
            border-color: rgba(148, 163, 184, .46)
        }

        .conv-head {
            padding: 2px 8px 10px;
            font-size: calc(11px * var(--font-scale, 1));
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #93a3be
        }

        .conv-list {
            flex: 1;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, .52) transparent;
            padding: 10px 10px 12px
        }

        .conv-list::-webkit-scrollbar {
            width: 11px
        }

        .conv-list::-webkit-scrollbar-track {
            background: transparent
        }

        .conv-list::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .48);
            border-radius: 999px;
            border: 3px solid transparent;
            background-clip: content-box
        }

        .conv-list::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, .7);
            background-clip: content-box
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
            display: block;
            text-decoration: none
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
            border-color: color-mix(in srgb, var(--accent) 55%, transparent)
        }

        .conv.active::before {
            background: var(--accent)
        }

        .conv-title {
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .conv-time {
            font-size: calc(12px * var(--font-scale, 1));
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
            border-radius: 10px;
            color: #cbd5e1;
            text-decoration: none;
            border: 1px solid transparent;
            font-size: calc(14px * var(--font-scale, 1))
        }

        .side-link:hover {
            background: rgba(30, 41, 59, .75);
            color: #f8fafc;
            border-color: rgba(148, 163, 184, .34)
        }

        .side-link.active {
            background: rgba(30, 41, 59, .95);
            color: #f8fafc;
            border-color: rgba(99, 102, 241, .55)
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
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            gap: 10px;
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

        .top-brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text);
            text-decoration: none;
            font-size: calc(15px * var(--font-scale, 1));
            font-weight: 700;
            white-space: nowrap
        }

        .top-brand .material-symbols-rounded {
            font-size: calc(18px * var(--font-scale, 1))
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

        .top-center {
            justify-self: center;
            min-width: 0;
            max-width: min(62vw, 720px)
        }

        .conversation-title {
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 600;
            color: var(--sub);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center
        }

        .top-right {
            justify-self: end
        }

        .chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            padding: 5px 10px;
            font-size: calc(12px * var(--font-scale, 1));
            color: var(--sub)
        }

        body.sidebar-collapsed .sidebar {
            width: 76px;
            min-width: 76px
        }

        body.sidebar-collapsed .main {
            margin-left: 76px
        }

        body.sidebar-collapsed .side-top {
            padding: 12px 8px
        }

        body.sidebar-collapsed .brand {
            justify-content: center;
            margin-bottom: 0
        }

        body.sidebar-collapsed .ghost,
        body.sidebar-collapsed .conv-list {
            display: none
        }

        body.sidebar-collapsed .brand-actions .brand-github {
            display: none
        }

        body.sidebar-collapsed .brand-actions {
            width: auto
        }

        body.sidebar-collapsed .side-foot {
            padding: 10px 8px
        }

        body.sidebar-collapsed .side-link {
            justify-content: center;
            padding: 10px
        }

        body.sidebar-collapsed .side-link span:last-child {
            display: none
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
            font-family: 'Sora', sans-serif;
            font-size: calc(34px * var(--font-scale, 1));
            font-weight: 600;
            letter-spacing: -.02em;
            margin-bottom: 10px
        }

        .empty p {
            font-family: 'Sora', sans-serif;
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
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 600;
            margin-bottom: 4px
        }

        .csub {
            font-size: calc(12px * var(--font-scale, 1));
            color: var(--sub)
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
            font-size: calc(14px * var(--font-scale, 1));
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
            font-size: calc(12px * var(--font-scale, 1));
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

        .provider-switch {
            position: relative;
            flex: 0 0 auto
        }

        .provider-btn {
            height: 38px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: var(--sub);
            padding: 0 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: calc(13px * var(--font-scale, 1));
            font-weight: 600
        }

        .provider-btn:hover {
            border-color: #cbd5e1
        }

        .provider-btn:disabled {
            opacity: .55;
            cursor: not-allowed
        }

        .provider-label {
            max-width: 100px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .provider-menu {
            position: absolute;
            bottom: 46px;
            left: 0;
            width: 210px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            box-shadow: var(--shadow);
            padding: 6px;
            display: none;
            z-index: 25
        }

        .provider-menu.open {
            display: block
        }

        .provider-option {
            width: 100%;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 8px
        }

        .provider-option:hover {
            background: rgba(79, 70, 229, .07)
        }

        .provider-option.active {
            background: rgba(79, 70, 229, .1);
            border-color: rgba(79, 70, 229, .24)
        }

        .provider-option > .material-symbols-rounded:last-child {
            opacity: 0
        }

        .provider-option.active > .material-symbols-rounded:last-child {
            opacity: 1
        }

        .provider-option-main {
            display: inline-flex;
            align-items: center;
            gap: 8px
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
            font-size: calc(15px * var(--font-scale, 1));
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
            font-size: calc(12px * var(--font-scale, 1));
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

            .brand-collapse {
                display: none
            }

            .top-brand .brand-word {
                display: none
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

            body.sidebar-collapsed .sidebar {
                width: var(--sidew);
                min-width: var(--sidew)
            }

            body.sidebar-collapsed .main {
                margin-left: 0
            }

            .cards {
                grid-template-columns: 1fr
            }

            .top {
                grid-template-columns: auto minmax(0, 1fr) auto;
                padding: 0 12px
            }

            .top-center {
                max-width: 56vw
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
        html.dark-mode .starter-card,
        html.dark-mode .card {
            background: var(--panel);
            color: var(--text);
            border-color: var(--line)
        }

        html.dark-mode .starter-title {
            color: var(--text)
        }

        html.dark-mode .starter-sub,
        html.dark-mode .csub {
            color: var(--sub)
        }

        html.dark-mode .ctitle {
            color: var(--text)
        }

        html.dark-mode .card:hover {
            border-color: #475569
        }

        html.dark-mode .composer {
            background: var(--panel);
            border-color: var(--line)
        }

        html.dark-mode .provider-btn,
        html.dark-mode .provider-menu {
            background: var(--panel);
            border-color: var(--line);
            color: var(--text)
        }

        html.dark-mode .provider-option:hover {
            background: rgba(99, 102, 241, .16)
        }

        html.dark-mode .provider-option.active {
            background: rgba(99, 102, 241, .22);
            border-color: rgba(129, 140, 248, .38)
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
        <?php
        renderAppSidebar([
            'base_path' => BASE_PATH,
            'conversations' => $conversations,
            'current_conversation_id' => $current_conversation_id,
            'current_page' => 'chat',
            'user_role' => $user_role,
            'show_recent' => true,
            'show_new_chat' => true
        ]);
        ?>

        <main class="main">
            <header class="top">
                <div class="top-left">
                    <button class="menu" id="menuBtn" type="button" aria-label="Open conversations"><span class="material-symbols-rounded">menu</span></button>
                    <a class="top-brand" href="<?php echo BASE_PATH; ?>pages/chat.php">
                        <span class="material-symbols-rounded">smart_toy</span>
                        <span class="brand-word">InfoBot</span>
                    </a>
                </div>
                <div class="top-center">
                    <div class="conversation-title"><?php echo htmlspecialchars($current_conversation_title); ?></div>
                </div>
                <div class="top-right">
                    <div class="chip" id="modelBadge">Model: API (Groq)</div>
                </div>
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
                                        <?php if ($msg['role'] !== 'user'): ?><button class="icon" type="button" onclick="copyMessage(this)" aria-label="Copy response"><span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">content_copy</span></button><?php endif; ?>
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
                        <div class="provider-switch">
                            <button class="provider-btn" id="providerButton" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="providerMenu">
                                <span class="material-symbols-rounded" id="providerIcon" style="font-size: calc(18px * var(--font-scale, 1));">cloud</span>
                                <span class="provider-label" id="providerLabel">API</span>
                                <span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">expand_more</span>
                            </button>
                            <div class="provider-menu" id="providerMenu" role="menu">
                                <button class="provider-option" type="button" data-provider="api" role="menuitem">
                                    <span class="provider-option-main"><span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">cloud</span><span>API (Groq)</span></span>
                                    <span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">check</span>
                                </button>
                                <button class="provider-option" type="button" data-provider="local" role="menuitem">
                                    <span class="provider-option-main"><span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">memory</span><span>Local (Ollama)</span></span>
                                    <span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">check</span>
                                </button>
                            </div>
                        </div>
                        <textarea id="messageInput" rows="1" placeholder="Message InfoBot..." aria-label="Type a message"></textarea>
                        <button class="send" id="sendButton" type="submit" aria-label="Send"><span class="material-symbols-rounded">north_east</span></button>
                    </form>
                    <div class="hint">Enter to send, Shift + Enter for new line</div>
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
        const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
        const sidebarCollapseIcon = document.getElementById('sidebarCollapseIcon');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const emptyState = document.getElementById('emptyState');
        const messageList = document.getElementById('messageList');
        const messageEnd = document.getElementById('messageEnd');
        const modelBadge = document.getElementById('modelBadge');
        const providerButton = document.getElementById('providerButton');
        const providerMenu = document.getElementById('providerMenu');
        const providerLabel = document.getElementById('providerLabel');
        const providerIcon = document.getElementById('providerIcon');
        const providerOptions = document.querySelectorAll('.provider-option');
        const DRAFT_KEY = `infobot_draft_${conversationId}`;
        const QUICK_PROMPT_KEY = 'infobot_quick_prompt';
        const PROVIDER_KEY = 'infobot_provider';
        const SIDEBAR_COLLAPSE_KEY = 'infobot_sidebar_collapsed';
        const PROVIDER_META = {
            api: {
                short: 'API',
                label: 'API (<?php echo htmlspecialchars(GROQ_MODEL); ?>)',
                icon: 'cloud'
            },
            local: {
                short: 'Local',
                label: 'Local (<?php echo htmlspecialchars(LLM_MODEL); ?>)',
                icon: 'memory'
            }
        };
        let selectedProvider = 'api';

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('open')
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open')
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

        if (menuBtn) menuBtn.addEventListener('click', openSidebar);
        if (sidebarCollapseBtn) sidebarCollapseBtn.addEventListener('click', toggleSidebarCollapsed);
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

        function setProvider(provider) {
            const nextProvider = PROVIDER_META[provider] ? provider : 'api';
            selectedProvider = nextProvider;

            const meta = PROVIDER_META[nextProvider];
            providerLabel.textContent = meta.short;
            providerIcon.textContent = meta.icon;
            if (modelBadge) {
                modelBadge.textContent = 'Model: ' + meta.label;
            }

            providerOptions.forEach((option) => {
                option.classList.toggle('active', option.dataset.provider === nextProvider);
            });

            localStorage.setItem(PROVIDER_KEY, nextProvider);
            try {
                window.dispatchEvent(new CustomEvent('infobot:provider-changed', {
                    detail: {
                        provider: nextProvider
                    }
                }));
            } catch (e) {
                // Ignore dispatch failures in older environments.
            }
        }

        function toggleProviderMenu(forceOpen) {
            const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !providerMenu.classList.contains('open');
            providerMenu.classList.toggle('open', shouldOpen);
            providerButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        }

        function closeProviderMenu() {
            toggleProviderMenu(false);
        }

        function restoreProvider() {
            const saved = localStorage.getItem(PROVIDER_KEY);
            setProvider(saved && PROVIDER_META[saved] ? saved : 'api');
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
                el.innerHTML = `<div class="avatar" aria-hidden="true"><span class="material-symbols-rounded">smart_toy</span></div><div class="wrap"><div class="bubble">${renderAssistantHtml(content)}</div><div class="meta"><time datetime="${nowIso}">${getTime()}</time><button class="icon" type="button" onclick="copyMessage(this)" aria-label="Copy response"><span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">content_copy</span></button></div></div>`;
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

        function restoreQuickPrompt() {
            const prompt = localStorage.getItem(QUICK_PROMPT_KEY);
            if (!prompt) return;
            if (messageInput.value.trim()) return;
            messageInput.value = prompt;
            autoResize();
            saveDraft();
            localStorage.removeItem(QUICK_PROMPT_KEY);
            messageInput.focus();
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
            providerButton.disabled = true;
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
                        message: content,
                        provider: selectedProvider
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
            providerButton.disabled = false;
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
        providerButton.addEventListener('click', () => {
            toggleProviderMenu();
        });
        providerOptions.forEach((option) => {
            option.addEventListener('click', () => {
                setProvider(option.dataset.provider || 'api');
                closeProviderMenu();
            });
        });
        document.addEventListener('click', (event) => {
            if (!providerMenu.contains(event.target) && !providerButton.contains(event.target)) {
                closeProviderMenu();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeProviderMenu();
            }
        });
        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(e);
            }
        });
        window.addEventListener('beforeunload', saveDraft);
        restoreSidebarCollapsed();
        restoreProvider();
        restoreDraft();
        restoreQuickPrompt();
        autoResize();
        ensureBottom();
        document.querySelectorAll('.msg.assistant .bubble').forEach((bubble) => {
            bubble.innerHTML = renderAssistantHtml(bubble.textContent || '');
        });
    </script>
</body>

</html>
