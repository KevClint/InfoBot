# 📁 COMPLETE PROJECT FOLDER STRUCTURE

```
infobot/
│
├── 📄 index.php                          # Main entry point - redirects to login or chat
├── 📄 .htaccess                          # Apache configuration for security & performance
├── 📄 README.md                          # Complete project documentation
├── 📄 SETUP_GUIDE.txt                    # Quick setup instructions
├── 📄 TECHNICAL_DOCUMENTATION.md         # Detailed technical explanation
│
├── 📁 config/                            # Configuration files
│   └── 📄 database.php                   # Database connection & Groq API settings
│
├── 📁 includes/                          # Helper functions & utilities
│   ├── 📄 auth.php                       # Authentication functions (login, logout, etc.)
│   └── 📄 chatbot.php                    # Chatbot API functions (send/receive messages)
│
├── 📁 pages/                             # Web pages (views)
│   ├── 📄 login.php                      # User login page
│   ├── 📄 register.php                   # User registration page
│   ├── 📄 chat.php                       # Main chat interface (the heart of the app)
│   ├── 📄 manage.php                     # Management dashboard (CRUD overview)
│   ├── 📄 knowledge_form.php             # Add/Edit knowledge base entries
│   ├── 📄 knowledge_view.php             # View single knowledge entry
│   └── 📄 logout.php                     # Logout handler
│
├── 📁 api/                               # API endpoints (backend logic)
│   ├── 📄 chat.php                       # Handle chat messages to/from Groq API
│   └── 📄 delete_conversation.php        # Delete conversation endpoint
│
├── 📁 assets/                            # Static files (CSS, JS, images)
│   └── 📁 css/
│       └── 📄 style.css                  # Main stylesheet (all styles in one file)
│
└── 📁 database/                          # Database files
    └── 📄 schema.sql                     # Complete database structure + sample data
```

---

## 📋 FILE DESCRIPTIONS

### Root Level Files

**index.php**
- First file that runs when accessing the site
- Checks if user is logged in
- Redirects to chat (if logged in) or login page (if not)

**.htaccess**
- Apache web server configuration
- Security settings (block sensitive files)
- Performance optimization (compression, caching)
- URL rewriting rules

**README.md**
- Complete project documentation
- Installation instructions
- Usage guide
- Troubleshooting tips

**SETUP_GUIDE.txt**
- Quick start guide in plain text
- Step-by-step setup instructions
- Perfect for beginners

**TECHNICAL_DOCUMENTATION.md**
- In-depth technical explanation
- Architecture details
- Security features
- Learning outcomes

---

### 📁 config/ - Configuration

**database.php** (Most Important Config File)
- Database connection settings
- Groq API key configuration
- Connection helper functions
- ⚠️ This is where you add your API key!

**What you need to configure:**
```php
define('DB_HOST', 'localhost');           // Usually don't change
define('DB_USER', 'root');                // Usually don't change
define('DB_PASS', '');                    // Usually don't change
define('DB_NAME', 'ai_chatbot_db');       // Usually don't change

define('GROQ_API_KEY', 'YOUR_KEY_HERE');  // ← ADD YOUR KEY HERE!
```

---

### 📁 includes/ - Helper Functions

**auth.php** - Authentication Library
Functions:
- `isLoggedIn()` - Check if user is logged in
- `getCurrentUserId()` - Get current user's ID
- `getCurrentUsername()` - Get current user's name
- `requireLogin()` - Protect pages (redirect if not logged in)
- `loginUser()` - Create user session
- `logoutUser()` - Destroy session
- `sanitizeInput()` - Clean user input
- `hashPassword()` - Hash password securely
- `verifyPassword()` - Check password against hash

**chatbot.php** - Chatbot Library
Functions:
- `getChatbotResponse()` - Send message to Groq API
- `getSystemPrompt()` - Get chatbot personality
- `saveMessage()` - Save message to database
- `createConversation()` - Create new chat
- `getConversationMessages()` - Get chat history
- `getUserConversations()` - Get all user's chats
- `deleteConversation()` - Delete a chat
- `updateConversationTitle()` - Rename chat

---

### 📁 pages/ - Web Pages

**login.php**
- User login form
- Validates credentials
- Creates session on success
- Shows error messages

**register.php**
- New user registration form
- Validates email format
- Checks password strength
- Hashes password before saving
- Checks for duplicate usernames/emails

**chat.php** ⭐ (Main Application)
- Chat interface (like ChatGPT)
- Sidebar with conversation list
- Message display area
- Input box with send button
- JavaScript handles real-time updates
- Connects to /api/chat.php

**manage.php** (Dashboard)
- Shows all conversations
- Shows knowledge base entries
- CRUD operations interface
- Delete conversations
- View/Edit/Delete knowledge entries

**knowledge_form.php**
- Add new knowledge entry
- Edit existing entry
- Form with question/answer/category
- Validation

**knowledge_view.php**
- Display full knowledge entry
- Shows question, answer, metadata
- Edit/Delete buttons (if you own it)

**logout.php**
- Simple logout handler
- Destroys session
- Redirects to login

---

### 📁 api/ - API Endpoints

**chat.php** ⭐ (Most Important API)
- Receives user messages via POST
- Saves user message to database
- Gets conversation history
- Sends to Groq API
- Saves AI response
- Returns JSON response

Example request:
```json
POST /api/chat.php
{
  "conversation_id": 5,
  "message": "What is PHP?"
}
```

Example response:
```json
{
  "success": true,
  "message": "PHP is a server-side scripting language..."
}
```

**delete_conversation.php**
- Deletes a conversation
- Security: Only owner can delete
- Cascading delete (removes all messages)

---

### 📁 assets/css/ - Styling

**style.css** (Single Stylesheet)
All styles in one file, organized by section:

1. **Imports**: Google Fonts & Icons
2. **CSS Variables**: Colors, spacing, shadows
3. **Reset**: Browser defaults normalization
4. **Utilities**: Helper classes (mt-1, mb-2, etc.)
5. **Components**: Buttons, forms, cards, alerts
6. **Layout**: Header, navigation, containers
7. **Chat Interface**: Messages, input, sidebar
8. **Auth Pages**: Login/register styling
9. **Management Pages**: Tables, CRUD interface
10. **Responsive**: Mobile breakpoints

**Design System:**
```css
--primary-color: #6366f1      /* Indigo - main brand color */
--success-color: #10b981      /* Green - success messages */
--danger-color: #ef4444       /* Red - errors, delete */
--bg-color: #f9fafb          /* Light gray - background */
--surface-color: #ffffff      /* White - cards, boxes */
```

---

### 📁 database/ - Database

**schema.sql** ⭐ (Critical File)
Contains:
1. Database creation command
2. All table structures
3. Relationships (foreign keys)
4. Indexes for performance
5. Sample data (admin user + 3 knowledge entries)

**Tables created:**
- `users` - User accounts
- `conversations` - Chat sessions
- `messages` - Individual chat messages
- `knowledge_base` - Q&A pairs

**Default admin user:**
- Username: admin
- Password: admin123
- Email: admin@chatbot.com

---

## 🔄 HOW FILES WORK TOGETHER

### User Login Flow:
```
1. User visits index.php
2. index.php includes auth.php
3. auth.php checks session
4. No session → redirect to login.php
5. login.php shows form
6. User submits form
7. login.php validates with database.php
8. Success → auth.php creates session
9. Redirect to chat.php
```

### Chat Message Flow:
```
1. User types in chat.php
2. JavaScript sends AJAX to api/chat.php
3. api/chat.php includes:
   - auth.php (check login)
   - database.php (connect to DB)
   - chatbot.php (API functions)
4. chatbot.php saves message
5. chatbot.php calls Groq API
6. Groq returns response
7. chatbot.php saves response
8. api/chat.php returns JSON
9. JavaScript displays message
```

### CRUD Flow (Knowledge Base):
```
CREATE:
  manage.php → knowledge_form.php → saves to DB

READ:
  manage.php → displays list
  knowledge_view.php → shows details

UPDATE:
  manage.php → knowledge_form.php?id=X → updates DB

DELETE:
  manage.php → POST action → deletes from DB
```

---

## 🎯 WHICH FILES TO MODIFY

### To Change API Key:
📄 `config/database.php` - Line ~11

### To Change Design:
📄 `assets/css/style.css` - Any section

### To Add New Page:
1. Create file in `pages/` folder
2. Include `auth.php` at top
3. Call `requireLogin()` if needed
4. Add link in navigation

### To Change Chatbot Personality:
📄 `includes/chatbot.php` - `getSystemPrompt()` function

### To Modify Database:
📄 `database/schema.sql` - Edit and re-import

---

## 📊 FILE SIZES (Approximate)

- **Total Project**: ~250 KB
- **PHP Files**: ~150 KB
- **CSS**: ~30 KB
- **SQL**: ~5 KB
- **Documentation**: ~65 KB

Very lightweight! No bloated frameworks.

---

## 🔍 FILE DEPENDENCIES

**Every PHP page includes:**
```php
require_once '../config/database.php';   // Database connection
require_once '../includes/auth.php';      // Authentication
```

**Chat-related files also include:**
```php
require_once '../includes/chatbot.php';   // Chatbot functions
```

---

## 💾 WHAT GETS STORED WHERE

### Database (MySQL):
- User accounts
- Chat conversations
- All messages
- Knowledge base entries

### Session (PHP):
- User ID
- Username
- Login timestamp

### Browser (None):
- No localStorage
- No cookies (except session)
- Everything server-side for security

---

## 🎨 CUSTOMIZATION GUIDE

### Change Colors:
Edit `style.css` root variables (lines 13-25)

### Change Font:
Edit `style.css` @import line and font-family

### Change Layout:
Edit `style.css` responsive breakpoint (line ~800)

### Change AI Model:
Edit `database.php` GROQ_MODEL constant

### Change Database Name:
Edit `database.php` DB_NAME constant + recreate database

---

## 📱 RESPONSIVE BREAKPOINTS

**Desktop**: > 768px
- Full sidebar visible
- Large chat area
- All features

**Mobile/Tablet**: ≤ 768px
- Sidebar hidden (toggle button)
- Stacked layout
- Simplified navigation
- Smaller tables

---

## 🔒 SECURITY FILES

**Most Security-Critical Files:**
1. `includes/auth.php` - Authentication logic
2. `config/database.php` - Credentials
3. `.htaccess` - Access control
4. All pages that handle user input

**Never expose:**
- `database.php` (has API key)
- `.sql` files (database structure)
- Error logs

---

## 🎓 FILES BY LEARNING TOPIC

**Learn PHP Basics:**
- `pages/login.php`
- `pages/register.php`

**Learn Database/SQL:**
- `database/schema.sql`
- `config/database.php`
- `includes/chatbot.php`

**Learn Authentication:**
- `includes/auth.php`
- `pages/login.php`
- `pages/logout.php`

**Learn API Integration:**
- `api/chat.php`
- `includes/chatbot.php`

**Learn CRUD:**
- `pages/manage.php`
- `pages/knowledge_form.php`
- `pages/knowledge_view.php`

**Learn CSS:**
- `assets/css/style.css`

**Learn JavaScript/AJAX:**
- `pages/chat.php` (bottom script section)

---

**All files work together to create a complete, professional web application! 🚀**
