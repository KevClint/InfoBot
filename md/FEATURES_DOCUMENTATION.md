# InfoBot - Professional AI Chatbot Application
## Complete Feature Overview & Implementation Guide

### 📋 Project Overview
InfoBot is a fully-featured, professional AI chatbot web application built with PHP, MySQL, and modern web technologies. It includes user authentication, admin panel, customizable UI, and advanced chat features.

---

## ✨ Features Implemented

### 1. **User Appearance & UI Features**
- **Dark Mode Toggle**: Users can enable/disable dark mode, with preference saved in localStorage and database
- **Font Size Adjustment**: Three size options (small, medium, large) with CSS variable scaling
- **Theme Color Selection**: 5 theme color options (Blue, Green, Purple, Orange, Cyan)
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- **Settings Page**: Dedicated `/pages/settings.php` for customizing appearance

**Location**: `/pages/settings.php`
**Styles**: `/assets/css/style.css` (includes dark mode and theme variables)

### 2. **Advanced Chat Features**
- **AI Chatbot Interface**: Interactive chat with Groq API integration
- **Typing Indicator**: Shows when bot is "thinking" with animated dots
- **Favorite Responses**: Users can mark helpful bot answers as favorites (heart icon)
- **Search Chat History**: Filter messages in current conversation by keyword
- **Welcome Modal**: Friendly greeting on first visit (sessionStorage managed)
- **Conversation Titles Auto-generated**: Based on first user message (max 60 chars)

**Location**: `/pages/chat.php`
**Features API**: `/api/toggle_favorite.php`, `/api/search_chats.php`

### 3. **Admin Panel & Management**
- **Admin Dashboard**: Overview with statistics (users, conversations, messages, KB entries)
- **Knowledge Base CRUD**: Create, read, update, delete knowledge entries
- **Admin Role System**: Users table now supports role field (admin/user)
- **Knowledge Base Management**: Add custom Q&A pairs for chatbot training
- **Status Toggling**: Activate/deactivate knowledge entries

**Locations**: 
- `/pages/admin/index.php` - Dashboard
- `/pages/admin/knowledge.php` - Knowledge base management
- `/api/save_knowledge.php` - Knowledge base API

### 4. **Database Enhancements**
- **New Tables**:
  - `user_preferences`: Stores dark mode, font size, theme color per user
  - `favorite_responses`: Tracks user-marked favorite bot responses
  - Updated `users` table with `role` field (admin/user)
  - Updated `knowledge_base` table with `is_active` flag

- **Security**: All queries use prepared statements to prevent SQL injection
- **Indexes**: Optimized indexes for faster queries

**Location**: `/database/schema.sql`

### 5. **Modern UI/UX Design**
- **Google Fonts**: Poppins font family for clean, modern appearance
- **Material Icons**: Material Symbols Outlined icons throughout
- **Professional Color Scheme**: Primary blue with complementary colors
- **Smooth Animations**: Hover effects, transitions, and micro-interactions
- **Modal Dialogs**: For welcome modal and knowledge base forms
- **Dark Mode Support**: Complete dark variant for all components
- **Responsive Grid Layouts**: Auto-adapting to different screen sizes

**Files**:
- `/assets/css/style.css` - Main stylesheet (2400+ lines)
- `/assets/css/admin.css` - Admin panel styles

### 6. **Authentication & Security**
- **User Roles**: Admin and regular user support
- **Session Management**: Secure session handling with regeneration
- **Password Security**: Bcrypt hashing with PASSWORD_DEFAULT
- **CSRF Protection**: Token generation and verification
- **XSS Prevention**: HTML escaping in all user inputs
- **SQL Injection Prevention**: Prepared statements throughout
- **Admin Requirements**: `requireAdmin()` function ensures authorization

**Location**: `/includes/auth.php` (includes new role functions)

---

## 📁 Project Structure

```
chatbot_project/
├── assets/
│   └── css/
│       ├── style.css          # Main stylesheet (responsive, dark mode, themes)
│       └── admin.css          # Admin panel styles
├── config/
│   └── database.php           # Database connection configuration
├── database/
│   └── schema.sql             # Updated SQL schema with all tables
├── includes/
│   ├── auth.php               # Auth functions (+ new role checks)
│   ├── chatbot.php            # Chatbot API integration
│   └── preferences.php        # NEW: User preferences & favorites functions
├── pages/
│   ├── admin/
│   │   ├── index.php          # NEW: Admin dashboard
│   │   └── knowledge.php      # NEW: Knowledge base management
│   ├── chat.php               # UPDATED: Chat interface (all features)
│   ├── settings.php           # NEW: User preferences/appearance
│   ├── login.php              # User login
│   ├── register.php           # User registration
│   ├── logout.php             # User logout
│   └── manage.php             # User management (optional)
├── api/
│   ├── chat.php               # Chat API endpoint
│   ├── delete_conversation.php# Delete chat endpoint
│   ├── toggle_favorite.php    # NEW: Add/remove favorites
│   ├── save_preferences.php   # NEW: Save user preferences
│   ├── search_chats.php       # NEW: Search chat history
│   └── save_knowledge.php     # NEW: Save knowledge entries
├── index.php                  # Root/home page
├── README.md                  # Documentation
├── SETUP_GUIDE.txt            # Setup instructions
└── TECHNICAL_DOCUMENTATION.md # Technical details
```

---

## 🎨 Color Theme System

### Available Colors
- **Blue** (Default): #3b82f6 - Professional and trustworthy
- **Green**: #10b981 - Fresh and calm
- **Purple**: #8b5cf6 - Creative and premium
- **Orange**: #f97316 - Warm and welcoming
- **Cyan**: #06b6d4 - Modern and cool

### Dark Mode Colors
Automatically adjusts all colors when dark mode is enabled:
- Background: #111827 (dark gray)
- Surface: #1f2937 (lighter gray)
- Text Primary: #f9fafb (white)
- Text Secondary: #d1d5db (light gray)

---

## 🚀 How to Use New Features

### For Users:
1. **Settings Page**: Click Settings in navigation to customize appearance
2. **Dark Mode**: Toggle the dark mode switch on/settings page
3. **Font Size**: Choose small, medium, or large text
4. **Theme Color**: Select preferred primary color
5. **Favorites**: Click the heart icon on bot responses to save them
6. **Search**: Use the search box in chat header to filter messages
7. **Welcome**: See greeting modal on first visit to chat

### For Admins:
1. **Admin Panel**: Use Admin link in navigation (only visible if admin role)
2. **Dashboard**: View system statistics at `/pages/admin/`
3. **Knowledge Base**: Manage Q&A entries at `/pages/admin/knowledge.php`
4. **Add Entry**: Click "Add New Entry" button to create knowledge items
5. **Edit/Delete**: Manage existing knowledge base entries

---

## 🔐 Security Features

- ✅ **Prepared Statements**: All database queries use bound parameters
- ✅ **Password Hashing**: Bcrypt with `PASSWORD_DEFAULT` algorithm
- ✅ **Session Security**: Regenerated session IDs on login
- ✅ **XSS Protection**: HTML escaping with `htmlspecialchars()`
- ✅ **CSRF Tokens**: Generated and verified for form submissions
- ✅ **SQL Injection Prevention**: No raw SQL in queries
- ✅ **Access Control**: Role-based admin access verification
- ✅ **Input Validation**: All user inputs validated before processing

---

## 📱 Responsive Breakpoints

- **Desktop**: Full layout with sidebar (1024px+)
- **Tablet**: Adjusted spacing and navigation (768px - 1023px)
- **Mobile**: Collapsed sidebar, full-width chat (< 768px)

---

## 🛠 API Endpoints

### Chat
- `POST /api/chat.php` - Send message to chatbot

### Favorites
- `POST /api/toggle_favorite.php` - Add/remove favorite responses

### Preferences
- `POST /api/save_preferences.php` - Save user appearance settings

### Search
- `POST /api/search_chats.php` - Search chat history by keyword

### Knowledge Base
- `POST /api/save_knowledge.php` - Create/update knowledge entries
- `POST /api/delete_conversation.php` - Delete conversation

---

## 📊 Database Tables

### users
```sql
id (INT, PRIMARY KEY, AUTO_INCREMENT)
username (VARCHAR 50, UNIQUE)
email (VARCHAR 100, UNIQUE)
password (VARCHAR 255, hashed)
role (VARCHAR 20, default: 'user')
created_at (TIMESTAMP)
```

### user_preferences
```sql
id (INT, PRIMARY KEY)
user_id (INT, UNIQUE, FOREIGN KEY)
dark_mode (BOOLEAN, default: FALSE)
font_size (VARCHAR 20, default: 'medium')
theme_color (VARCHAR 20, default: 'blue')
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

### favorite_responses
```sql
id (INT, PRIMARY KEY)
user_id (INT, FOREIGN KEY)
message_id (INT, FOREIGN KEY)
created_at (TIMESTAMP)
UNIQUE (user_id, message_id)
```

### conversations
```sql
id (INT, PRIMARY KEY)
user_id (INT, FOREIGN KEY)
title (VARCHAR 200, auto-generated from first message)
created_at (TIMESTAMP)
updated_at (TIMESTAMP, auto-updated)
```

### messages
```sql
id (INT, PRIMARY KEY)
conversation_id (INT, FOREIGN KEY)
user_id (INT, FOREIGN KEY)
role (ENUM: 'user', 'assistant')
content (TEXT)
created_at (TIMESTAMP)
```

### knowledge_base
```sql
id (INT, PRIMARY KEY)
question (VARCHAR 500)
answer (TEXT)
category (VARCHAR 100)
created_by (INT, FOREIGN KEY)
is_active (BOOLEAN, default: TRUE)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
```

---

## 🎯 Recent Updates & Fixes

1. ✅ **Fixed**: Logo link creating duplicate conversations - now preserves conversation ID
2. ✅ **Fixed**: New Chat button spam - now disables after click
3. ✅ **Added**: Conversation Title Auto-generation from first user message
4. ✅ **Added**: Complete Dark Mode support with persistent storage
5. ✅ **Added**: Theme Color Customization system
6. ✅ **Added**: Font Size adjustment with CSS scaling
7. ✅ **Added**: User Preferences database table and management
8. ✅ **Added**: Favorite/Unfavorite functionality for responses
9. ✅ **Added**: Search chat history feature in current conversation
10. ✅ **Added**: Welcome modal on first visit to chat page
11. ✅ **Added**: Admin Dashboard with statistics
12. ✅ **Added**: Knowledge Base Management CRUD system
13. ✅ **Updated**: User authentication with role support

---

## 📝 CSS Classes & Utilities

### Theme Variables
```css
--color-primary: Changes with selected theme
--dark-mode: Automatic color scheme swap
--font-size-multiplier: 0.9, 1, or 1.1 based on preference
```

### Responsive Classes
```css
@media (max-width: 768px) - Tablet/Mobile styles
@media (max-width: 480px) - Small mobile styles
```

---

## 🔄 Local Storage Usage

- `darkMode`: 'true' or 'false' - Dark mode preference
- `fontSize`: 'small', 'medium', or 'large' - Text size preference
- `themeColor`: 'blue', 'green', 'purple', 'orange', 'cyan' - Theme color

Session Storage:
- `visited`: Tracks first visit to show welcome modal

---

## ⚙️ Installation & Setup

1. Create database: `ai_chatbot_db`
2. Import schema: `database/schema.sql`
3. Update config: `config/database.php` with credentials
4. Set Groq API key in config file
5. Access at: `http://localhost/chatbot_project/`
6. Default admin: `admin` / `admin123`

---

## 🎓 Beginner-Friendly Features

- **Clean Code Comments**: Every function documented
- **Modular Structure**: Separate files for concerns
- **Prepared Statements**: Security built-in by default
- **Error Handling**: Comprehensive error messages
- **Responsive Design**: Mobile-first approach
- **Progressive Enhancement**: Works without JavaScript

---

## 📞 Support & Documentation

- Refer to `README.md` for general information
- Check `SETUP_GUIDE.txt` for installation steps
- See `TECHNICAL_DOCUMENTATION.md` for technical details
- Code comments provide guidance on implementation

---

**Version**: 2.0 - Professional Edition with Full Features  
**Last Updated**: February 2026  
**Status**: Production Ready ✅
