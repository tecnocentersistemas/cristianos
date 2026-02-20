<?php
/**
 * FaithTunes AI API - Video Creation Endpoint
 * Receives a user prompt, calls OpenAI to generate content plan
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    http_response_code(405);
    exit;
}

// Get OpenAI key from local config file on VPS
function getOpenAIKey() {
    // First try local config file
    $configPaths = [
        __DIR__ . '/../.openai-key',
        '/var/www/cristianos/.openai-key',
        '/root/.faithtunes-openai-key'
    ];

    foreach ($configPaths as $path) {
        if (file_exists($path)) {
            $key = trim(file_get_contents($path));
            if ($key && strpos($key, 'sk-') === 0) {
                return $key;
            }
        }
    }

    // Fallback: try environment variable
    $envKey = getenv('OPENAI_API_KEY');
    if ($envKey) return $envKey;

    return null;
}

// Call OpenAI
function callOpenAI($apiKey, $prompt, $lang = 'es') {
    $systemPrompt = "You are FaithTunes, a Christian music video creator AI. The user wants to create a video with music, nature images, and Bible verses.

Based on the user's request, generate a JSON response with this EXACT structure:
{
  \"title\": \"Video title in the user's language\",
  \"theme\": \"one of: faith, hope, love, peace, gratitude, strength\",
  \"mood\": \"one of: peaceful, joyful, powerful, reflective, uplifting\",
  \"genre\": \"one of: country, rock, gospel, folk, worship, ballad\",
  \"poem\": [\"Line 1 of a short Christian poem/lyric\", \"Line 2\", \"Line 3\", \"Line 4\", \"Line 5\", \"Line 6\"],
  \"verses\": [
    {\"ref\": \"Bible Reference\", \"text\": \"Full verse text\"},
    {\"ref\": \"Bible Reference 2\", \"text\": \"Full verse text 2\"},
    {\"ref\": \"Bible Reference 3\", \"text\": \"Full verse text 3\"}
  ],
  \"imageSearchTerms\": [\"nature search term 1\", \"nature search term 2\", \"nature search term 3\", \"nature search term 4\", \"nature search term 5\"],
  \"description\": \"Short description of the video in the user's language\"
}

Rules:
- The poem should be a short original Christian poem/lyric (6 lines) related to the theme
- Bible verses must be REAL and accurate
- Image search terms should be nature/animal related (mountains, eagles, rivers, sunsets, lambs, forests, etc.)
- Respond in the same language the user writes in
- The content must be Christian/inspirational
- ONLY return the JSON, no other text";

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.8,
        'max_tokens' => 1000
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => 'OpenAI API error', 'code' => $httpCode, 'response' => $response];
    }

    $result = json_decode($response, true);
    $content = $result['choices'][0]['message']['content'] ?? '';
    
    // Extract JSON from response (handle markdown code blocks)
    $content = trim($content);
    if (strpos($content, '```') !== false) {
        preg_match('/```(?:json)?\s*(.*?)\s*```/s', $content, $matches);
        $content = $matches[1] ?? $content;
    }
    
    $parsed = json_decode($content, true);
    if (!$parsed) {
        return ['error' => 'Failed to parse AI response', 'raw' => $content];
    }
    
    return $parsed;
}

// Fetch images from Pexels
function fetchPexelsImages($searchTerms) {
    // Pexels API is free, 200 req/hour
    $pexelsKey = 'YOUR_PEXELS_KEY'; // Will use fallback URLs if not set
    $images = [];
    
    foreach ($searchTerms as $term) {
        $url = 'https://api.pexels.com/v1/search?' . http_build_query([
            'query' => $term . ' nature',
            'per_page' => 1,
            'orientation' => 'landscape',
            'size' => 'large'
        ]);
        
        $ctx = stream_context_create([
            'http' => [
                'header' => "Authorization: $pexelsKey\r\n",
                'timeout' => 8
            ]
        ]);
        
        $data = @file_get_contents($url, false, $ctx);
        if ($data) {
            $result = json_decode($data, true);
            if (!empty($result['photos'][0])) {
                $photo = $result['photos'][0];
                $images[] = [
                    'url' => $photo['src']['large2x'] ?? $photo['src']['large'],
                    'alt' => $photo['alt'] ?? $term,
                    'photographer' => $photo['photographer'] ?? 'Pexels'
                ];
                continue;
            }
        }
        
        // Fallback: use curated nature images from Pexels
        $fallbackIds = [417173, 2098427, 1054218, 346529, 2662434, 1252890, 15286, 1166209, 209831, 247502, 1578750, 462118, 326055, 1423600, 53594, 2559484, 1054655, 288621, 1661179, 1032650];
        $randId = $fallbackIds[array_rand($fallbackIds)];
        $images[] = [
            'url' => "https://images.pexels.com/photos/$randId/pexels-photo-$randId.jpeg?auto=compress&cs=tinysrgb&w=1280&h=720&fit=crop",
            'alt' => $term,
            'photographer' => 'Pexels'
        ];
    }
    
    return $images;
}

// Main logic
$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';
$lang = $input['lang'] ?? 'es';

if (empty($prompt)) {
    echo json_encode(['error' => 'Prompt is required']);
    http_response_code(400);
    exit;
}

// Get API key
$apiKey = getOpenAIKey();
if (!$apiKey) {
    echo json_encode(['error' => 'Could not load API key']);
    http_response_code(500);
    exit;
}

// Call OpenAI
$aiResult = callOpenAI($apiKey, $prompt, $lang);
if (isset($aiResult['error'])) {
    echo json_encode($aiResult);
    http_response_code(500);
    exit;
}

// Fetch images
$images = fetchPexelsImages($aiResult['imageSearchTerms'] ?? ['mountains', 'sunset', 'river', 'eagle', 'forest']);

// Select background music - Real compositions from Incompetech (Kevin MacLeod, CC BY 3.0) + verified Pixabay
$audioByMood = [
    'peaceful' => [
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/At%20Rest.mp3', 'name' => 'At Rest - Piano'],
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Eternal%20Hope.mp3', 'name' => 'Eternal Hope - Orchestral'],
        ['url' => 'https://cdn.pixabay.com/audio/2022/02/22/audio_d1718ab41b.mp3', 'name' => 'Calm Meditation']
    ],
    'joyful' => [
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Wholesome.mp3', 'name' => 'Wholesome - Upbeat'],
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Amazing%20Plan.mp3', 'name' => 'Amazing Plan - Bright'],
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Groove%20Grove.mp3', 'name' => 'Groove Grove - Fun']
    ],
    'powerful' => [
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Inspired.mp3', 'name' => 'Inspired - Epic'],
        ['url' => 'https://cdn.pixabay.com/audio/2022/01/18/audio_d0a13f69d2.mp3', 'name' => 'Epic Cinematic'],
        ['url' => 'https://cdn.pixabay.com/audio/2022/11/22/audio_febc508520.mp3', 'name' => 'Orchestral Power']
    ],
    'reflective' => [
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/At%20Rest.mp3', 'name' => 'At Rest - Reflective'],
        ['url' => 'https://cdn.pixabay.com/audio/2021/11/25/audio_91b32e02f9.mp3', 'name' => 'Soft Ambient'],
        ['url' => 'https://cdn.pixabay.com/audio/2022/08/03/audio_54ca0ffa52.mp3', 'name' => 'Gentle Piano']
    ],
    'uplifting' => [
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Wholesome.mp3', 'name' => 'Wholesome - Uplifting'],
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Eternal%20Hope.mp3', 'name' => 'Eternal Hope'],
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Amazing%20Plan.mp3', 'name' => 'Amazing Plan']
    ]
];

// Also map genre to specific styles
$audioByGenre = [
    'country' => [
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Americana.mp3', 'name' => 'Americana - Country'],
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Daily%20Beetle.mp3', 'name' => 'Daily Beetle - Acoustic']
    ],
    'rock' => [
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Inspired.mp3', 'name' => 'Inspired - Rock'],
        ['url' => 'https://cdn.pixabay.com/audio/2022/01/18/audio_d0a13f69d2.mp3', 'name' => 'Epic Rock']
    ],
    'folk' => [
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Americana.mp3', 'name' => 'Americana - Folk'],
        ['url' => 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Daily%20Beetle.mp3', 'name' => 'Daily Beetle - Folk']
    ]
];

$mood = $aiResult['mood'] ?? 'peaceful';
$genre = $aiResult['genre'] ?? 'worship';

// Prefer genre-specific track if available, otherwise use mood
$trackPool = $audioByGenre[$genre] ?? $audioByMood[$mood] ?? $audioByMood['peaceful'];
$selected = $trackPool[array_rand($trackPool)];
$selectedAudio = $selected['url'];
$audioName = $selected['name'];

// Build final response
$response = [
    'success' => true,
    'video' => [
        'title' => $aiResult['title'] ?? 'FaithTunes Video',
        'theme' => $aiResult['theme'] ?? 'faith',
        'mood' => $mood,
        'genre' => $aiResult['genre'] ?? 'worship',
        'description' => $aiResult['description'] ?? '',
        'poem' => $aiResult['poem'] ?? [],
        'verses' => $aiResult['verses'] ?? [],
        'images' => $images,
        'audio' => $selectedAudio,
        'audioName' => $audioName ?? 'Instrumental'
    ]
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
