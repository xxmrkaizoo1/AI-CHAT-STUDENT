🎓 Student AI Chatbot (Laravel + Real AI)

This project is a simple AI Chatbot for students built using Laravel and OpenAI API.
Students can ask questions, and the AI will reply like a tutor.

🚀 Features

Chat with real AI (OpenAI)

Built using Laravel

Simple UI (HTML + JavaScript)

API key stored safely in .env

Suitable for student projects / final year project

🧰 Requirements

Make sure you have:

PHP 8.1 or above

Composer

Laravel 10 / 11

Internet connection

OpenAI API Key

📦 Installation Steps
1️⃣ Clone or create Laravel project
composer create-project laravel/laravel student-ai-chat
cd student-ai-chat

2️⃣ Run the server
php artisan serve


Open browser:

http://127.0.0.1:8000

3️⃣ Setup OpenAI API Key

Open .env file and add:

OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-4o-mini


⚠️ Do not share your API key

🛣️ Routes Used
GET  /chat   → Chat page
POST /chat   → Send message to AI

🧠 How AI Works

Student types a question

Laravel sends the question to OpenAI API

AI processes the question

AI sends the answer back

Answer is shown on the screen

📂 Main Files
app/Http/Controllers/ChatController.php
resources/views/chat.blade.php
routes/web.php
.env

🎯 Example Use Cases

Ask coding questions

Ask study-related questions

Simple tutor for students

Learning how AI works in real applications

🔐 Security Notes

API key is stored in .env

API is called server-side (safe)

CSRF protection enabled

🧩 Future Improvements

Student login system

Save chat history

AI for specific subjects (Math, Programming)

Admin dashboard

Limit number of messages

👨‍🎓 Author

Student Project
Built for learning Laravel + AI integration
