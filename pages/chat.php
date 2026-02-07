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
                <button class="btn btn-primary" style="width: 100%;" onclick="newConversation()">
                    <span class="material-symbols-outlined">add</span>
                    New Chat
                </button>
            </div>
            <div class="sidebar-content">
                <?php if (empty($conversations)): ?>
                    <p class="text-muted" style="font-size: 14px; padding: 12px;">No conversations yet. Start a new chat!</p>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div class="conversation-item <?php echo $conv['id'] == $current_conversation_id ? 'active' : ''; ?>" 
                             onclick="loadConversation(<?php echo $conv['id']; ?>)">
                            <div>
                                <div class="conversation-title"><?php echo htmlspecialchars($conv['title']); ?></div>
                                <div class="conversation-date"><?php echo date('M j, g:i A', strtotime($conv['updated_at'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Chat Area -->
        <main class="chat-main">
            <div class="chat-header">
                <h2 class="chat-title">AI Assistant</h2>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="text" id="searchInput" class="chat-search" placeholder="Search messages..." 
                           style="padding: 8px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-md); width: 200px;">
                    <button class="btn btn-icon btn-secondary" onclick="clearChat()" title="Delete chat">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <span class="material-symbols-outlined">chat_bubble_outline</span>
                        <p>Start a conversation with the AI assistant!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['role']; ?>" data-message-id="<?php echo isset($msg['id']) ? $msg['id'] : ''; ?>">
                            <div class="message-avatar">
                                <span class="material-symbols-outlined">
                                    <?php echo $msg['role'] === 'user' ? 'person' : 'smart_toy'; ?>
                                </span>
                            </div>
                            <div>
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
                                </div>
                                <div class="message-time" style="display: flex; align-items: center; gap: 4px;">
                                    <?php echo date('g:i A', strtotime($msg['created_at'])); ?>
                                    <?php if ($msg['role'] === 'assistant'): ?>
                                        <button class="favorite-btn" onclick="toggleFavorite(this)" title="Add to favorites" style="background: none; border: none; cursor: pointer; padding: 0 4px; color: inherit; font-size: 16px;">
                                            <span class="material-symbols-outlined" style="font-size: 18px;">favorite</span>
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
                        placeholder="Type your message here..."
                        rows="1"
                        required
                    ></textarea>
                    <button type="submit" class="send-button" id="sendButton">
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </form>
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

        // Initialize theme on page load
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

        // Show welcome modal on first visit
        function showWelcomeModal() {
            const hasVisited = sessionStorage.getItem('visited');
            if (!hasVisited) {
                const modalHTML = `
                    <div id="welcomeModal" class="modal-overlay" onclick="closeWelcomeModal(event)">
                        <div class="modal" style="display: block; margin: auto;" onclick="event.stopPropagation()">
                            <div class="modal-header">
                                <h2>Welcome to InfoBot!</h2>
                                <button class="close-button" onclick="closeWelcomeModal()">×</button>
                            </div>
                            <div class="modal-body" style="text-align: center;">
                                <div style="font-size: 48px; margin-bottom: 16px;">
                                    <span class="material-symbols-outlined" style="font-size: 48px;">smart_toy</span>
                                </div>
                                <h3 style="margin-bottom: 12px;">Hello! How can I help you today?</h3>
                                <p style="color: var(--text-secondary); margin-bottom: 20px;">
                                    I'm your AI assistant. You can ask me questions, get information, or have a conversation. 
                                    Don't hesitate to reach out!
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

        // Toggle favorite response
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
                    if (data.action === 'added') {
                        btn.style.color = 'var(--danger-color)';
                    } else {
                        btn.style.color = 'inherit';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Search messages in current conversation
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const messages = chatMessages.querySelectorAll('.message');
            
            messages.forEach(msg => {
                const content = msg.textContent.toLowerCase();
                if (query === '' || content.includes(query)) {
                    msg.style.display = 'flex';
                } else {
                    msg.style.display = 'none';
                }
            });
        });

        // Fix logo link to not create new conversation when clicked
        document.querySelector('.logo').href = basePath + 'pages/chat.php?conversation_id=' + conversationId;

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Handle Enter key (Shift+Enter for new line)
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(e);
            }
        });

        // Send message function
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

            // Show typing indicator
            const typingDiv = showTypingIndicator();

            try {
                // Send message to API
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

                // Remove typing indicator
                typingDiv.remove();

                if (data.success) {
                    // Add bot response to chat
                    addMessage('bot', data.message);
                } else {
                    // Show error
                    addMessage('bot', 'Sorry, I encountered an error: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                typingDiv.remove();
                addMessage('bot', 'Sorry, I couldn\'t connect to the server. Please try again.');
                console.error('Error:', error);
            }

            // Re-enable input
            messageInput.disabled = false;
            sendButton.disabled = false;
            messageInput.focus();
        }

        // Add message to chat display
        function addMessage(role, content) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message ' + role;
            
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            
            let favoriteBtn = '';
            if (role === 'bot') {
                favoriteBtn = `
                    <button class="favorite-btn" onclick="toggleFavorite(this)" title="Add to favorites" style="background: none; border: none; cursor: pointer; padding: 0 4px; color: inherit; font-size: 16px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">favorite</span>
                    </button>
                `;
            }
            
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <span class="material-symbols-outlined">
                        ${role === 'user' ? 'person' : 'smart_toy'}
                    </span>
                </div>
                <div>
                    <div class="message-content">${escapeHtml(content).replace(/\n/g, '<br>')}</div>
                    <div class="message-time" style="display: flex; align-items: center; gap: 4px;">${timeString}${favoriteBtn}</div>
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Show typing indicator
        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot';
            typingDiv.id = 'typingIndicator';
            
            typingDiv.innerHTML = `
                <div class="message-avatar">
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

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load conversation
        function loadConversation(convId) {
            window.location.href = basePath + 'pages/chat.php?conversation_id=' + convId;
        }

        // New conversation
        function newConversation() {
            const btn = event.target.closest('button');
            if (btn) btn.disabled = true;
            window.location.href = basePath + 'pages/chat.php?new=true';
        }

        // Clear current chat
        function clearChat() {
            if (confirm('Are you sure you want to delete this conversation?')) {
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
                        alert('Error deleting conversation');
                    }
                });
            }
        }

        // Initialize on page load
        initializeTheme();
        showWelcomeModal();

        // Auto-scroll to bottom on load
        chatMessages.scrollTop = chatMessages.scrollHeight;
    </script>
</body>
</html>
