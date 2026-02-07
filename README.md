# AI Chatbot Web Application

A professional, full-featured AI chatbot web application built with PHP, MySQL, and the Groq API. This project includes user authentication, chat conversations, and a knowledge base management system with full CRUD operations.

---

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation Guide](#installation-guide)
- [Project Structure](#project-structure)
- [How It Works](#how-it-works)
- [Usage Guide](#usage-guide)
- [API Configuration](#api-configuration)
- [Troubleshooting](#troubleshooting)

---

## ✨ Features

### 🔐 Authentication System
- User registration with email validation
- Secure login with password hashing (bcrypt)
- Session-based authentication
- Password strength requirements
- Protected routes

### 💬 AI Chatbot
- Real-time chat interface
- Powered by Groq API (Llama 3.1 model)
- Conversation history tracking
- Multiple conversation management
- Typing indicators
- Clean, modern UI similar to ChatGPT

### 📊 CRUD System
- **Create**: Start new conversations, add knowledge base entries
- **Read**: View conversation history, read knowledge entries
- **Update**: Edit knowledge base questions and answers
- **Delete**: Remove conversations and knowledge entries

### 👨‍💼 Management Dashboard
- View all conversations with message counts
- Manage knowledge base entries
- Category-based organization
- User-specific data access control

---

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+ (Procedural with some OOP concepts)
- **Database**: MySQL 5.7+ / MariaDB
- **Server**: XAMPP (Apache + MySQL)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Icons**: Google Material Symbols
- **Fonts**: Google Fonts (Poppins)
- **AI API**: Groq API

---

## 📦 Requirements

- XAMPP (or LAMP/WAMP) installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- A Groq API key (free at https://console.groq.com/)
- Modern web browser

---

## 🚀 Installation Guide

### Step 1: Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP on your computer
3. Start Apache and MySQL from XAMPP Control Panel

### Step 2: Setup Project Files

1. Extract the `chatbot_project` folder
2. Copy the entire folder to XAMPP's `htdocs` directory:
   ```
   C:\xampp\htdocs\chatbot_project\  (Windows)
   /Applications/XAMPP/htdocs/chatbot_project/  (Mac)
   /opt/lampp/htdocs/chatbot_project/  (Linux)
   ```

### Step 3: Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "New" to create a new database
3. Name it: `ai_chatbot_db`
4. Select "utf8mb4_general_ci" as collation
5. Click "Create"

### Step 4: Import Database Schema

1. Select the `ai_chatbot_db` database
2. Click the "SQL" tab
3. Open `database/schema.sql` file with a text editor
4. Copy all the SQL code
5. Paste it into the SQL tab in phpMyAdmin
6. Click "Go" to execute

**Note**: The schema includes a default admin user:
- Username: `admin`
- Password: `admin123`

### Step 5: Configure Groq API

1. Visit https://console.groq.com/
2. Sign up for a free account
3. Generate an API key
4. Open `config/database.php`
5. Replace `YOUR_GROQ_API_KEY_HERE` with your actual API key:
   ```php
   define('GROQ_API_KEY', 'gsk_your_actual_key_here');
   ```

### Step 6: Test the Application

1. Open your browser
2. Go to: http://localhost/chatbot_project/
3. You should be redirected to the login page
4. Try logging in with:
   - Username: `admin`
   - Password: `admin123`
5. Or create a new account by clicking "Create one"

---

## 📁 Project Structure

```
chatbot_project/
│
├── index.php                 # Main entry point (redirects to login/chat)
│
├── config/
│   └── database.php          # Database connection & Groq API config
│
├── includes/
│   ├── auth.php              # Authentication helper functions
│   └── chatbot.php           # Chatbot API helper functions
│
├── pages/
│   ├── login.php             # User login page
│   ├── register.php          # User registration page
│   ├── chat.php              # Main chat interface
│   ├── manage.php            # Management dashboard (CRUD)
│   ├── knowledge_form.php    # Add/Edit knowledge entries
│   ├── knowledge_view.php    # View knowledge entry details
│   └── logout.php            # Logout handler
│
├── api/
│   ├── chat.php              # Handle chat messages to Groq API
│   └── delete_conversation.php  # Delete conversation endpoint
│
├── assets/
│   └── css/
│       └── style.css         # Main stylesheet
│
└── database/
    └── schema.sql            # Database schema with sample data
```

---

## 🔧 How It Works

### Authentication Flow

1. User visits the site → redirected to `login.php` (if not logged in)
2. User logs in → credentials verified against database
3. Password is verified using `password_verify()` (secure hashing)
4. Session is created with user ID and username
5. User is redirected to chat interface

### Chat Flow

1. User types a message and clicks Send
2. JavaScript sends message to `/api/chat.php` via AJAX
3. PHP saves user message to database
4. PHP retrieves conversation history (last 10 messages)
5. PHP sends messages to Groq API with system prompt
6. Groq API returns AI response
7. PHP saves AI response to database
8. Response is sent back to JavaScript
9. JavaScript displays the response in chat UI

### Groq API Integration

The chatbot uses Groq's API with these settings:
- **Model**: `llama-3.1-8b-instant` (fast, efficient)
- **Temperature**: 0.7 (balanced creativity)
- **Max Tokens**: 1000 (response length limit)

**System Prompt** (defines chatbot personality):
```
"You are a helpful, friendly, and knowledgeable AI assistant. 
Your goal is to provide accurate, clear, and helpful responses 
to user questions. Be conversational and warm in your tone, 
but remain professional. If you don't know something, admit 
it honestly. Keep your responses concise but informative. 
Always be respectful and encouraging."
```

### Database Structure

**Users Table**
- Stores user accounts
- Passwords are hashed with bcrypt
- Includes email and creation date

**Conversations Table**
- Each chat session is a conversation
- Linked to user via foreign key
- Tracks creation and update times

**Messages Table**
- Individual messages in conversations
- Role: 'user' or 'assistant'
- Includes timestamp

**Knowledge Base Table**
- Q&A pairs for reference
- Organized by category
- Tracks who created each entry

---

## 📖 Usage Guide

### For Regular Users

1. **Register/Login**: Create an account or log in
2. **Start Chatting**: Type messages in the chat box
3. **View History**: Click on past conversations in the sidebar
4. **New Chat**: Click "New Chat" button
5. **Delete Chat**: Click trash icon in chat header

### For Admins/Managers

1. **Access Dashboard**: Click "Manage" in navigation
2. **View Conversations**: See all your chat history
3. **Manage Knowledge Base**:
   - Click "Add Entry" to create new Q&A
   - Click "View" to see full entry
   - Click "Edit" to modify your entries
   - Click "Delete" to remove entries

---

## 🔑 API Configuration

### Getting a Groq API Key

1. Visit https://console.groq.com/
2. Sign up with your email
3. Verify your email
4. Click "API Keys" in the dashboard
5. Click "Create API Key"
6. Copy the key (starts with `gsk_`)
7. Paste it in `config/database.php`

### API Rate Limits

Groq offers generous free tier limits:
- 30 requests per minute
- 14,400 requests per day
- Perfect for development and small projects

---

## 🐛 Troubleshooting

### Problem: "Connection failed" error

**Solution**: 
- Check if MySQL is running in XAMPP
- Verify database name is `ai_chatbot_db`
- Check credentials in `config/database.php`

### Problem: "API error" when chatting

**Solution**:
- Verify your Groq API key is correct
- Check if you have internet connection
- Ensure API key is not expired

### Problem: "Page not found" (404 error)

**Solution**:
- Ensure project is in `htdocs/chatbot_project/`
- Use correct URL: `http://localhost/chatbot_project/`
- Check Apache is running in XAMPP

### Problem: Blank page or PHP errors

**Solution**:
- Enable error reporting in PHP
- Check Apache error logs in XAMPP
- Ensure PHP version is 7.4 or higher

### Problem: Login doesn't work

**Solution**:
- Clear browser cookies/cache
- Check if database has the admin user
- Try registering a new account
- Verify password is at least 6 characters

### Problem: Conversations not saving

**Solution**:
- Check database connection
- Verify table structure is correct
- Check browser console for JavaScript errors

---

## 🎓 For Students

This project is designed as a school/college project and includes:

✅ **Beginner-friendly code** with extensive comments  
✅ **No frameworks** - pure PHP, HTML, CSS, JavaScript  
✅ **Complete CRUD operations** demonstrated  
✅ **Modern UI design** with responsive layout  
✅ **Security best practices** (password hashing, SQL injection prevention)  
✅ **Professional structure** suitable for portfolios  

---

## 📝 License

This project is created for educational purposes. Feel free to use it for your school projects!

---

## 🤝 Credits

- **UI Icons**: Google Material Symbols
- **Fonts**: Google Fonts (Poppins)
- **AI Model**: Groq (Llama 3.1)
- **Design Inspiration**: Modern chat applications

---

## 📧 Need Help?

If you encounter any issues:

1. Check the troubleshooting section above
2. Review the code comments for explanations
3. Verify all setup steps were followed correctly
4. Check XAMPP error logs for detailed error messages

---

**Happy Coding! 🚀**
