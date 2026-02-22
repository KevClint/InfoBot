<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infobot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: '#4F46E5'
                    },
                    fontFamily: {
                        inter: ['Inter', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <style>
        body { font-family: Inter, sans-serif; }
        #chatFeed { scroll-behavior: smooth; }
    </style>
</head>
<body class="h-full overflow-hidden bg-gray-50 text-gray-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="flex h-screen w-full">
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-[260px] -translate-x-full border-r border-gray-200 bg-white transition-transform duration-200 ease-out lg:static lg:translate-x-0 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-full flex-col">
                <div class="border-b border-gray-200 px-4 py-4 dark:border-slate-800">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand/10 text-brand">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 3a9 9 0 1 0 9 9"></path>
                                    <path d="M21 3v6h-6"></path>
                                </svg>
                            </span>
                            <span class="text-2xl font-semibold tracking-tight">Infobot</span>
                        </div>
                        <button type="button" id="closeSidebar" class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200" aria-label="Close sidebar">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 18l-6-6 6-6"></path>
                            </svg>
                        </button>
                    </div>

                    <button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>
                        New Chat
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-4 py-4">
                    <h2 class="mb-3 text-sm font-semibold text-gray-500 dark:text-slate-400">Tools</h2>
                    <ul class="space-y-1 text-sm">
                        <li><button type="button" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-slate-200 dark:hover:bg-slate-800"><span>📝</span> Summarize</button></li>
                        <li><button type="button" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-slate-200 dark:hover:bg-slate-800"><span>⌨</span> Code Helper</button></li>
                        <li><button type="button" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-slate-200 dark:hover:bg-slate-800"><span>🖼</span> Image Analysis</button></li>
                    </ul>

                    <h2 class="mb-3 mt-6 text-sm font-semibold text-gray-500 dark:text-slate-400">Recent Chats</h2>
                    <ul class="space-y-1 text-sm">
                        <li><button type="button" class="w-full truncate rounded-lg bg-gray-100 px-3 py-2 text-left text-gray-800 dark:bg-slate-800 dark:text-slate-100">Hey Infobot, can you write...</button></li>
                        <li><button type="button" class="w-full truncate rounded-lg px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-800">Create an API checklist</button></li>
                        <li><button type="button" class="w-full truncate rounded-lg px-3 py-2 text-left text-gray-700 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-800">Summarize release notes</button></li>
                    </ul>
                </div>
            </div>
        </aside>

        <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden"></div>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3">
                    <button id="openSidebar" type="button" class="rounded-lg border border-gray-200 p-2 text-gray-600 hover:bg-gray-100 lg:hidden dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Open sidebar">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <label for="modelSelect" class="sr-only">Model selector</label>
                    <select id="modelSelect" class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <option value="standard">Standard</option>
                        <option value="pro">Pro</option>
                        <option value="research">Research</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button id="themeBtn" type="button" class="rounded-lg border border-gray-200 p-2 text-gray-600 hover:bg-gray-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Toggle theme">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </button>
                    <button type="button" class="rounded-lg border border-gray-200 p-2 text-gray-600 hover:bg-gray-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="Settings">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.2a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.2a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3h.1a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.2a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8v.1a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.2a1.7 1.7 0 0 0-1.4 1z"></path>
                        </svg>
                    </button>
                </div>
            </header>

            <main class="relative flex min-h-0 flex-1 flex-col">
                <div id="chatFeed" class="flex-1 overflow-y-auto px-4 pb-44 pt-6 sm:px-6">
                    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
                        <article class="flex items-start gap-3">
                            <div class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 3a9 9 0 1 0 9 9"></path>
                                    <path d="M21 3v6h-6"></path>
                                </svg>
                            </div>
                            <div class="max-w-[90%] rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <p class="text-sm text-gray-700 dark:text-slate-200">Hi, I am Infobot. Ask me anything and I will answer using your local Ollama model.</p>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="absolute inset-x-0 bottom-0 border-t border-gray-200 bg-white/95 px-4 py-4 backdrop-blur sm:px-6 dark:border-slate-800 dark:bg-slate-900/95">
                    <form id="chatForm" class="mx-auto flex w-full max-w-3xl items-end gap-2 rounded-2xl border border-gray-200 bg-white p-2.5 shadow-lg shadow-gray-300/30 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/20">
                        <button type="button" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200" aria-label="Attach file">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21.4 11.1l-8.5 8.5a6 6 0 1 1-8.5-8.5l8.5-8.5a4 4 0 1 1 5.7 5.7l-8.5 8.5a2 2 0 0 1-2.8-2.8l8.1-8.1"></path>
                            </svg>
                        </button>
                        <label for="promptInput" class="sr-only">Message input</label>
                        <textarea id="promptInput" rows="1" placeholder="Ask Infobot anything..." class="max-h-40 min-h-[40px] flex-1 resize-none border-0 bg-transparent px-1.5 py-2 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-slate-100 dark:placeholder:text-slate-500"></textarea>
                        <button id="sendBtn" type="submit" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50" aria-label="Send message">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 2L11 13"></path>
                                <path d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        const chatFeed = document.getElementById('chatFeed');
        const chatForm = document.getElementById('chatForm');
        const promptInput = document.getElementById('promptInput');
        const sendBtn = document.getElementById('sendBtn');
        const modelSelect = document.getElementById('modelSelect');
        const openSidebar = document.getElementById('openSidebar');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const themeBtn = document.getElementById('themeBtn');
        const THEME_KEY = 'infobot-theme';

        function escapeHtml(value) {
            return value.replace(/[&<>"']/g, function (ch) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[ch];
            });
        }

        function normalizeLines(value) {
            return value.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        }

        function renderAssistantHtml(content) {
            let safe = escapeHtml(normalizeLines(content || ''));
            const codeBlocks = [];

            safe = safe.replace(/```([a-zA-Z0-9+#\-.]*)\n?([\s\S]*?)```/g, function (_, lang, code) {
                const token = '__CODE_BLOCK_' + codeBlocks.length + '__';
                codeBlocks.push({
                    lang: lang || 'code',
                    code: code.replace(/^\n+|\n+$/g, '')
                });
                return token;
            });

            safe = safe.replace(/`([^`\n]+)`/g, '<code class="rounded bg-gray-200 px-1 py-0.5 text-[13px] dark:bg-slate-700">$1</code>');
            safe = safe.replace(/\n/g, '<br>');

            codeBlocks.forEach(function (block, index) {
                const html = '<div class="mt-3 overflow-hidden rounded-xl border border-slate-700/70 bg-slate-900">' +
                    '<div class="border-b border-slate-700/70 px-3 py-2 text-xs font-medium uppercase tracking-wide text-slate-300">' + escapeHtml(block.lang) + '</div>' +
                    '<pre class="overflow-x-auto p-3 text-[13px] leading-6 text-slate-100"><code>' + block.code + '</code></pre>' +
                    '</div>';

                safe = safe.replace('__CODE_BLOCK_' + index + '__', html);
            });

            return safe;
        }

        function getTimeLabel() {
            return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function createUserMessage(text) {
            const el = document.createElement('article');
            el.className = 'flex justify-end';
            el.innerHTML = '<div class="max-w-[90%] rounded-2xl bg-gray-200 px-4 py-3 text-sm text-gray-900 dark:bg-slate-700 dark:text-slate-50">' + escapeHtml(text).replace(/\n/g, '<br>') + '<div class="mt-2 text-right text-[11px] text-gray-500 dark:text-slate-300">' + getTimeLabel() + '</div></div>';
            return el;
        }

        function createAssistantMessage(html) {
            const el = document.createElement('article');
            el.className = 'flex items-start gap-3';
            el.innerHTML = '<div class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a9 9 0 1 0 9 9"></path><path d="M21 3v6h-6"></path></svg></div><div class="max-w-[90%] rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"><div class="message-content text-sm text-gray-800 dark:text-slate-100">' + html + '</div><div class="mt-2 text-[11px] text-gray-500 dark:text-slate-400">' + getTimeLabel() + '</div></div>';
            return el;
        }

        function createTypingMessage() {
            const el = document.createElement('article');
            el.className = 'flex items-start gap-3';
            el.id = 'typingIndicator';
            el.innerHTML = '<div class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a9 9 0 1 0 9 9"></path><path d="M21 3v6h-6"></path></svg></div><div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-slate-700 dark:bg-slate-900"><div class="flex items-center gap-1.5"><span class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:0ms]"></span><span class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:150ms]"></span><span class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:300ms]"></span></div></div>';
            return el;
        }

        function appendMessage(element) {
            chatFeed.firstElementChild.appendChild(element);
            chatFeed.scrollTop = chatFeed.scrollHeight;
        }

        function autoResizeTextarea() {
            promptInput.style.height = 'auto';
            promptInput.style.height = Math.min(promptInput.scrollHeight, 160) + 'px';
        }

        function setSending(sending) {
            sendBtn.disabled = sending;
            promptInput.disabled = sending;
            modelSelect.disabled = sending;
        }

        function openMobileSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        }

        function closeMobileSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        }

        function applyTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem(THEME_KEY, theme);
        }

        async function sendMessage() {
            const prompt = promptInput.value.trim();
            if (!prompt) return;

            setSending(true);
            appendMessage(createUserMessage(prompt));
            promptInput.value = '';
            autoResizeTextarea();

            const typing = createTypingMessage();
            appendMessage(typing);

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        prompt: prompt,
                        model: modelSelect.value
                    })
                });

                const data = await response.json();
                if (typing.parentNode) typing.remove();

                if (!response.ok || !data.success) {
                    const errorText = data.error || 'Failed to fetch a response from the local model.';
                    appendMessage(createAssistantMessage('<p>' + escapeHtml(errorText) + '</p>'));
                } else {
                    appendMessage(createAssistantMessage(renderAssistantHtml(data.response || '')));
                }
            } catch (error) {
                if (typing.parentNode) typing.remove();
                appendMessage(createAssistantMessage('<p>Request failed. Check that Ollama is running on <code>localhost:11434</code>.</p>'));
            } finally {
                setSending(false);
                promptInput.focus();
            }
        }

        chatForm.addEventListener('submit', function (event) {
            event.preventDefault();
            sendMessage();
        });

        promptInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        });

        promptInput.addEventListener('input', autoResizeTextarea);
        openSidebar.addEventListener('click', openMobileSidebar);
        closeSidebar.addEventListener('click', closeMobileSidebar);
        sidebarOverlay.addEventListener('click', closeMobileSidebar);

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                sidebarOverlay.classList.add('hidden');
                sidebar.classList.remove('-translate-x-full');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });

        themeBtn.addEventListener('click', function () {
            const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            applyTheme(next);
        });

        const savedTheme = localStorage.getItem(THEME_KEY);
        if (savedTheme === 'dark' || savedTheme === 'light') {
            applyTheme(savedTheme);
        } else {
            applyTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        }

        autoResizeTextarea();
        promptInput.focus();
    </script>
</body>
</html>
