-- ============================================
-- AI CHATBOT DATABASE SCHEMA
-- ============================================
-- This file contains all the SQL needed to set up the database
-- Run this in phpMyAdmin after creating a database named 'ai_chatbot_db'

-- Create database (run this first)
CREATE DATABASE IF NOT EXISTS ai_chatbot_db;
USE ai_chatbot_db;

-- ============================================
-- USERS TABLE
-- ============================================
-- Stores user account information
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Will store hashed passwords
    role VARCHAR(20) DEFAULT 'user',  -- user or admin
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- CHAT CONVERSATIONS TABLE
-- ============================================
-- Stores individual chat sessions
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) DEFAULT 'New Conversation',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- CHAT MESSAGES TABLE
-- ============================================
-- Stores individual messages in conversations
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('user', 'assistant') NOT NULL,  -- Who sent the message
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- KNOWLEDGE BASE TABLE (OPTIONAL)
-- ============================================
-- Admins can add custom Q&A pairs for the chatbot
CREATE TABLE IF NOT EXISTS knowledge_base (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    created_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- USER PREFERENCES TABLE
-- ============================================
-- Stores user UI preferences (dark mode, font size, theme color)
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    dark_mode BOOLEAN DEFAULT FALSE,
    font_size VARCHAR(20) DEFAULT 'medium',  -- small, medium, large
    theme_color VARCHAR(20) DEFAULT 'blue',   -- blue, green, purple, orange, cyan
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- FAVORITE RESPONSES TABLE
-- ============================================
-- Stores user's favorite bot responses
CREATE TABLE IF NOT EXISTS favorite_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, message_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- INSERT DEMO ADMIN USER
-- ============================================
-- Password: admin123
-- Always change this in production!
INSERT INTO users (username, email, password) VALUES 
('admin', 'admin@chatbot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================
-- INSERT SAMPLE KNOWLEDGE BASE ENTRIES
-- ============================================
INSERT INTO knowledge_base (question, answer, category, created_by) VALUES
('What is this chatbot?', 'I am a helpful AI assistant powered by Groq API. I can answer questions, provide information, and help you with various tasks!', 'General', 1),
('How do I use this chatbot?', 'Simply type your question or message in the chat box below and press Enter or click Send. I will respond as quickly as possible!', 'General', 1),
('What can you help me with?', 'I can help with general questions, explanations, homework assistance, creative writing, coding help, and much more. Feel free to ask me anything!', 'General', 1);
