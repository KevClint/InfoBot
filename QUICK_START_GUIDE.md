# Quick Start Guide - New Features & Customization

## 🎨 Appearance Customization

### Accessing Settings
1. Log in to your account
2. Click **Settings** in the top navigation
3. Customize your preferences

### Dark Mode
- Toggle the **Dark Mode** switch
- Automatically affects all pages
- Preference saved to database and localStorage
- Perfect for low-light environments

### Text Size
Click one of three size buttons:
- **Small** (Recommended for large monitors)
- **Medium** (Default, recommended)
- **Large** (Recommended for accessibility)

### Theme Color
Choose your preferred primary color:
- Blue (Professional, default)
- Green (Calm and friendly)
- Purple (Creative and modern)
- Orange (Warm and welcoming)
- Cyan (Tech-forward)

All colors automatically apply to buttons, links, and highlights throughout the application.

---

## 💬 Chat Features

### Favorite Responses
1. Send a message to the chatbot
2. Look for the **❤️ heart icon** next to bot responses
3. Click to add/remove from favorites
4. Favorited responses appear in red color

### Search Messages
1. In the chat header, use the **Search** box
2. Type any keyword (minimum 2 characters)
3. Messages are filtered in real-time
4. Clear search box to show all messages again

### Welcome Modal
- Shows on your first visit to the chat
- Contains helpful greeting and tips
- Click "Get Started" or close button to dismiss
- Won't appear again during your session

### Auto-Generated Conversation Titles
- Each new chat automatically gets a title based on your first message
- Title is limited to 60 characters
- Helps organize your conversation history in the sidebar

---

## 🛠 Admin Features (Admin Users Only)

### Accessing Admin Panel
1. Only available if you have admin role
2. Click **Admin** in the top navigation
3. View dashboard with system statistics

### Admin Dashboard
View real-time statistics:
- Total registered users
- Number of conversations
- Total messages sent
- Knowledge base entries

### Knowledge Base Management

#### Adding New Knowledge Entry
1. Click **"Add New Entry"** button
2. Fill in the form:
   - **Question**: The query users might ask
   - **Answer**: The chatbot's response
   - **Category**: Choose from dropdown (General, Technical, Usage, Features, Other)
3. Click **"Save Entry"**

#### Editing Knowledge Entry
1. On the Knowledge Base page, find the entry
2. Click the **edit icon** (pencil)
3. Modify the question, answer, or category
4. Click **"Save Entry"**

#### Deleting Knowledge Entry
1. Find the entry in the Knowledge Base table
2. Click the **delete icon** (trash bin)
3. Confirm the deletion

#### Toggling Entry Status
- Entries have an "Active" or "Inactive" status
- Active entries are used by the chatbot
- Inactive entries are archived but not deleted
- Use status buttons to toggle (if implemented)

---

## 🌐 Using the Application

### Starting a New Conversation
1. Click **"New Chat"** button in the sidebar
2. Type your first message
3. Conversation automatically gets a title from your message

### Switching Between Conversations
1. Click any conversation in the sidebar
2. Chat history loads automatically
3. Search and favorites work within that conversation

### Deleting a Conversation
1. Click the **delete icon** (trash) in the top right
2. Confirm deletion
3. You'll be redirected to a new conversation

---

## 📱 Mobile Usage

### Sidebar on Mobile
- Sidebar is hidden by default on small screens
- Pull/swipe from left edge to open (if implemented)
- Tap conversation to switch

### Chat Input
- Textarea expands as you type
- Send button is always visible
- Supports multi-line messages (Shift+Enter)

### Search on Mobile
- Search box accessible from chat header
- Works the same as desktop version
- Results update in real-time

---

## 💾 Data & Privacy

### What Gets Saved
✅ Your conversations and messages (in database)  
✅ Your preferences (dark mode, font size, theme)  
✅ Your favorite responses  
✅ Your login session  

### What's Local Only
✅ First visit tracking (sessionStorage)  
✅ Theme preferences (localStorage as backup)  
✅ Search input (not saved)  

### Deleting Data
- Delete individual conversations anytime
- Your preferences stay until changed
- Account deletion can be requested from admin

---

## ⌨ Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| Enter | Send message |
| Shift + Enter | New line in message |
| Focus on settings | Tab through options |
| Close modal | Escape key (if implemented) |

---

## 🎯 Tips & Best Practices

### For Better Chat Experience
1. **Ask clear questions** - More specific = better answers
2. **Use favorites** - Save helpful responses for reference
3. **Search history** - Find past conversations quickly
4. **Dark mode at night** - Reduce eye strain

### For Admins
1. **Update knowledge base regularly** - Helps chatbot improve
2. **Review popular questions** - See what users ask most
3. **Organize by category** - Makes management easier
4. **Keep answers concise** - Better for chat format

### Accessibility Tips
1. **Use large text size** - If you have vision difficulties
2. **Enable dark mode** - Reduces contrast fatigue
3. **Use descriptive search** - Keywords help filter better
4. **Use favorites** - Mark important responses for quick access

---

## 🛡 Security Notes

### Protecting Your Account
- Don't share your password
- Log out on shared computers
- Your data is encrypted in transit
- All inputs are sanitized

### For Admins
- Only share admin credentials with trusted staff
- Change default admin password after setup
- Review knowledge base for quality
- Monitor user activity

---

## 🐛 Troubleshooting

### Dark Mode Not Saving
- **Solution**: Check if localStorage is enabled in browser
- Try a different browser
- Clear cache and try again

### Theme Color Not Applying
- **Solution**: Refresh the page (Ctrl+F5 for hard refresh)
- Check browser console for errors
- Try a different color

### Search Not Working
- **Solution**: Make sure search term is at least 2 characters
- Message must be from current conversation
- Try simpler search terms

### Favorite Button Not Working
- **Solution**: Refresh the page
- Check browser console for errors
- Make sure you're logged in

### Admin Panel Not Visible
- **Solution**: You must have admin role
- Contact administrator to enable admin access
- Log out and log back in

---

## 📞 Need Help?

- Check the main **README.md** for general information
- Review **TECHNICAL_DOCUMENTATION.md** for technical details
- Check code comments for implementation details
- Contact your system administrator

---

## 🔄 Version History

**v2.0 (Current)**
- Added dark mode system
- Added theme color customization
- Added font size adjustment
- Added favorites feature
- Added search functionality
- Added admin panel
- Added knowledge base management
- Added welcome modal
- Auto-generate conversation titles

**v1.0 (Previous)**
- Basic chat functionality
- User authentication
- Conversation history
- Groq API integration

---

**Last Updated**: February 2026  
**For Questions**: Refer to inline code comments and main documentation
