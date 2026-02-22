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
$message_page_size = 80;
$older_chunk_count = isset($_GET['msg_offset']) ? max(0, intval($_GET['msg_offset'])) : 0;

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

$total_message_count = getConversationMessageCount($current_conversation_id);
$visible_window_size = $message_page_size + $older_chunk_count;
$messages = getConversationMessagesPage($current_conversation_id, $visible_window_size, 0);
$has_older_messages = count($messages) < $total_message_count;
$older_offset = $older_chunk_count + $message_page_size;
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

        .older-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 14px;
        }

        .older-btn {
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            color: var(--sub);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            font-size: calc(13px * var(--font-scale, 1));
        }

        .older-btn:hover {
            border-color: #cbd5e1;
            color: var(--text);
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
            padding: 14px 20px 16px;
            background: linear-gradient(to top, rgba(248, 250, 252, 1) 64%, rgba(248, 250, 252, .7) 86%, rgba(248, 250, 252, 0))
        }

        .composer-stage {
            max-width: var(--stage);
            margin: 0 auto
        }

        .jump-latest-wrap {
            position: fixed;
            right: 28px;
            bottom: 112px;
            z-index: 55;
            pointer-events: none
        }

        .jump-latest-btn {
            width: 40px;
            height: 40px;
            border: 1px solid color-mix(in srgb, var(--accent) 70%, #1f2937 30%);
            border-radius: 999px;
            background: var(--accent);
            color: #f8fafc;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 24px color-mix(in srgb, var(--accent) 35%, transparent), 0 0 0 2px rgba(255, 255, 255, .9);
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
            transition: opacity .16s ease, transform .16s ease, border-color .16s ease
        }

        .jump-latest-btn:hover {
            border-color: color-mix(in srgb, var(--accent-h) 75%, #1f2937 25%);
            background: var(--accent-h);
            color: #f8fafc
        }

        .jump-latest-btn.hide {
            opacity: 0;
            transform: translateY(6px);
            pointer-events: none
        }

        .composer {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #fff;
            box-shadow: var(--shadow);
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: stretch;
            overflow: visible
        }

        .composer-row {
            display: flex;
            gap: 8px;
            align-items: center;
            min-width: 0
        }

        .attachment-tray {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height .18s ease, opacity .18s ease;
            min-width: 0
        }

        .composer.has-attachments .attachment-tray {
            max-height: 94px;
            opacity: 1
        }

        .attachment-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 2px;
            min-width: 0;
            scrollbar-width: thin
        }

        .attachment-item {
            width: 82px;
            height: 82px;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            flex: 0 0 auto;
            position: relative;
            background: #f8fafc
        }

        .attachment-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block
        }

        .attachment-remove {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 20px;
            height: 20px;
            border: 0;
            border-radius: 999px;
            background: rgba(15, 23, 42, .78);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer
        }

        .attachment-remove .material-symbols-rounded {
            font-size: calc(14px * var(--font-scale, 1))
        }

        .provider-switch {
            position: relative;
            flex: 0 0 auto
        }

        .provider-btn {
            height: 34px;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: #fff;
            color: var(--sub);
            padding: 0 9px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: calc(12px * var(--font-scale, 1));
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
            bottom: 40px;
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

        .composer-icon {
            width: 34px;
            height: 34px;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: #fff;
            color: var(--sub);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex: 0 0 auto
        }

        .composer-icon:hover {
            border-color: #cbd5e1;
            color: var(--text)
        }

        .composer-icon:disabled {
            opacity: .5;
            cursor: not-allowed
        }

        .voice-btn.listening {
            color: #ef4444;
            border-color: rgba(239, 68, 68, .45);
            background: rgba(254, 226, 226, .75)
        }

        .composer textarea {
            flex: 1;
            border: 0;
            background: transparent;
            resize: none;
            outline: none;
            min-height: 20px;
            max-height: 160px;
            font-family: 'Inter', sans-serif;
            font-size: calc(15px * var(--font-scale, 1));
            color: var(--text);
            line-height: 1.4;
            padding: 4px 2px;
            min-width: 0
        }

        .composer textarea::placeholder {
            color: #94a3b8
        }

        .send {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 9px;
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

        .hint {
            margin-top: 8px;
            font-size: calc(12px * var(--font-scale, 1));
            color: #94a3b8;
            text-align: center
        }

        .hint.error {
            color: #ef4444
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

            .composer {
                padding: 7px
            }

            .composer-row {
                gap: 6px
            }

            .provider-btn {
                padding: 0 8px
            }

            .provider-label {
                max-width: 62px
            }

            .jump-latest-wrap {
                right: 14px;
                bottom: 102px
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
        html.dark-mode .card,
        html.dark-mode .older-btn {
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
        html.dark-mode .composer-icon,
        html.dark-mode .provider-menu {
            background: var(--panel);
            border-color: var(--line);
            color: var(--text)
        }

        html.dark-mode .jump-latest-btn {
            background: var(--accent);
            border-color: color-mix(in srgb, var(--accent) 75%, #0f172a 25%);
            color: #f8fafc;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .4), 0 0 0 2px rgba(15, 23, 42, .7)
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

        html.dark-mode .attachment-item {
            background: rgba(15, 23, 42, .55);
            border-color: var(--line)
        }

        html.dark-mode .attachment-remove {
            background: rgba(2, 6, 23, .85)
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
                    <?php if ($total_message_count === 0): ?>
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

                    <?php if ($has_older_messages): ?>
                        <div class="older-wrap">
                            <a class="older-btn" href="<?php echo BASE_PATH; ?>pages/chat.php?conversation_id=<?php echo (int)$current_conversation_id; ?>&msg_offset=<?php echo (int)$older_offset; ?>">
                                <span class="material-symbols-rounded">history</span>
                                <span>Load older messages</span>
                            </a>
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
                    <div class="jump-latest-wrap">
                        <button class="jump-latest-btn" id="jumpLatestBtn" type="button" aria-label="Jump to latest message">
                            <span class="material-symbols-rounded">keyboard_arrow_down</span>
                        </button>
                    </div>
                    <form class="composer" onsubmit="sendMessage(event)">
                        <div class="attachment-tray" id="attachmentTray" aria-live="polite">
                            <div class="attachment-scroll" id="attachmentScroll"></div>
                        </div>
                        <div class="composer-row">
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
                                    <button class="provider-option" type="button" data-provider="hf" role="menuitem">
                                        <span class="provider-option-main"><span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">hub</span><span>API (Hugging Face)</span></span>
                                        <span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">check</span>
                                    </button>
                                    <button class="provider-option" type="button" data-provider="local_llama" role="menuitem">
                                        <span class="provider-option-main"><span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">memory</span><span>Local (Llama 3.2)</span></span>
                                        <span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">check</span>
                                    </button>
                                    <button class="provider-option" type="button" data-provider="local_gemma" role="menuitem">
                                        <span class="provider-option-main"><span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">image</span><span>Local (Gemma 3 4B)</span></span>
                                        <span class="material-symbols-rounded" style="font-size: calc(18px * var(--font-scale, 1));">check</span>
                                    </button>
                                </div>
                            </div>
                            <input id="attachmentInput" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden>
                            <button class="composer-icon attach-btn" id="attachButton" type="button" aria-label="Attach image">
                                <span class="material-symbols-rounded">attach_file</span>
                            </button>
                            <textarea id="messageInput" rows="1" placeholder="Message InfoBot..." aria-label="Type a message"></textarea>
                            <button class="composer-icon voice-btn" id="voiceButton" type="button" aria-label="Voice input">
                                <span class="material-symbols-rounded" id="voiceIcon">mic</span>
                            </button>
                            <button class="send" id="sendButton" type="submit" aria-label="Send">
                                <span class="material-symbols-rounded" id="sendIcon">north_east</span>
                            </button>
                        </div>
                    </form>
                    <div class="hint" id="composerHint">Enter to send, Shift + Enter for new line</div>
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
        const composerForm = document.querySelector('.composer');
        const messageInput = document.getElementById('messageInput');
        const attachmentInput = document.getElementById('attachmentInput');
        const attachButton = document.getElementById('attachButton');
        const attachmentTray = document.getElementById('attachmentTray');
        const attachmentScroll = document.getElementById('attachmentScroll');
        const voiceButton = document.getElementById('voiceButton');
        const voiceIcon = document.getElementById('voiceIcon');
        const sendButton = document.getElementById('sendButton');
        const sendIcon = document.getElementById('sendIcon');
        const jumpLatestBtn = document.getElementById('jumpLatestBtn');
        const composerHint = document.getElementById('composerHint');
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
        const MAX_ATTACHMENTS = 4;
        const MAX_ATTACHMENT_BYTES = 5 * 1024 * 1024;
        const ALLOWED_ATTACHMENT_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        const LOCAL_LLAMA_MODEL = '<?php echo htmlspecialchars(LLM_MODEL_LLAMA); ?>';
        const LOCAL_GEMMA_MODEL = '<?php echo htmlspecialchars(LLM_MODEL_GEMMA); ?>';
        const PROVIDER_META = {
            api: {
                short: 'API',
                label: 'API (<?php echo htmlspecialchars(GROQ_MODEL); ?>)',
                icon: 'cloud',
                requestProvider: 'api',
                localModel: '',
                supportsAttachments: false,
                supportsVoice: false
            },
            hf: {
                short: 'HF',
                label: 'HF (<?php echo htmlspecialchars(HF_MODEL); ?>)',
                icon: 'hub',
                requestProvider: 'hf',
                localModel: '',
                supportsAttachments: true,
                supportsVoice: false
            },
            local_llama: {
                short: 'Llama',
                label: 'Local (' + LOCAL_LLAMA_MODEL + ')',
                icon: 'memory',
                requestProvider: 'local',
                localModel: LOCAL_LLAMA_MODEL,
                supportsAttachments: false,
                supportsVoice: false
            },
            local_gemma: {
                short: 'Gemma',
                label: 'Local (' + LOCAL_GEMMA_MODEL + ')',
                icon: 'image',
                requestProvider: 'local',
                localModel: LOCAL_GEMMA_MODEL,
                supportsAttachments: true,
                supportsVoice: true
            }
        };
        let selectedProvider = 'api';
        let selectedAttachments = [];
        let isGenerating = false;
        let activeController = null;
        let speechRecognizer = null;
        let voiceSupportedByBrowser = false;
        let isListening = false;

        function setComposerHint(message, isError = false) {
            if (!composerHint) return;
            composerHint.textContent = message;
            composerHint.classList.toggle('error', !!isError);
        }

        function resetComposerHint() {
            setComposerHint('Enter to send, Shift + Enter for new line', false);
        }

        function currentProviderMeta() {
            return PROVIDER_META[selectedProvider] || PROVIDER_META.api;
        }

        function canUseAttachments() {
            return !!currentProviderMeta().supportsAttachments;
        }

        function canUseVoice() {
            return !!currentProviderMeta().supportsVoice && !!voiceSupportedByBrowser;
        }

        function refreshInputCapabilities() {
            const attachmentsAllowed = canUseAttachments();
            const voiceAllowed = canUseVoice();

            if (attachButton) {
                attachButton.disabled = isGenerating || !attachmentsAllowed;
                attachButton.title = attachmentsAllowed ? 'Attach image' : 'Images are available only with Local Gemma 3 (4B) or HF vision models.';
            }
            if (voiceButton) {
                voiceButton.disabled = isGenerating || !voiceAllowed;
                voiceButton.title = voiceAllowed ? 'Voice input' : (voiceSupportedByBrowser ? 'Voice is available only with Local Gemma 3 (4B).' : 'Voice input not supported in this browser');
            }

            if (!attachmentsAllowed && selectedAttachments.length > 0) {
                clearAttachments();
            }

            if (!voiceAllowed && isListening && speechRecognizer) {
                speechRecognizer.stop();
            }
        }

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
        if (jumpLatestBtn) jumpLatestBtn.addEventListener('click', jumpToLatest);
        if (overlay) overlay.addEventListener('click', closeSidebar);
        window.addEventListener('resize', () => {
            if (window.innerWidth > 960) closeSidebar();
            updateJumpLatestVisibility();
        });
        if (chatScroll) {
            chatScroll.addEventListener('scroll', updateJumpLatestVisibility);
        }
        window.addEventListener('scroll', updateJumpLatestVisibility, {
            passive: true
        });

        function scrollToBottom() {
            if (!chatScroll) return;
            chatScroll.scrollTop = chatScroll.scrollHeight;
        }

        function isNearBottom(threshold = 64) {
            if (chatScroll && chatScroll.scrollHeight > (chatScroll.clientHeight + 4)) {
                return (chatScroll.scrollHeight - chatScroll.scrollTop - chatScroll.clientHeight) <= threshold;
            }
            const doc = document.documentElement;
            if (!doc) return true;
            return (doc.scrollHeight - window.scrollY - window.innerHeight) <= threshold;
        }

        function updateJumpLatestVisibility() {
            if (!jumpLatestBtn) return;
            jumpLatestBtn.classList.toggle('hide', isNearBottom(10));
        }

        function jumpToLatest() {
            if (!chatScroll) return;
            const assistantMessages = messageList ? Array.from(messageList.querySelectorAll('.msg.assistant')) : [];
            const latestAssistant = assistantMessages.reverse().find((node) => node && node.id !== 'typingIndicator') || null;
            const target = latestAssistant || messageEnd;
            if (target && typeof target.scrollIntoView === 'function') {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'end'
                });
            }
            // Fallback for containers where scrollIntoView does not target the intended scroller.
            requestAnimationFrame(() => {
                if (!latestAssistant) {
                    chatScroll.scrollTop = chatScroll.scrollHeight;
                    return;
                }
                const lowPoint = (latestAssistant.offsetTop + latestAssistant.offsetHeight) - chatScroll.clientHeight + 20;
                chatScroll.scrollTop = Math.max(0, lowPoint);
            });
            setTimeout(() => {
                if (!latestAssistant) {
                    chatScroll.scrollTop = chatScroll.scrollHeight;
                    return;
                }
                const lowPoint = (latestAssistant.offsetTop + latestAssistant.offsetHeight) - chatScroll.clientHeight + 20;
                chatScroll.scrollTop = Math.max(0, lowPoint);
            }, 120);
        }

        let bottomFrame = null;

        function ensureBottom() {
            if (bottomFrame !== null) return;
            bottomFrame = requestAnimationFrame(() => {
                bottomFrame = null;
                scrollToBottom();
                updateJumpLatestVisibility();
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

        function setComposerGeneratingState(generating) {
            isGenerating = generating;
            if (sendIcon) {
                sendIcon.textContent = generating ? 'stop' : 'north_east';
            }
            if (sendButton) {
                sendButton.setAttribute('aria-label', generating ? 'Stop generating' : 'Send');
            }
            if (providerButton) providerButton.disabled = generating;
            if (attachmentInput) attachmentInput.disabled = generating;
            if (messageInput) messageInput.disabled = generating;
            refreshInputCapabilities();
        }

        function renderAttachmentTray() {
            if (!composerForm || !attachmentTray || !attachmentScroll) return;

            attachmentScroll.innerHTML = '';
            const hasAttachments = selectedAttachments.length > 0;
            composerForm.classList.toggle('has-attachments', hasAttachments);

            if (!hasAttachments) return;

            selectedAttachments.forEach((item) => {
                const card = document.createElement('div');
                card.className = 'attachment-item';

                const img = document.createElement('img');
                img.src = item.previewUrl;
                img.alt = item.name || 'Attachment preview';
                card.appendChild(img);

                const remove = document.createElement('button');
                remove.className = 'attachment-remove';
                remove.type = 'button';
                remove.setAttribute('aria-label', 'Remove attachment');
                remove.innerHTML = '<span class="material-symbols-rounded">close</span>';
                remove.addEventListener('click', () => {
                    selectedAttachments = selectedAttachments.filter((x) => x.id !== item.id);
                    renderAttachmentTray();
                });
                card.appendChild(remove);

                attachmentScroll.appendChild(card);
            });
        }

        function buildAttachmentPayload() {
            return selectedAttachments.map((item) => ({
                name: item.name,
                mime: item.mime,
                base64: item.base64
            }));
        }

        function clearAttachments() {
            selectedAttachments = [];
            if (attachmentInput) attachmentInput.value = '';
            renderAttachmentTray();
        }

        function fileToDataUrl(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(String(reader.result || ''));
                reader.onerror = () => reject(new Error('Failed to read file'));
                reader.readAsDataURL(file);
            });
        }

        async function addAttachmentFiles(fileList) {
            if (!canUseAttachments()) {
                clearAttachments();
                setComposerHint('Images are available only with Local Gemma 3 (4B).', true);
                return;
            }
            if (!fileList || fileList.length === 0) return;

            const incoming = Array.from(fileList);
            let added = 0;
            for (const file of incoming) {
                if (selectedAttachments.length >= MAX_ATTACHMENTS) break;
                if (!file || !ALLOWED_ATTACHMENT_TYPES.includes(file.type)) continue;
                if (file.size > MAX_ATTACHMENT_BYTES) continue;

                try {
                    const dataUrl = await fileToDataUrl(file);
                    const splitIndex = dataUrl.indexOf(',');
                    const base64 = splitIndex >= 0 ? dataUrl.slice(splitIndex + 1) : '';
                    if (!base64) continue;

                    selectedAttachments.push({
                        id: Date.now() + Math.floor(Math.random() * 100000),
                        name: file.name || 'image',
                        mime: file.type,
                        base64: base64,
                        previewUrl: dataUrl
                    });
                    added++;
                } catch (e) {
                    console.error(e);
                }
            }

            renderAttachmentTray();
            if (added > 0) {
                resetComposerHint();
            } else {
                setComposerHint('No valid images added (jpg/png/webp/gif, up to 5MB each).', true);
            }
        }

        function getUserPreviewContent(text, attachmentCount) {
            const clean = text.trim();
            if (!attachmentCount) return clean;
            const label = attachmentCount === 1 ? '[Image attached]' : '[' + attachmentCount + ' images attached]';
            return clean ? (clean + '\n\n' + label) : label;
        }

        function stopGeneration() {
            if (!isGenerating) return;
            if (activeController) {
                activeController.abort();
            }
        }

        function updateVoiceButtonState(listening) {
            isListening = listening;
            if (voiceButton) {
                voiceButton.classList.toggle('listening', listening);
                voiceButton.setAttribute('aria-label', listening ? 'Stop voice input' : 'Voice input');
            }
            if (voiceIcon) {
                voiceIcon.textContent = listening ? 'mic_off' : 'mic';
            }
        }

        function initSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            voiceSupportedByBrowser = !!SpeechRecognition;
            if (!SpeechRecognition || !voiceButton) {
                if (voiceButton) {
                    voiceButton.disabled = true;
                    voiceButton.title = 'Voice input not supported in this browser';
                }
                setComposerHint('Voice input is not supported in this browser.', true);
                return;
            }

            speechRecognizer = new SpeechRecognition();
            speechRecognizer.lang = 'en-US';
            speechRecognizer.continuous = false;
            speechRecognizer.interimResults = false;

            speechRecognizer.onresult = (event) => {
                let finalText = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0] ? event.results[i][0].transcript : '';
                    if (event.results[i].isFinal) finalText += transcript;
                }

                const nextText = finalText.trim();
                if (!nextText) return;
                const prefix = messageInput.value.trim();
                messageInput.value = prefix ? (prefix + (prefix.endsWith(' ') ? '' : ' ') + nextText) : nextText;
                autoResize();
                saveDraft();
                resetComposerHint();
            };

            speechRecognizer.onerror = (event) => {
                const code = event && event.error ? String(event.error) : 'unknown';
                if (code === 'not-allowed' || code === 'service-not-allowed') {
                    setComposerHint('Microphone permission denied. Allow mic access and try again.', true);
                } else if (code === 'no-speech') {
                    setComposerHint('No speech detected. Try again.', true);
                } else {
                    setComposerHint('Voice input failed (' + code + ').', true);
                }
                updateVoiceButtonState(false);
            };

            speechRecognizer.onend = () => {
                updateVoiceButtonState(false);
            };
        }

        function toggleVoiceInput() {
            if (!speechRecognizer || isGenerating || !canUseVoice()) return;
            if (isListening) {
                speechRecognizer.stop();
                updateVoiceButtonState(false);
                resetComposerHint();
                return;
            }
            try {
                speechRecognizer.start();
                updateVoiceButtonState(true);
                setComposerHint('Listening... Speak now.', false);
            } catch (e) {
                updateVoiceButtonState(false);
                setComposerHint('Could not start voice input.', true);
            }
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
            refreshInputCapabilities();
            if (!canUseAttachments()) {
                clearAttachments();
            }
            if (!canUseVoice() && isListening && speechRecognizer) {
                speechRecognizer.stop();
            }
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
            setProvider(saved && PROVIDER_META[saved] ? saved : 'local_gemma');
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

        function addMessage(role, content, options = {}) {
            const autoScroll = !!options.autoScroll;
            removeEmptyState();
            const el = createMsg(role, content);
            messageList.appendChild(el);
            if (autoScroll) {
                ensureBottom();
            }
            updateJumpLatestVisibility();
            return el;
        }

        function showTyping(options = {}) {
            const autoScroll = !!options.autoScroll;
            removeEmptyState();
            const el = document.createElement('article');
            el.className = 'msg assistant';
            el.id = 'typingIndicator';
            el.innerHTML = '<div class="avatar" aria-hidden="true"><span class="material-symbols-rounded">smart_toy</span></div><div class="wrap"><div class="bubble"><div class="typing"><span></span><span></span><span></span></div></div></div>';
            messageList.appendChild(el);
            if (autoScroll) {
                ensureBottom();
            }
            updateJumpLatestVisibility();
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
            messageInput.style.height = Math.min(messageInput.scrollHeight, 160) + 'px';
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
            if (isGenerating) {
                stopGeneration();
                return;
            }

            const content = messageInput.value.trim();
            const meta = currentProviderMeta();
            const attachmentsPayload = canUseAttachments() ? buildAttachmentPayload() : [];
            if (!content && attachmentsPayload.length === 0) return;
            if (isListening && speechRecognizer) {
                speechRecognizer.stop();
            }

            const shouldStickToBottom = isNearBottom(180);
            addMessage('user', getUserPreviewContent(content, attachmentsPayload.length), {
                autoScroll: true
            });
            messageInput.value = '';
            autoResize();
            localStorage.removeItem(DRAFT_KEY);
            clearAttachments();

            const typing = showTyping({
                autoScroll: shouldStickToBottom
            });
            activeController = new AbortController();
            setComposerGeneratingState(true);

            try {
                const response = await fetch(basePath + 'api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    signal: activeController.signal,
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        message: content,
                        provider: meta.requestProvider,
                        local_model: meta.localModel,
                        attachments: attachmentsPayload
                    })
                });
                let data = null;
                const rawText = await response.text();
                try {
                    data = rawText ? JSON.parse(rawText) : null;
                } catch (e) {
                    data = null;
                }
                if (!response.ok) {
                    const detail = data && data.error ? data.error : ('HTTP ' + response.status);
                    throw new Error(detail);
                }
                if (typing && typing.parentNode) typing.remove();
                if (data.success) {
                    addMessage('assistant', data.message || '', {
                        autoScroll: shouldStickToBottom
                    });
                    if (attachmentsPayload.length > 0 && data.attachments_used !== true) {
                        setComposerHint('Images were attached but not used by the selected model.', true);
                    } else {
                        resetComposerHint();
                    }
                } else {
                    addMessage('assistant', 'Error: ' + (data.error || 'Unknown error'));
                    if (attachmentsPayload.length > 0) {
                        setComposerHint('Image request failed. Switch to a vision-capable model.', true);
                    }
                }
            } catch (error) {
                if (typing && typing.parentNode) typing.remove();
                if (error && error.name === 'AbortError') {
                    addMessage('assistant', 'Generation stopped.', {
                        autoScroll: shouldStickToBottom
                    });
                    resetComposerHint();
                } else {
                    const detail = error && error.message ? error.message : 'Connection issue. Please try again.';
                    addMessage('assistant', 'Error: ' + detail, {
                        autoScroll: shouldStickToBottom
                    });
                    setComposerHint(detail, true);
                    console.error(error);
                }
            } finally {
                activeController = null;
                setComposerGeneratingState(false);
                messageInput.focus();
            }
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
        if (attachButton) {
            attachButton.addEventListener('click', () => {
                if (attachmentInput && !isGenerating) {
                    attachmentInput.click();
                }
            });
        }
        if (attachmentInput) {
            attachmentInput.addEventListener('change', async (event) => {
                const files = event.target && event.target.files ? event.target.files : [];
                await addAttachmentFiles(files);
                attachmentInput.value = '';
            });
        }
        if (voiceButton) {
            voiceButton.addEventListener('click', toggleVoiceInput);
        }
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
                if (isListening && speechRecognizer) {
                    speechRecognizer.stop();
                }
                resetComposerHint();
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
        setComposerGeneratingState(false);
        renderAttachmentTray();
        resetComposerHint();
        initSpeechRecognition();
        restoreProvider();
        restoreDraft();
        restoreQuickPrompt();
        autoResize();
        ensureBottom();
        updateJumpLatestVisibility();
        document.querySelectorAll('.msg.assistant .bubble').forEach((bubble) => {
            bubble.innerHTML = renderAssistantHtml(bubble.textContent || '');
        });
    </script>
</body>

</html>
