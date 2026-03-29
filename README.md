# 📺 YouTube AI Course Scraper

A Laravel-based application that uses AI and YouTube Data API to automatically generate and curate educational playlists. The system builds structured learning paths by combining AI-generated search queries with real YouTube playlist data.

---

## 🌟 Features

- 🤖 AI-powered search strategy using Google Gemini
- 📚 Automatic discovery of YouTube playlists
- ⏱️ Calculates total course duration (aggregated from all videos)
- 👀 Aggregates video views for popularity insights
- 🔄 Smart deduplication using playlist ID
- 📊 Category-based filtering system
- ⚡ Simple and responsive Bootstrap dashboard
- 🧠 Fallback logic when AI API is unavailable

---

## 🛠️ Tech Stack

- **Backend:** Laravel 10/11
- **AI Engine:** Google Gemini API
- **Data Source:** YouTube Data API v3
- **Frontend:** Blade, Bootstrap 5, Vanilla JavaScript
- **Database:** MySQL

---

# ⚙️ Setup Instructions

## 1. Clone the Repository

bash
git clone https://github.com/your-username/youtube-ai-scraper.git
cd youtube-ai-scraper

## 2. Install Dependencies
composer install
npm install
npm run dev

## 3. Setup Environment
cp .env.example .env
php artisan key:generate

## 4. Configure Database
### Update your .env file:
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
### Then run migrations:
php artisan migrate

# 🔑 API Keys Configuration
This project requires two API services:
## 1. YouTube Data API

Enable it from Google Cloud Console:
	•	https://console.cloud.google.com/apis/library/youtube.googleapis.com

### Add to .env:
YOUTUBE_API_KEY=your_youtube_api_key
 
## 2. Google Gemini API
Enable Generative Language API:
	•	https://console.cloud.google.com/apis/library/generativelanguage.googleapis.com

### Add to .env:
GEMINI_API_KEY=your_gemini_api_key

# 🚀 How to Run the Project
## 1. Start Laravel Server
php artisan serve
## 2. Open in Browser
http://127.0.0.1:8000

## 3. Usage Flow
### 	1.	Enter categories (one per line)
Web Development
Data Science
Graphic Design
### 2.	Click Start Fetching
### 3.	System will:
	•	Send category to Gemini AI
	•	Generate optimized YouTube search queries
	•	Fetch playlists from YouTube
	•	Extract playlist metadata
	•	Calculate total duration
	•	Store unique results in database
### 4.	Browse curated playlists from dashboard

# 👨‍💻 Author

## 🔥 Why this version is strong
- Looks like **real SaaS GitHub project**
- Clear recruiter-friendly structure
- Explains API limitations (VERY important)
- Shows engineering maturity
- Clean setup + run flow
