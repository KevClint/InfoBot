<?php

/**
 * Render the shared app sidebar used by Chat/Settings pages.
 *
 * @param array $options
 *  - base_path (string)
 *  - conversations (array)
 *  - current_conversation_id (int)
 *  - current_page ('chat'|'settings')
 *  - user_role (string)
 *  - show_recent (bool)
 *  - show_new_chat (bool)
 */
function renderAppSidebar(array $options = []): void
{
    $basePath = isset($options['base_path']) ? (string)$options['base_path'] : '/';
    $conversations = isset($options['conversations']) && is_array($options['conversations']) ? $options['conversations'] : [];
    $currentConversationId = isset($options['current_conversation_id']) ? (int)$options['current_conversation_id'] : 0;
    $currentPage = isset($options['current_page']) ? (string)$options['current_page'] : 'chat';
    $userRole = isset($options['user_role']) ? (string)$options['user_role'] : 'user';
    $showRecent = array_key_exists('show_recent', $options) ? (bool)$options['show_recent'] : true;
    $showNewChat = array_key_exists('show_new_chat', $options) ? (bool)$options['show_new_chat'] : true;

    $settingsActive = $currentPage === 'settings' ? ' active' : '';
    $chatActive = $currentPage === 'chat' ? ' active' : '';

    $sidebarConversations = [];
    foreach ($conversations as $index => $conv) {
        $convId = (int)($conv['id'] ?? 0);
        if ($convId <= 0) {
            continue;
        }

        $isActive = $currentPage === 'chat'
            ? ($convId === $currentConversationId)
            : ($index === 0);

        $timeSource = $conv['updated_at'] ?? ($conv['created_at'] ?? null);
        $timestamp = $timeSource ? strtotime((string)$timeSource) : 0;
        if (!is_int($timestamp) || $timestamp < 0) {
            $timestamp = 0;
        }

        $sidebarConversations[] = [
            'id' => $convId,
            'title' => (string)($conv['title'] ?? 'Conversation'),
            'timestamp' => $timestamp,
            'time_label' => $timestamp > 0 ? date('M j, g:i A', $timestamp) : '',
            'active' => $isActive,
            'url' => $basePath . 'pages/chat.php?conversation_id=' . $convId
        ];
    }

    $jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    $sidebarConversationsJson = json_encode($sidebarConversations, $jsonFlags);
    if ($sidebarConversationsJson === false) {
        $sidebarConversationsJson = '[]';
    }

    $localModel = defined('LLM_MODEL') ? (string)LLM_MODEL : 'Unknown';
    $apiModel = defined('GROQ_MODEL') ? (string)GROQ_MODEL : 'Unknown';
    $hfModel = defined('HF_MODEL') ? (string)HF_MODEL : 'Unknown';
    $apiConfigured = defined('GROQ_API_KEY') && trim((string)GROQ_API_KEY) !== '' && defined('GROQ_API_URL') && trim((string)GROQ_API_URL) !== '';
    $hfConfigured = defined('HF_API_KEY') && trim((string)HF_API_KEY) !== '' && defined('HF_API_URL') && trim((string)HF_API_URL) !== '';
    ?>
    <style>
        .side-search-wrap {
            margin-top: 10px;
            position: relative;
            min-width: 0;
        }

        .side-search-wrap .material-symbols-rounded {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: calc(17px * var(--font-scale, 1));
            color: #94a3b8;
            pointer-events: none;
        }

        .side-search {
            width: 100%;
            border: 1px solid rgba(148, 163, 184, .32);
            background: rgba(15, 23, 42, .45);
            color: #e5e7eb;
            border-radius: 10px;
            padding: 9px 11px 9px 34px;
            font-family: inherit;
            font-size: calc(13px * var(--font-scale, 1));
            outline: none;
            min-width: 0;
        }

        .side-search::placeholder {
            color: #8ea0be;
        }

        .side-search:focus {
            border-color: color-mix(in srgb, var(--accent) 58%, transparent);
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--accent) 20%, transparent);
        }

        .conv-head-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-width: 0;
            margin-bottom: 8px;
        }

        .conv-head-row .conv-head {
            margin: 0;
            padding: 0;
            white-space: nowrap;
        }

        .conv-filter {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #9fb0ca;
            border: 1px solid rgba(148, 163, 184, .24);
            border-radius: 999px;
            background: rgba(15, 23, 42, .36);
            padding: 3px 7px;
            min-width: 0;
            flex: 0 0 auto;
        }

        .conv-filter .material-symbols-rounded {
            font-size: calc(15px * var(--font-scale, 1));
        }

        .conv-sort {
            border: 0;
            background: transparent;
            color: #b8c4da;
            font-family: inherit;
            font-size: calc(11px * var(--font-scale, 1));
            outline: none;
            min-width: 0;
            max-width: 120px;
            text-overflow: ellipsis;
        }

        .conv-groups {
            min-width: 0;
        }

        .conv-sec-title {
            padding: 2px 2px 7px;
            font-size: calc(10px * var(--font-scale, 1));
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #89a0c0;
        }

        .conv-group {
            margin-bottom: 10px;
        }

        .conv-item {
            position: relative;
        }

        .conv-item .conv {
            padding-right: 38px;
            margin-bottom: 8px;
        }

        .conv-pin {
            position: absolute;
            right: 8px;
            top: 8px;
            width: 24px;
            height: 24px;
            border: 1px solid transparent;
            background: transparent;
            color: #94a3b8;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
        }

        .conv-pin .material-symbols-rounded {
            font-size: calc(16px * var(--font-scale, 1));
        }

        .conv-pin:hover {
            color: #e2e8f0;
            background: rgba(30, 41, 59, .64);
        }

        .conv-pin.pinned {
            color: var(--accent);
            background: color-mix(in srgb, var(--accent) 14%, transparent);
            border-color: color-mix(in srgb, var(--accent) 38%, transparent);
        }

        .sb-empty-void {
            border: 1px dashed rgba(148, 163, 184, .32);
            border-radius: 12px;
            background: rgba(15, 23, 42, .24);
            color: #9fb0ca;
            min-height: 120px;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 6px;
            text-align: center;
            padding: 10px;
            margin: 10px 0 12px;
        }

        .sb-empty-void .material-symbols-rounded {
            font-size: calc(20px * var(--font-scale, 1));
            color: #8398b8;
            opacity: .8;
        }

        .sb-empty-title {
            font-size: calc(12px * var(--font-scale, 1));
            color: #b8c6dd;
        }

        .sb-empty-sub {
            font-size: calc(11px * var(--font-scale, 1));
            color: #8ca0bf;
        }

        .side-tools {
            border-top: 1px solid rgba(148, 163, 184, .2);
            margin-top: 8px;
            padding-top: 10px;
        }

        .side-primary {
            display: none;
            padding: 10px;
            border-bottom: 1px solid rgba(148, 163, 184, .18);
        }

        body.sidebar-collapsed .side-primary {
            display: block;
        }

        .side-link-chat-top {
            display: none;
        }

        body.sidebar-collapsed .side-link-chat-top {
            display: flex;
        }

        body.sidebar-collapsed .side-link-chat-bottom {
            display: none;
        }

        .sidebar .side-foot {
            margin-top: auto;
        }

        .side-tools-head {
            padding: 2px 2px 8px;
            font-size: calc(10px * var(--font-scale, 1));
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #89a0c0;
        }

        .side-tools-grid {
            display: grid;
            gap: 8px;
        }

        .side-tool {
            width: 100%;
            min-width: 0;
            border: 1px solid rgba(148, 163, 184, .28);
            background: rgba(15, 23, 42, .34);
            color: #d6e0f0;
            border-radius: 10px;
            padding: 8px 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-family: inherit;
            font-size: calc(12px * var(--font-scale, 1));
            text-align: left;
            overflow: hidden;
        }

        .side-tool .material-symbols-rounded {
            font-size: calc(16px * var(--font-scale, 1));
            flex: 0 0 auto;
        }

        .side-tool span:last-child {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .side-tool:hover {
            background: rgba(30, 41, 59, .72);
            border-color: rgba(148, 163, 184, .42);
        }

        .side-status {
            border: 1px solid rgba(148, 163, 184, .28);
            border-radius: 10px;
            background: rgba(15, 23, 42, .34);
            padding: 8px 10px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 4px 8px;
            align-items: center;
            min-width: 0;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #94a3b8;
            box-shadow: 0 0 0 2px rgba(148, 163, 184, .2);
        }

        .status-dot.pending {
            background: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, .22);
        }

        .status-dot.online {
            background: #22c55e;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, .22);
        }

        .status-dot.offline {
            background: #ef4444;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, .22);
        }

        .status-text,
        .status-model {
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .status-text {
            color: #d2dcec;
            font-size: calc(12px * var(--font-scale, 1));
            font-weight: 600;
        }

        .status-model {
            grid-column: 2;
            color: #95a8c6;
            font-size: calc(11px * var(--font-scale, 1));
        }

        body.sidebar-collapsed .side-search-wrap,
        body.sidebar-collapsed .side-status .status-text,
        body.sidebar-collapsed .side-status .status-model {
            display: none;
        }

        body.sidebar-collapsed .side-status {
            display: flex;
            justify-content: center;
            padding: 8px;
            margin-bottom: 8px;
        }
    </style>

    <aside class="sidebar" id="sidebar"
           data-base-path="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>"
           data-local-model="<?php echo htmlspecialchars($localModel, ENT_QUOTES); ?>"
           data-api-model="<?php echo htmlspecialchars($apiModel, ENT_QUOTES); ?>"
           data-hf-model="<?php echo htmlspecialchars($hfModel, ENT_QUOTES); ?>"
           data-local-llama-model="<?php echo htmlspecialchars(defined('LLM_MODEL_LLAMA') ? (string)LLM_MODEL_LLAMA : 'llama3.2:3b', ENT_QUOTES); ?>"
           data-local-gemma-model="<?php echo htmlspecialchars(defined('LLM_MODEL_GEMMA') ? (string)LLM_MODEL_GEMMA : 'gemma3:4b', ENT_QUOTES); ?>"
           data-api-configured="<?php echo $apiConfigured ? 'true' : 'false'; ?>"
           data-hf-configured="<?php echo $hfConfigured ? 'true' : 'false'; ?>">
        <div class="side-top">
            <div class="brand">
                <div class="brand-actions">
                    <button class="brand-collapse" id="sidebarCollapseBtn" type="button" aria-label="Collapse sidebar">
                        <span class="material-symbols-rounded" id="sidebarCollapseIcon">left_panel_close</span>
                    </button>
                    <a class="brand-github" href="https://github.com/KevClint" target="_blank" rel="noopener noreferrer" aria-label="Open GitHub profile">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 .5a12 12 0 0 0-3.79 23.39c.6.11.82-.26.82-.58l-.02-2.03c-3.34.73-4.04-1.41-4.04-1.41-.55-1.36-1.33-1.72-1.33-1.72-1.09-.73.09-.72.09-.72 1.2.08 1.83 1.22 1.83 1.22 1.08 1.82 2.83 1.29 3.52.98.11-.77.42-1.29.76-1.59-2.67-.3-5.47-1.31-5.47-5.86 0-1.3.47-2.36 1.23-3.19-.12-.3-.53-1.52.12-3.17 0 0 1-.32 3.3 1.22a11.55 11.55 0 0 1 6 0c2.3-1.54 3.3-1.22 3.3-1.22.65 1.65.24 2.87.12 3.17.77.83 1.23 1.89 1.23 3.19 0 4.56-2.8 5.55-5.48 5.85.43.37.81 1.09.81 2.21l-.01 3.28c0 .32.22.7.82.58A12 12 0 0 0 12 .5Z" />
                        </svg>
                    </a>
                </div>
            </div>
            <?php if ($showNewChat): ?>
                <a class="ghost" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/chat.php?new=true"><span class="material-symbols-rounded">add</span>New Chat</a>
            <?php endif; ?>
            <?php if ($showRecent): ?>
                <div class="side-search-wrap">
                    <span class="material-symbols-rounded">search</span>
                    <input class="side-search" id="sideSearchInput" type="text" placeholder="Search chats..." autocomplete="off" />
                </div>
            <?php endif; ?>
        </div>

        <div class="side-primary">
            <a class="side-link side-link-chat-top<?php echo $chatActive; ?>" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/chat.php"><span class="material-symbols-rounded">chat</span><span>Chat</span></a>
        </div>

        <?php if ($showRecent): ?>
            <div class="conv-list">
                <div class="conv-head-row">
                    <div class="conv-head">Recent Chats</div>
                    <label class="conv-filter" for="convSortSelect" aria-label="Sort or filter conversations">
                        <span class="material-symbols-rounded">filter_alt</span>
                        <select class="conv-sort" id="convSortSelect">
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                            <option value="title">Title A-Z</option>
                            <option value="pinned">Pinned</option>
                        </select>
                    </label>
                </div>

                <div class="conv-groups" id="convGroups"></div>

                <div class="sb-empty-void" id="convVoid">
                    <span class="material-symbols-rounded">chat_bubble</span>
                    <div class="sb-empty-title">Your conversation history will appear here.</div>
                    <div class="sb-empty-sub">Use New Chat to start one.</div>
                </div>

                <section class="side-tools" aria-label="Quick tools">
                    <div class="side-tools-head">Quick Tools</div>
                    <div class="side-tools-grid">
                        <button class="side-tool" type="button" data-tool-prompt="Summarize this document into key points and action items with owners.">
                            <span class="material-symbols-rounded">summarize</span>
                            <span>Summarize Document</span>
                        </button>
                        <button class="side-tool" type="button" data-tool-prompt="Act as a code helper and debug this issue step-by-step with a safe fix plan.">
                            <span class="material-symbols-rounded">code</span>
                            <span>Code Helper</span>
                        </button>
                        <button class="side-tool" type="button" data-tool-prompt="Analyze this output and explain what failed, why, and what to run next.">
                            <span class="material-symbols-rounded">analytics</span>
                            <span>Analyze Output</span>
                        </button>
                    </div>
                </section>
            </div>
        <?php endif; ?>

        <div class="side-foot">
            <div class="side-status" id="sideStatus">
                <span class="status-dot pending" id="sideStatusDot"></span>
                <span class="status-text" id="sideStatusText">Ollama: Checking...</span>
                <span class="status-model" id="sideStatusModel">Model: <?php echo htmlspecialchars($localModel, ENT_QUOTES); ?></span>
            </div>
            <?php if ($userRole === 'admin'): ?>
                <a class="side-link" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/admin/index.php"><span class="material-symbols-rounded">admin_panel_settings</span><span>Admin</span></a>
            <?php endif; ?>
            <a class="side-link side-link-chat-bottom<?php echo $chatActive; ?>" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/chat.php"><span class="material-symbols-rounded">chat</span><span>Chat</span></a>
            <a class="side-link<?php echo $settingsActive; ?>" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/settings.php"><span class="material-symbols-rounded">settings</span><span>Settings</span></a>
            <a class="side-link" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/logout.php"><span class="material-symbols-rounded">logout</span><span>Logout</span></a>
        </div>

        <script id="sidebarConversationsData" type="application/json"><?php echo $sidebarConversationsJson; ?></script>
    </aside>

    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            const convDataEl = document.getElementById('sidebarConversationsData');
            const convGroups = document.getElementById('convGroups');
            const convVoid = document.getElementById('convVoid');
            const searchInput = document.getElementById('sideSearchInput');
            const sortSelect = document.getElementById('convSortSelect');
            const statusDot = document.getElementById('sideStatusDot');
            const statusText = document.getElementById('sideStatusText');
            const statusModel = document.getElementById('sideStatusModel');
            const toolButtons = sidebar.querySelectorAll('.side-tool');
            const pinnedKey = 'infobot_pinned_conversations_v1';
            const quickPromptKey = 'infobot_quick_prompt';
            const providerKey = 'infobot_provider';

            let conversations = [];
            try {
                conversations = convDataEl ? JSON.parse(convDataEl.textContent || '[]') : [];
            } catch (e) {
                conversations = [];
            }

            function escapeHtml(value) {
                return String(value || '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function readPinnedIds() {
                try {
                    const raw = localStorage.getItem(pinnedKey);
                    const parsed = raw ? JSON.parse(raw) : [];
                    if (!Array.isArray(parsed)) return new Set();
                    return new Set(parsed.map((id) => Number(id)).filter((id) => Number.isFinite(id) && id > 0));
                } catch (e) {
                    return new Set();
                }
            }

            function writePinnedIds(set) {
                localStorage.setItem(pinnedKey, JSON.stringify(Array.from(set)));
            }

            let pinnedIds = readPinnedIds();

            function createConversationItem(conversation, isPinned) {
                const wrapper = document.createElement('div');
                wrapper.className = 'conv-item';

                const link = document.createElement('a');
                link.className = 'conv' + (conversation.active ? ' active' : '');
                link.href = conversation.url;
                link.innerHTML =
                    '<div class="conv-title">' + escapeHtml(conversation.title || 'Conversation') + '</div>' +
                    (conversation.time_label ? '<div class="conv-time">' + escapeHtml(conversation.time_label) + '</div>' : '');

                const pinBtn = document.createElement('button');
                pinBtn.className = 'conv-pin' + (isPinned ? ' pinned' : '');
                pinBtn.type = 'button';
                pinBtn.setAttribute('aria-label', isPinned ? 'Unpin conversation' : 'Pin conversation');
                pinBtn.innerHTML = '<span class="material-symbols-rounded">' + (isPinned ? 'keep_off' : 'keep') + '</span>';
                pinBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const id = Number(conversation.id);
                    if (!Number.isFinite(id) || id <= 0) return;
                    if (pinnedIds.has(id)) pinnedIds.delete(id);
                    else pinnedIds.add(id);
                    writePinnedIds(pinnedIds);
                    renderConversationGroups();
                });

                wrapper.appendChild(link);
                wrapper.appendChild(pinBtn);
                return wrapper;
            }

            function createSection(title, list, pinnedSection) {
                if (!Array.isArray(list) || list.length === 0) return null;

                const section = document.createElement('section');
                section.className = 'conv-group';

                const header = document.createElement('div');
                header.className = 'conv-sec-title';
                header.textContent = title;
                section.appendChild(header);

                list.forEach((conversation) => {
                    section.appendChild(createConversationItem(conversation, pinnedSection));
                });

                return section;
            }

            function getBucketLabel(timestamp) {
                if (!timestamp || timestamp <= 0) return 'Older';

                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                const itemDate = new Date(timestamp * 1000);
                const itemDay = new Date(itemDate.getFullYear(), itemDate.getMonth(), itemDate.getDate());
                const diffDays = Math.floor((today.getTime() - itemDay.getTime()) / 86400000);

                if (diffDays <= 0) return 'Today';
                if (diffDays === 1) return 'Yesterday';
                if (diffDays <= 7) return 'Previous 7 Days';
                return 'Older';
            }

            function sortConversations(list, mode) {
                const cloned = list.slice();
                if (mode === 'oldest') {
                    cloned.sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));
                    return cloned;
                }
                if (mode === 'title') {
                    cloned.sort((a, b) => String(a.title || '').localeCompare(String(b.title || '')));
                    return cloned;
                }
                cloned.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));
                return cloned;
            }

            function renderConversationGroups() {
                if (!convGroups || !convVoid) return;

                const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : '';
                const mode = sortSelect ? sortSelect.value : 'newest';
                const baseList = sortConversations(conversations, mode);

                let filtered = baseList.filter((item) => {
                    if (!searchTerm) return true;
                    return String(item.title || '').toLowerCase().includes(searchTerm);
                });

                if (mode === 'pinned') {
                    filtered = filtered.filter((item) => pinnedIds.has(Number(item.id)));
                }

                convGroups.innerHTML = '';

                const pinnedList = filtered.filter((item) => pinnedIds.has(Number(item.id)));
                const regularList = filtered.filter((item) => !pinnedIds.has(Number(item.id)));

                const pinnedSection = createSection('Pinned', pinnedList, true);
                if (pinnedSection) convGroups.appendChild(pinnedSection);

                if (mode !== 'pinned') {
                    const buckets = {
                        'Today': [],
                        'Yesterday': [],
                        'Previous 7 Days': [],
                        'Older': []
                    };

                    regularList.forEach((item) => {
                        const key = getBucketLabel(Number(item.timestamp || 0));
                        if (!buckets[key]) buckets[key] = [];
                        buckets[key].push(item);
                    });

                    ['Today', 'Yesterday', 'Previous 7 Days', 'Older'].forEach((bucketName) => {
                        const section = createSection(bucketName, buckets[bucketName], false);
                        if (section) convGroups.appendChild(section);
                    });
                }

                const visibleCount = filtered.length;
                const isSparse = visibleCount <= 1 && searchTerm === '' && mode !== 'pinned';

                if (visibleCount === 0) {
                    convVoid.style.display = 'flex';
                    convVoid.querySelector('.material-symbols-rounded').textContent = searchTerm ? 'search_off' : 'chat_bubble';
                    convVoid.querySelector('.sb-empty-title').textContent = searchTerm ? 'No chats match your search.' : 'Your conversation history will appear here.';
                    convVoid.querySelector('.sb-empty-sub').textContent = searchTerm ? 'Try another keyword or clear filters.' : 'Use New Chat to start one.';
                } else if (isSparse) {
                    convVoid.style.display = 'flex';
                    convVoid.querySelector('.material-symbols-rounded').textContent = 'chat_bubble';
                    convVoid.querySelector('.sb-empty-title').textContent = 'Your conversation history will appear here.';
                    convVoid.querySelector('.sb-empty-sub').textContent = 'Keep chatting and this area will fill up.';
                } else {
                    convVoid.style.display = 'none';
                }
            }

            function initTools() {
                if (!toolButtons || toolButtons.length === 0) return;
                const basePath = sidebar.getAttribute('data-base-path') || '/';

                toolButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        const prompt = button.getAttribute('data-tool-prompt') || '';
                        if (prompt) {
                            localStorage.setItem(quickPromptKey, prompt);
                        }
                        window.location.href = basePath + 'pages/chat.php?new=true';
                    });
                });
            }

            function setStatusWidget(state, text, modelLabel) {
                if (!statusDot || !statusText || !statusModel) return;

                statusDot.classList.remove('pending', 'online', 'offline');
                statusDot.classList.add(state);
                statusText.textContent = text;
                statusModel.textContent = 'Model: ' + modelLabel;
            }

            function getSelectedProvider() {
                const saved = localStorage.getItem(providerKey);
                if (saved === 'local_llama' || saved === 'local_gemma' || saved === 'api' || saved === 'hf') {
                    return saved;
                }
                if (saved === 'local') {
                    return 'local_gemma';
                }
                return 'api';
            }

            function updateStatusWidget() {
                if (!statusDot || !statusText || !statusModel) return;

                const selectedProvider = getSelectedProvider();
                const basePath = sidebar.getAttribute('data-base-path') || '/';
                const localModel = sidebar.getAttribute('data-local-model') || 'Unknown';
                const localLlamaModel = sidebar.getAttribute('data-local-llama-model') || 'llama3.2:3b';
                const localGemmaModel = sidebar.getAttribute('data-local-gemma-model') || 'gemma3:4b';
                const apiModel = sidebar.getAttribute('data-api-model') || 'Unknown';
                const hfModel = sidebar.getAttribute('data-hf-model') || 'Unknown';
                const apiConfigured = sidebar.getAttribute('data-api-configured') === 'true';
                const hfConfigured = sidebar.getAttribute('data-hf-configured') === 'true';

                if (selectedProvider === 'api') {
                    if (!apiConfigured) {
                        setStatusWidget('offline', 'Groq: Not Configured', apiModel);
                        return;
                    }
                    setStatusWidget('pending', 'Groq: Checking...', apiModel);
                    fetch(basePath + 'api/provider_status.php?provider=api', { cache: 'no-store' })
                        .then((response) => response.json())
                        .then((data) => {
                            const online = !!(data && data.success && data.configured && data.online);
                            setStatusWidget(online ? 'online' : 'offline', online ? 'Groq: Running' : 'Groq: Not Running', apiModel);
                        })
                        .catch(() => {
                            setStatusWidget('offline', 'Groq: Not Running', apiModel);
                        });
                    return;
                }
                if (selectedProvider === 'hf') {
                    if (!hfConfigured) {
                        setStatusWidget('offline', 'HF: Not Configured', hfModel);
                        return;
                    }
                    setStatusWidget('pending', 'HF: Checking...', hfModel);
                    fetch(basePath + 'api/provider_status.php?provider=hf', { cache: 'no-store' })
                        .then((response) => response.json())
                        .then((data) => {
                            const online = !!(data && data.success && data.configured && data.online);
                            setStatusWidget(online ? 'online' : 'offline', online ? 'HF: Running' : 'HF: Not Running', hfModel);
                        })
                        .catch(() => {
                            setStatusWidget('offline', 'HF: Not Running', hfModel);
                        });
                    return;
                }

                const chosenLocalModel = selectedProvider === 'local_llama' ? localLlamaModel : (selectedProvider === 'local_gemma' ? localGemmaModel : localModel);

                setStatusWidget('pending', 'Ollama: Checking...', chosenLocalModel);
                fetch(basePath + 'api/local_status.php', { cache: 'no-store' })
                    .then((response) => response.json())
                    .then((data) => {
                        const online = !!(data && data.success && data.online);
                        setStatusWidget(online ? 'online' : 'offline', online ? 'Ollama: Running' : 'Ollama: Not Running', chosenLocalModel);
                    })
                    .catch(() => {
                        setStatusWidget('offline', 'Ollama: Not Running', chosenLocalModel);
                    });
            }

            if (searchInput) {
                searchInput.addEventListener('input', renderConversationGroups);
            }

            if (sortSelect) {
                sortSelect.addEventListener('change', renderConversationGroups);
            }

            window.addEventListener('storage', function (event) {
                if (event && event.key === providerKey) {
                    updateStatusWidget();
                }
            });

            window.addEventListener('infobot:provider-changed', function () {
                updateStatusWidget();
            });

            initTools();
            renderConversationGroups();
            updateStatusWidget();
        })();
    </script>
    <?php
}
