<?php

namespace App\Http\Controllers;

use App\Models\Course;
use DateInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    /**
     * Show the main page with the input form and course grid.
     * GET /
     */
    public function youtubeDurationToSeconds($duration)
    {
        $interval = new DateInterval($duration);

        return ($interval->h * 3600) +
            ($interval->i * 60) +
            $interval->s;
    }
    public function index(Request $request)
    {
        $activeCategory = $request->query('category', 'all');

        // Start building the query
        $query = Course::query()->latest();

        // Filter by category if one is selected
        if ($activeCategory !== 'all') {
            $query->where('category', $activeCategory);
        }

        // Paginate 8 cards per page
        $courses = $query->paginate(8)->withQueryString();

        // Get all categories that exist in the database (for the tab bar)
        $categories = Course::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Get count per category (for the badge numbers on tabs)
        $counts = Course::selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('home', compact('courses', 'categories', 'counts', 'activeCategory'));
    }

    /**
     * This is the main function. It does 3 things in order:
     *   1. For each category → ask AI to generate search queries
     *   2. For each query    → search YouTube for playlists
     *   3. Save each playlist to the database (skip duplicates)
     *
     * POST /fetch
     */
    public function fetch(Request $request): JsonResponse
    {
        // ── Step 0: Validate the input ────────────────────────────────────────
        $request->validate([
            'categories' => ['required', 'string', 'max:2000'],
        ]);

        // Turn the textarea text into a clean array
        $categories = array_values(array_filter(
            array_map('trim', explode("\n", $request->input('categories'))),
            fn($line) => $line !== ''
        ));

        if (empty($categories)) {
            return response()->json(['error' => 'Enter at least one category.'], 422);
        }

        // Counters we'll return at the end
        $coursesFound = 0;
        $duplicatesSkipped = 0;
        $stop = false;

        // ── Steps 1 + 2 + 3: Loop through each category ──────────────────────
        foreach ($categories as $category) {
            if ($stop) break;
            // STEP 1: Ask the AI to give us search queries for this category
            $queries = $this->generateQueriesWithAI($category);

            // STEP 2 + 3: For each query, search YouTube then save results
            foreach ($queries as $query) {
                if ($stop) break;

                // STEP 2: Search YouTube for playlists matching this query
                $playlists = $this->searchYouTube($query);

                // STEP 3: Save each playlist to the database
                foreach ($playlists as $playlist) {
                    if ($stop) break;

                    // Check if this playlist already exists (deduplication)
                    $alreadyExists = Course::where('playlist_id', $playlist['playlist_id'])->exists();

                    if ($alreadyExists) {
                        $duplicatesSkipped++;
                        continue; // skip, don't save again
                    }

                    // Save the new playlist
                    Course::create([
                        'playlist_id' => $playlist['playlist_id'],
                        'title' => $playlist['title'],
                        'description' => $playlist['description'],
                        'thumbnail_url' => $playlist['thumbnail_url'],
                        'channel_name' => $playlist['channel_name'],
                        'video_count' => $playlist['video_count'],
                        'view_count' => $playlist['view_count'],
                        'playlist_duration' => $playlist['playlist_duration'],
                        'category' => $category,
                        'search_query' => $query,
                    ]);

                    $coursesFound++;

                    if ($coursesFound >= 2) {
                        $stop = true;
                        break;
                    }

                }
            }
        }

        // ── Done! Return a summary to the frontend ────────────────────────────
        return response()->json([
            $queries,
            $playlists,
            'success' => true,
            'courses_found' => $coursesFound,
            'duplicates_skipped' => $duplicatesSkipped,
            'message' => "Completed! Added {$coursesFound} course, ignored {$duplicatesSkipped} duplicated.",
        ]);
    }

    private function generateQueriesWithAI(string $category): array
    {
        $prompt = "Generate exactly 20 YouTube search queries to find educational playlist courses about: \"{$category}\".
Mix beginner, intermediate, advanced levels. Use formats like: full course, tutorial series, bootcamp, masterclass.
Return ONLY a JSON array of strings, no explanation. Example: [\"query one\", \"query two\"]";

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent";

            $response = Http::withHeaders([
                'x-goog-api-key' => config('services.gemini.key'),          // -H "x-goog-api-key: $GEMINI_API_KEY"
                'Content-Type' => 'application/json', // -H "Content-Type: application/json"
            ])
                ->timeout(45)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Gemini API error: ' . $response->body());
            }

            // Response: candidates[0] → content → parts[0] → text
            $rawText = $response->json('candidates.0.content.parts.0.text', '[]');

            // Clean markdown fences
            $clean = preg_replace('/^```(?:json)?\s*/m', '', $rawText);
            $clean = preg_replace('/\s*```\s*$/m', '', $clean);

            $decoded = json_decode(trim($clean), true);

            // If valid JSON array
            if (is_array($decoded) && count($decoded) > 0) {
                return array_values(array_filter(array_map('strval', $decoded)));
            }
        } catch (\Throwable $e) {
            Log::warning("AI failed for '{$category}': " . $e->getMessage());
        }

        // Fallback: if AI fails for any reason, use these basic queries
        return [
            "{$category} full course for beginners",
            "{$category} complete tutorial series",
            "{$category} masterclass playlist",
            "learn {$category} step by step",
            "{$category} bootcamp full course",
            "{$category} course for beginners",
            "advanced {$category} tutorial",
            "{$category} crash course",
        ];
    }

    private function searchYouTube(string $query): array
    {
        try {
            // ── Call 1: Search for playlists ──────────────────────────────────
            $searchResponse = Http::timeout(20)->get('https://www.googleapis.com/youtube/v3/search', [
                'part' => 'snippet',
                'q' => $query,
                'type' => 'playlist',
                'maxResults' => 2,  
                'key' => config('services.youtube.key'),
            ]);

            if ($searchResponse->failed()) {
                Log::warning("YouTube search failed for: {$query}");
                return [];
            }

            $items = $searchResponse->json('items', []);

            if (empty($items)) {
                return [];
            }

            // Collect all playlist IDs to get video counts in a second call
            $playlistIds = array_filter(
                array_map(fn($item) => $item['id']['playlistId'] ?? null, $items)
            );

            // ── Call 2: Get video count for each playlist ─────────────────────
            // (Search API doesn't include video count, so we need this extra call)
            $videoCounts = [];

            if (!empty($playlistIds)) {
                $detailsResponse = Http::timeout(15)->get('https://www.googleapis.com/youtube/v3/playlists', [
                    'part' => 'contentDetails',
                    'id' => implode(',', $playlistIds),
                    'maxResults' => 2,
                    'key' => config('services.youtube.key'),
                ]);
                foreach ($detailsResponse->json('items', []) as $detail) {
                    $videoCounts[$detail['id']] = $detail['contentDetails']['itemCount'] ?? 0;
                }
            }

            // ── Build the final result array ──────────────────────────────────
            $results = [];

            foreach ($items as $item) {
                $playlistId = $item['id']['playlistId'] ?? null;

                if (!$playlistId)
                    continue;

                $snippet = $item['snippet'] ?? [];
                $thumbnails = $snippet['thumbnails'] ?? [];

                // Pick the best thumbnail quality available
                $thumbnailUrl = $thumbnails['high']['url']
                    ?? $thumbnails['medium']['url']
                    ?? $thumbnails['default']['url']
                    ?? '';

                $results[] = [
                    'playlist_id' => $playlistId,
                    'title' => trim($snippet['title'] ?? 'Unknown Title'),
                    'description' => trim($snippet['description'] ?? ''),
                    'thumbnail_url' => $thumbnailUrl,
                    'channel_name' => trim($snippet['channelTitle'] ?? ''),
                    'video_count' => (int) ($videoCounts[$playlistId] ?? 0),
                    'view_count' =>  $this->getPlaylistViewCount($playlistId),
                    'playlist_duration' => $this->getPlaylistDuration($playlistId),
                ];
            }

            return $results;

        } catch (\Throwable $e) {
            Log::warning("YouTube exception for '{$query}': " . $e->getMessage());
            return [];
        }
    }
    private function getPlaylistDuration(string $playlistId): string
    {
        try {
            $key = config('services.youtube.key');

            $videoIds = [];
            $nextPageToken = null;

            // STEP 1: Get ALL videos in playlist (loop pagination)
            do {
                $response = Http::timeout(15)->get(
                    'https://www.googleapis.com/youtube/v3/playlistItems',
                    [
                        'part' => 'contentDetails',
                        'playlistId' => $playlistId,
                        'maxResults' => 50,
                        'pageToken' => $nextPageToken,
                        'key' => $key,
                    ]
                );

                if ($response->failed())
                    break;

                foreach ($response->json('items', []) as $item) {
                    $videoId = $item['contentDetails']['videoId'] ?? null;
                    if ($videoId) {
                        $videoIds[] = $videoId;
                    }
                }

                $nextPageToken = $response->json('nextPageToken');

            } while ($nextPageToken);

            if (empty($videoIds)) {
                return '0m';
            }

            // STEP 2: Get durations in chunks (max 50 per request)
            $totalSeconds = 0;

            foreach (array_chunk($videoIds, 50) as $chunk) {
                $videoResponse = Http::timeout(15)->get(
                    'https://www.googleapis.com/youtube/v3/videos',
                    [
                        'part' => 'contentDetails',
                        'id' => implode(',', $chunk),
                        'key' => $key,
                    ]
                );

                foreach ($videoResponse->json('items', []) as $video) {
                    $duration = $video['contentDetails']['duration'] ?? null;

                    if ($duration) {
                        $totalSeconds += $this->youtubeDurationToSeconds($duration);
                    }
                }
            }

            // STEP 3: Convert to readable format
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);

            return $hours > 0
                ? "{$hours}h {$minutes}m"
                : "{$minutes}m";

        } catch (\Throwable $e) {
            Log::warning("Duration error {$playlistId}: " . $e->getMessage());
            return '0m';
        }
    }
    private function getPlaylistViewCount(string $playlistId): int
    {
        try {
            $key = config('services.youtube.key');;

            // STEP 1: get first valid video ID
            $itemsResponse = Http::timeout(10)->get(
                'https://www.googleapis.com/youtube/v3/playlistItems',
                [
                    'part' => 'contentDetails',
                    'playlistId' => $playlistId,
                    'maxResults' => 5,
                    'key' => $key,
                ]
            );

            if ($itemsResponse->failed()) {
                Log::error('PlaylistItems failed', $itemsResponse->json());
                return 0;
            }

            $items = $itemsResponse->json('items', []);

            $firstVideoId = null;

            foreach ($items as $item) {
                if (!empty($item['contentDetails']['videoId'])) {
                    $firstVideoId = $item['contentDetails']['videoId'];
                    break;
                }
            }

            if (!$firstVideoId) {
                return 0;
            }

            // STEP 2: get views only
            $videoResponse = Http::timeout(10)->get(
                'https://www.googleapis.com/youtube/v3/videos',
                [
                    'part' => 'statistics', // 👈 only what we need
                    'id' => $firstVideoId,
                    'key' => $key,
                ]
            );

            if ($videoResponse->failed()) {
                Log::error('Video failed', $videoResponse->json());
                return 0;
            }

            $viewCount = $videoResponse->json('items.0.statistics.viewCount');

            return (int) ($viewCount ?? 0);

        } catch (\Throwable $e) {
            Log::error("Playlist view error {$playlistId}: " . $e->getMessage());
            return 0;
        }
    }
}