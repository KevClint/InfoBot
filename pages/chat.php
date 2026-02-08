<?php
/**
 * CHAT PAGE
 * 
 * Main chat interface where users interact with the AI chatbot.
 * Displays conversation history and allows sending new messages.
 * Features: favorites, search, dark mode, theme colors, font size.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/chatbot.php';
require_once __DIR__ . '/../includes/preferences.php';

// Require user to be logged in
requireLogin();

$user_id = getCurrentUserId();
$username = getCurrentUsername();
$user_role = getCurrentUserRole();
$prefs = getUserPreferences($user_id);

// Get user's conversations
$conversations = getUserConversations($user_id);

// Get current conversation ID (from URL or use most recent)
$current_conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : null;
$create_new = isset($_GET['new']) && $_GET['new'] === 'true';

// If no conversation specified, use most recent or create new
if (!$current_conversation_id) {
    if ($create_new || empty($conversations)) {
        // Create a new conversation
        $current_conversation_id = createConversation($user_id);
        header('Location: ' . BASE_PATH . 'pages/chat.php?conversation_id=' . $current_conversation_id);
        exit();
    } else {
        // User has existing conversations, redirect to most recent
        $current_conversation_id = $conversations[0]['id'];
        header('Location: ' . BASE_PATH . 'pages/chat.php?conversation_id=' . $current_conversation_id);
        exit();
    }
}

// Get messages for current conversation
$messages = getConversationMessages($current_conversation_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoBot - Chat</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
    <link rel="icon" href="<?php echo BASE_PATH; ?>assets/icons/logo-robot-64px.jpg">
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
                    <a href="<?php echo BASE_PATH; ?>pages/chat.php" class="nav-link active">
                        <span class="material-symbols-outlined">chat</span>
                        <span>Chat</span>
                    </a>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="<?php echo BASE_PATH; ?>pages/admin/index.php" class="nav-link">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                            <span>Admin</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_PATH; ?>pages/settings.php" class="nav-link">
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

    <!-- Chat Container -->
    <div class="chat-container">
        <!-- Sidebar -->
        <aside class="chat-sidebar">
            <div class="sidebar-header">
                <button class="btn btn-primary" style="width: 100%; padding: 16px 24px; font-size: 16px; font-weight: 600;" onclick="newConversation()">
                    <span class="material-symbols-outlined">add</span>
                    New
                </button>
            </div>
            <div class="sidebar-content">
                <?php if (empty($conversations)): ?>
                    <p class="text-muted" style="font-size: 14px; padding: 12px;">No conversations yet. Start a new chat!</p>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <button class="conversation-item <?php echo $conv['id'] == $current_conversation_id ? 'active' : ''; ?>"
                                onclick="loadConversation(<?php echo $conv['id']; ?>)"
                                aria-label="Open conversation: <?php echo htmlspecialchars($conv['title']); ?>"
                                aria-current="<?php echo $conv['id'] == $current_conversation_id ? 'page' : 'false'; ?>">
                            <div>
                                <div class="conversation-title"><?php echo htmlspecialchars($conv['title']); ?></div>
                                <div class="conversation-date"><?php echo date('M j, g:i A', strtotime($conv['updated_at'])); ?></div>
                            </div>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Chat Area -->
        <main class="chat-main">
            <div class="chat-header">
                <h2 class="chat-title">AI Assistant</h2>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="searchInput" class="chat-search form-input" 
                           placeholder="Search messages... (Ctrl+K)"
                           aria-label="Search messages in current conversation"
                           style="padding: 8px 12px; width: 200px;">
                    <button class="btn btn-icon btn-secondary" onclick="clearChat()" 
                            title="Delete this conversation" 
                            aria-label="Delete this conversation">
                        <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                    </button>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <span class="material-symbols-outlined" style="font-size: 56px; margin-bottom: 12px;">chat_bubble_outline</span>
                        <h3>Start a conversation</h3>
                        <p>Ask the AI assistant anything to get started</p>
                        <div class="suggested-prompts">
                            <button class="suggested-prompt" onclick="insertPrompt('What can you help me with?')" 
                                    aria-label="Use suggested prompt: What can you help me with?">
                                <span class="material-symbols-outlined">lightbulb</span>
                                <span>What can you help me with?</span>
                            </button>
                            <button class="suggested-prompt" onclick="insertPrompt('Tell me something interesting')" 
                                    aria-label="Use suggested prompt: Tell me something interesting">
                                <span class="material-symbols-outlined">explore</span>
                                <span>Tell me something interesting</span>
                            </button>
                            <button class="suggested-prompt" onclick="insertPrompt('How do I get started?')" 
                                    aria-label="Use suggested prompt: How do I get started?">
                                <span class="material-symbols-outlined">play_circle</span>
                                <span>How do I get started?</span>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['role']; ?>" data-message-id="<?php echo isset($msg['id']) ? $msg['id'] : ''; ?>"
                             role="article" aria-label="<?php echo $msg['role'] === 'user' ? 'Your message' : 'Assistant message'; ?>">
                            <div class="message-avatar" aria-hidden="true">
                                <span class="material-symbols-outlined">
                                    <?php echo $msg['role'] === 'user' ? 'person' : 'smart_toy'; ?>
                                </span>
                            </div>
                            <div>
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
                                </div>
                                <div class="message-time" style="display: flex; align-items: center; gap: 4px;">
                                    <time datetime="<?php echo $msg['created_at']; ?>">
                                        <?php echo date('g:i A', strtotime($msg['created_at'])); ?>
                                    </time>
                                    <?php if ($msg['role'] === 'assistant'): ?>
                                        <button class="message-action-btn copy-btn" 
                                                onclick="copyMessage(this)" 
                                                title="Copy message"
                                                aria-label="Copy message to clipboard">
                                            <span class="material-symbols-outlined" aria-hidden="true">content_copy</span>
                                        </button>
                                        <button class="message-action-btn favorite-btn" 
                                                onclick="toggleFavorite(this)" 
                                                title="Add to favorites"
                                                aria-label="Add this message to favorites">
                                            <span class="material-symbols-outlined" style="font-size: 18px;" aria-hidden="true">favorite</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="chat-input-container">
                <form class="chat-input-wrapper" onsubmit="sendMessage(event)">
                    <textarea 
                        id="messageInput" 
                        class="chat-input" 
                        placeholder="Type your message here... (Shift+Enter for new line)"
                        rows="1"
                        required
                        aria-label="Message input"
                        aria-describedby="inputHint"
                    ></textarea>
                    <button type="submit" class="send-button" id="sendButton" 
                            aria-label="Send message"
                            title="Send (Enter to send, Shift+Enter for new line)">
                        <span class="material-symbols-outlined" aria-hidden="true">send</span>
                    </button>
                </form>
                <div class="input-hint" id="inputHint" style="font-size: 12px; color: var(--text-light); padding: 4px 12px; text-align: right;">
                    Press Enter to send • Shift+Enter for new line
                </div>
            </div>
        </main>
    </div>

    <script>
        const basePath = '<?php echo BASE_PATH; ?>';
        const conversationId = <?php echo $current_conversation_id; ?>;
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const searchInput = document.getElementById('searchInput');

        // ===== DRAFT SAVING & RESTORATION =====
        const DRAFT_KEY = `infobot_draft_${conversationId}`;

        function saveDraft() {
            const draft = messageInput.value;
            if (draft.trim()) {
                localStorage.setItem(DRAFT_KEY, draft);
            } else {
                localStorage.removeItem(DRAFT_KEY);
            }
        }

        function restoreDraft() {
            const draft = localStorage.getItem(DRAFT_KEY);
            if (draft) {
                messageInput.value = draft;
                messageInput.style.height = 'auto';
                messageInput.style.height = Math.min(messageInput.scrollHeight, 120) + 'px';
            }
        }

        // Save draft before unloading
        window.addEventListener('beforeunload', saveDraft);

        // ===== KEYBOARD SHORTCUTS =====
        document.addEventListener('keydown', function(e) {
            // Ctrl+K or Cmd+K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            // ? to show help (future enhancement)
            if (e.key === '?' && !e.ctrlKey && !e.metaKey && document.activeElement !== messageInput) {
                // Could show help modal
            }
        });

        // ===== INITIALIZE THEME ON PAGE LOAD =====
        function initializeTheme() {
            const darkMode = localStorage.getItem('darkMode') === 'true';
            const fontSize = localStorage.getItem('fontSize') || 'medium';
            const themeColor = localStorage.getItem('themeColor') || 'blue';
            applyTheme(darkMode, fontSize, themeColor);
        }

        // Apply theme
        function applyTheme(darkMode, fontSize, themeColor) {
            const root = document.documentElement;
            
            if (darkMode) {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }

            root.style.setProperty('--font-size-multiplier', 
                fontSize === 'small' ? '0.9' : fontSize === 'large' ? '1.1' : '1');

            const colorMap = {
                'blue': '#3b82f6',
                'green': '#10b981',
                'purple': '#8b5cf6',
                'orange': '#f97316',
                'cyan': '#06b6d4'
            };
            root.style.setProperty('--color-primary', colorMap[themeColor]);
        }

        // ===== WELCOME MODAL =====
        function showWelcomeModal() {
            const hasVisited = sessionStorage.getItem('visited');
            if (!hasVisited) {
                const modalHTML = `
                    <div id="welcomeModal" class="modal-overlay" onclick="closeWelcomeModal(event)">
                        <div class="modal" style="display: block; margin: auto;" onclick="event.stopPropagation()">
                            <div class="modal-header">
                                <h2>Welcome to InfoBot!</h2>
                                <button class="close-button" onclick="closeWelcomeModal()" aria-label="Close welcome modal">×</button>
                            </div>
                            <div class="modal-body" style="text-align: center;">
                                <div style="font-size: 48px; margin-bottom: 16px;">
                                    <span class="material-symbols-outlined" style="font-size: 48px;">smart_toy</span>
                                </div>
                                <h3 style="margin-bottom: 12px;">Hello! How can I help you today?</h3>
                                <p style="color: var(--text-secondary); margin-bottom: 20px;">
                                    I'm your AI assistant. You can ask me questions, get information, or have a conversation. 
                                    Your conversation drafts are automatically saved!
                                </p>
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <button class="btn btn-primary" onclick="closeWelcomeModal()">Get Started</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                sessionStorage.setItem('visited', 'true');
            }
        }

        function closeWelcomeModal(event) {
            if (event && event.target.id !== 'welcomeModal') return;
            const modal = document.getElementById('welcomeModal');
            if (modal) modal.remove();
        }

        // ===== COPY MESSAGE FUNCTIONALITY =====
        function copyMessage(btn) {
            const messageEl = btn.closest('.message');
            const contentEl = messageEl.querySelector('.message-content');
            const text = contentEl.textContent;

            navigator.clipboard.writeText(text).then(() => {
                // Show feedback
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">check</span>';
                btn.style.color = 'var(--success-color)';
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.style.color = 'inherit';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Failed to copy message');
            });
        }

        // ===== INSERT PROMPT FROM SUGGESTION =====
        function insertPrompt(text) {
            messageInput.value = text;
            messageInput.style.height = 'auto';
            messageInput.style.height = Math.min(messageInput.scrollHeight, 120) + 'px';
            messageInput.focus();
            saveDraft();
        }

        // ===== FAVORITE TOGGLE =====
        function toggleFavorite(btn) {
            const messageEl = btn.closest('.message');
            const messageId = messageEl.dataset.messageId;
            
            fetch(basePath + 'api/toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message_id: messageId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.classList.toggle('favorited');
                    const icon = btn.querySelector('.material-symbols-outlined');
                    if (data.action === 'added') {
                        btn.style.color = 'var(--danger-color)';
                        icon.style.fontVariationSettings = "'FILL' 1";
                    } else {
                        btn.style.color = 'inherit';
                        icon.style.fontVariationSettings = "'FILL' 0";
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // ===== SEARCH MESSAGES =====
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const messages = chatMessages.querySelectorAll('.message');
            let visibleCount = 0;
            
            messages.forEach(msg => {
                const content = msg.textContent.toLowerCase();
                if (query === '' || content.includes(query)) {
                    msg.style.display = 'flex';
                    visibleCount++;
                } else {
                    msg.style.display = 'none';
                }
            });
            
            // Show no results message
            const noResults = chatMessages.querySelector('.no-search-results');
            if (visibleCount === 0 && query) {
                if (!noResults) {
                    const message = document.createElement('div');
                    message.className = 'no-search-results';
                    message.textContent = `No messages found matching "${query}"`;
                    chatMessages.appendChild(message);
                }
            } else if (noResults) {
                noResults.remove();
            }
        });

        // ===== FIX LOGO LINK =====
        const logoLink = document.querySelector('.logo');
        if (logoLink) {
            logoLink.href = basePath + 'pages/chat.php?conversation_id=' + conversationId;
        }

        // ===== AUTO-RESIZE TEXTAREA =====
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            saveDraft();
        });

        // ===== HANDLE ENTER KEY =====
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(e);
            }
        });

        // ===== SEND MESSAGE FUNCTION =====
        async function sendMessage(event) {
            event.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;

            // Disable input while sending
            messageInput.disabled = true;
            sendButton.disabled = true;

            // Add user message to chat
            addMessage('user', message);
            messageInput.value = '';
            messageInput.style.height = 'auto';
            localStorage.removeItem(DRAFT_KEY); // Clear draft after sending

            // Show typing indicator
            const typingDiv = showTypingIndicator();

            try {
                const response = await fetch(basePath + 'api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        message: message
                    })
                });

                const data = await response.json();
                typingDiv.remove();

                if (data.success) {
                    addMessage('bot', data.message);
                } else {
                    addMessage('bot', 'Sorry, I encountered an error: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                typingDiv.remove();
                addMessage('bot', 'Sorry, I couldn\'t connect to the server. Please try again.');
                console.error('Error:', error);
            }

            messageInput.disabled = false;
            sendButton.disabled = false;
            messageInput.focus();
        }

        // ===== ADD MESSAGE TO DISPLAY =====
        function addMessage(role, content) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message ' + role;
            messageDiv.setAttribute('role', 'article');
            messageDiv.setAttribute('aria-label', role === 'user' ? 'Your message' : 'Assistant message');
            
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            
            let actionButtons = '';
            if (role === 'bot') {
                actionButtons = `
                    <button class="message-action-btn copy-btn" 
                            onclick="copyMessage(this)" 
                            title="Copy message"
                            aria-label="Copy message to clipboard" 
                            style="background: none; border: none; cursor: pointer; padding: 0 4px; color: inherit; font-size: 16px;">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size: 18px;">content_copy</span>
                    </button>
                    <button class="message-action-btn favorite-btn" 
                            onclick="toggleFavorite(this)" 
                            title="Add to favorites" 
                            aria-label="Add this message to favorites"
                            style="background: none; border: none; cursor: pointer; padding: 0 4px; color: inherit; font-size: 16px;">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size: 18px;">favorite</span>
                    </button>
                `;
            }
            
            messageDiv.innerHTML = `
                <div class="message-avatar" aria-hidden="true">
                    <span class="material-symbols-outlined">
                        ${role === 'user' ? 'person' : 'smart_toy'}
                    </span>
                </div>
                <div>
                    <div class="message-content">${escapeHtml(content).replace(/\n/g, '<br>')}</div>
                    <div class="message-time" style="display: flex; align-items: center; gap: 4px;">
                        <time datetime="${new Date().toISOString()}">${timeString}</time>
                        ${actionButtons}
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // ===== TYPING INDICATOR =====
        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot';
            typingDiv.id = 'typingIndicator';
            typingDiv.setAttribute('aria-live', 'polite');
            typingDiv.setAttribute('aria-label', 'Assistant is typing');
            
            typingDiv.innerHTML = `
                <div class="message-avatar" aria-hidden="true">
                    <span class="material-symbols-outlined">smart_toy</span>
                </div>
                <div class="message-content">
                    <div class="typing-indicator">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            return typingDiv;
        }

        // ===== ESCAPE HTML =====
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ===== LOAD CONVERSATION =====
        function loadConversation(convId) {
            window.location.href = basePath + 'pages/chat.php?conversation_id=' + convId;
        }

        // ===== NEW CONVERSATION =====
        function newConversation() {
            const btn = event.target.closest('button');
            if (btn) btn.disabled = true;
            window.location.href = basePath + 'pages/chat.php?new=true';
        }

        // ===== CLEAR CURRENT CHAT =====
        function clearChat() {
            if (confirm('Are you sure you want to delete this conversation? This action cannot be undone.')) {
                fetch(basePath + 'api/delete_conversation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = basePath + 'pages/chat.php';
                    } else {
                        alert('Error deleting conversation: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting conversation');
                });
            }
        }

        // ===== INITIALIZE ON PAGE LOAD =====
        initializeTheme();
        restoreDraft();
        showWelcomeModal();
        chatMessages.scrollTop = chatMessages.scrollHeight;
    </script>
</body>
</html>
