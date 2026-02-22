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
    ?>
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
            <?php if ($showNewChat): ?>
                <a class="ghost" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/chat.php?new=true"><span class="material-symbols-rounded">add</span>New Chat</a>
            <?php endif; ?>
        </div>

        <?php if ($showRecent): ?>
            <div class="conv-list">
                <div class="conv-head">Recent Chats</div>
                <?php if (empty($conversations)): ?>
                    <div class="conv active" style="cursor:default;">
                        <div class="conv-title">No recent chats yet</div>
                        <div class="conv-time">Start one with New Chat</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $index => $conv): ?>
                        <?php
                        $convId = (int)($conv['id'] ?? 0);
                        $isActive = $currentPage === 'chat'
                            ? ($convId === $currentConversationId)
                            : ($index === 0);
                        $timeSource = $conv['updated_at'] ?? ($conv['created_at'] ?? null);
                        ?>
                        <a class="conv<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/chat.php?conversation_id=<?php echo $convId; ?>">
                            <div class="conv-title"><?php echo htmlspecialchars((string)($conv['title'] ?? 'Conversation')); ?></div>
                            <?php if ($timeSource): ?>
                                <div class="conv-time"><?php echo date('M j, g:i A', strtotime((string)$timeSource)); ?></div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="side-foot">
            <?php if ($userRole === 'admin'): ?>
                <a class="side-link" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/admin/index.php"><span class="material-symbols-rounded">admin_panel_settings</span><span>Admin</span></a>
            <?php endif; ?>
            <a class="side-link<?php echo $chatActive; ?>" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/chat.php"><span class="material-symbols-rounded">chat</span><span>Chat</span></a>
            <a class="side-link<?php echo $settingsActive; ?>" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/settings.php"><span class="material-symbols-rounded">settings</span><span>Settings</span></a>
            <a class="side-link" href="<?php echo htmlspecialchars($basePath, ENT_QUOTES); ?>pages/logout.php"><span class="material-symbols-rounded">logout</span><span>Logout</span></a>
        </div>
    </aside>
    <?php
}

