📺 YouTube AI Course Scraper
A high-performance Laravel-based web application that automates the discovery of educational content. By combining Google Gemini AI for search strategy and the YouTube Data API v3 for data extraction, it builds a curated library of video courses with deep metadata.

🌟 Key Features
AI-Powered Discovery: Uses the gemini-3-flash-preview model to generate optimized, level-specific search queries (Beginner to Advanced).

Deep Metadata Extraction:

Duration Calculation: Automatically sums the duration of all videos in a playlist to show total course length (e.g., 4h 20m).

Popularity Tracking: Fetches real-time view counts.

Smart Deduplication: Prevents redundant entries by tracking unique playlist_ids.

Modern UI: A clean, responsive dashboard built with Bootstrap 5 and Vanilla JS, featuring asynchronous "Start/Stop" fetching controls.

Dynamic Filtering: Instant category-based navigation with live course counters.

🛠️ Technical Stack
Framework: Laravel 10/11

AI Engine: Google Gemini Pro API

Data Source: YouTube Data API v3

Frontend: Blade Templates, Bootstrap 5, FontAwesome 7

Database: MySQL

🔑 API Configuration
This project requires two specific API keys from the Google Cloud Console.

1. Enable Services
Ensure the following APIs are enabled in your Google Project:

YouTube Data API v3

Generative Language API (for Gemini)

2. Environment Variables
Add your keys to the .env file:

Code snippet
# YouTube API Configuration
YOUTUBE_API_KEY=your_youtube_api_key_here

# Gemini AI Configuration
GEMINI_API_KEY=your_gemini_api_key_here
3. Service Mapping
Update your config/services.php to include the following:

PHP
'youtube' => [
    'key' => env('YOUTUBE_API_KEY'),
],
'gemini' => [
    'key' => env('GEMINI_API_KEY'),
],
🚀 Installation & Setup
Clone the Repository:

Bash
git clone https://github.com/yourusername/youtube-course-scraper.git
cd youtube-course-scraper
Install Dependencies:

Bash
composer install
npm install && npm run dev
Setup Database:
Update your .env with your database credentials, then run:

Bash
php artisan migrate
Run the Application:

Bash
php artisan serve
📖 Usage Guide
Access the Dashboard: Open http://localhost:8000 in your browser.

Input Categories: Enter topics (one per line) in the input area (e.g., Fullstack Web Development, Data Science with Python).

Fetch Data: Click Start Fetching.

The system sends the category to Gemini to generate a precise search query.

It then scans YouTube for the top matching playlists.

It loops through every video in those playlists to calculate the total runtime.

Explore: Use the category tabs to filter and find the perfect course. Clicking a card opens the course directly on YouTube.

🛡️ Important Notes
Quota Management: The getPlaylistDuration method requests details for every video. To save API quota, the maxResults for search is currently set to a conservative number.

Fallback Logic: If the Gemini API is unreachable or fails to return valid JSON, the system uses a robust set of fallback search strings to ensure the user still gets results.

