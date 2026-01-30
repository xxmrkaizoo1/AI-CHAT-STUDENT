<p align="center">
  <img src="logo.svg" width="200" alt="Student AI Chatbot Logo">
</p>

<h1 align="center">🎓 Student AI Chatbot</h1>

<p align="center">
A modern, AI-powered chatbot built with Laravel and Local AI (Ollama) to help students learn with simple, step-by-step explanations.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-PHP-red">
  <img src="https://img.shields.io/badge/AI-Local%20AI%20(Ollama)-blue">
  <img src="https://img.shields.io/badge/UI-Modern%20Animated-success">
  <img src="https://img.shields.io/badge/Project-Student%20Learning-green">
</p>

---

## 📌 Project Overview

**Student AI Chatbot** is a web-based learning assistant designed specifically for students.  
It allows students to ask academic questions and receive **real-time AI-generated answers** using **local AI models (Ollama)** — without relying on paid APIs.

Unlike simple rule-based chatbots, this system uses **real AI intelligence** that understands natural language and responds dynamically.

The chatbot is **strictly education-focused** and will politely refuse non-study-related questions.

---

## 🎯 Project Objectives

1. Build a **real AI-powered chatbot** using Laravel  
2. Use **Local AI (Ollama)** instead of cloud APIs  
3. Help students learn with **simple & clear explanations**  
4. Demonstrate **AI + Laravel integration**  
5. Create a **modern, professional UI** suitable for portfolio use  

---

## 👨‍🎓 Target Users

- Students  
- Beginner programmers  
- Laravel learners  
- Anyone interested in AI-powered web apps  

---

## ✨ System Features

### 🤖 Local AI Chatbot (Ollama)
- Uses **local AI models** (no OpenAI / no API cost)
- Runs fully on the developer’s machine
- Fast responses with streaming output

### ⚡ Live Streaming Responses
- AI types answers **word-by-word**
- Cursor animation while generating
- Looks similar to ChatGPT

### ⛔ Stop & 🔁 Regenerate
- Stop AI while it’s typing
- Regenerate answer with one click
- Prevents sending new messages during generation

### 📚 Student-Friendly Explanations
- Simple language
- Step-by-step explanations
- Short examples
- Beginner-friendly tone

### 🚫 Strict Study-Only Rules
- Blocks:
  - Games
  - Social media
  - Movies & music
  - Hacking / cheating
- Filters offensive language
- Responds politely with study guidance only

### 🌙 Dark / ☀️ Light Mode
- One-click theme toggle
- Preference saved using `localStorage`
- Smooth modern UI transitions

### 🎨 Modern Animated UI
- Clean chat bubbles
- Smooth fade-in animations
- Syntax-highlighted code blocks
- Markdown support (code, bold, italic)

### 🧠 Chat Memory
- Remembers recent messages per session
- Maintains conversation context
- Clears chat with one button

---

## 🧠 How the AI System Works (Simple Flow)

1. Student types a question  
2. Message is sent to Laravel backend  
3. Laravel validates & filters the question  
4. Question is sent to **Ollama Local AI**  
5. AI streams the answer back  
6. Laravel saves chat history  
7. Answer appears live on screen  

---

## 🛠️ Technology Stack

### Backend
- **Laravel (PHP)**
- Laravel HTTP Client
- Streaming responses

### Frontend
- Blade Template Engine
- HTML, CSS, JavaScript
- Highlight.js (code coloring)

### Artificial Intelligence
- **Ollama (Local AI)**
- LLaMA / compatible local models

### UI / UX
- Modern animated chat UI
- Dark / Light mode
- Responsive layout

---

## 📂 Important Project Files

| File | Description |
|-----|------------|
| `ChatController.php` | Handles AI logic, filtering, streaming |
| `chat.blade.php` | Chat UI, animations, dark/light mode |
| `ChatMessage.php` | Chat history model |
| `.env` | AI configuration (Ollama URL & model) |

---

## 🚀 Why This Project Is Special

✔ No paid API  
✔ Runs fully offline  
✔ Real AI (not fake logic)  
✔ Student-focused design  
✔ Portfolio-ready project  
✔ Demonstrates modern AI UX  

---

## 📸 Screenshots (Optional)
_Add screenshots of Light Mode, Dark Mode, Streaming, Code Blocks_

---

## 📄 License
This project is developed for **educational purposes**.

---

## 🙌 Author
Developed by **[Your Name]**  
Student | Laravel Developer | AI Enthusiast
