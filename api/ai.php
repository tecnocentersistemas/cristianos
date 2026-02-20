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
    // Available image categories that we have curated photos for
    $availableCategories = 'mountains, rivers, lakes, ocean, waterfalls, sunsets, sunrise, sky, stars, forests, trees, fields, flowers, desert, snow, rain, eagles, doves, lambs, lions, deer, butterflies, bees, horses, fish, wolves';

    $systemPrompt = "You are FaithTunes, a Christian music video creator AI.

Based on the user's request, return ONLY a JSON object with this structure:
{
  \"title\": \"Video title in the user's language\",
  \"theme\": \"one of: faith, hope, love, peace, gratitude, strength\",
  \"mood\": \"one of: peaceful, joyful, powerful, reflective, uplifting\",
  \"genre\": \"one of: country, rock, gospel, folk, worship, ballad\",
  \"poem\": [\"Line 1\", \"Line 2\", \"Line 3\", \"Line 4\", \"Line 5\", \"Line 6\"],
  \"verses\": [
    {\"ref\": \"Bible Reference\", \"text\": \"Full verse text\"},
    {\"ref\": \"Bible Reference 2\", \"text\": \"Full verse text 2\"},
    {\"ref\": \"Bible Reference 3\", \"text\": \"Full verse text 3\"}
  ],
  \"imageCategories\": [\"category1\", \"category2\", \"category3\", \"category4\", \"category5\"],
  \"description\": \"Short description\"
}

CRITICAL RULES:
- imageCategories MUST be exactly 5 items chosen ONLY from this list: $availableCategories
- Pick the categories that BEST match what the user asked for. If they say 'bees' use 'bees'. If they say 'mountains' use 'mountains'.
- The poem must be 6 lines, original, Christian/inspirational, in the user's language
- Bible verses must be REAL and accurate, in the user's language
- genre must match what the user requests. If they say 'country' use 'country', if 'rock' use 'rock'
- ONLY return JSON, no other text";

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
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

// Curated image database - verified Pexels URLs organized by category
function getImagesByCategory($categories) {
    $db = [
        'mountains' => [
            ['id'=>417173,'alt'=>'Snowy mountain peak'],
            ['id'=>2098427,'alt'=>'Mountain at sunrise'],
            ['id'=>1054218,'alt'=>'Mountain ridge with clouds'],
            ['id'=>147411,'alt'=>'Mountain lake in Italy'],
            ['id'=>2113566,'alt'=>'Golden summit'],
            ['id'=>2559941,'alt'=>'Rock formation']
        ],
        'rivers' => [
            ['id'=>2406389,'alt'=>'Crystal clear river'],
            ['id'=>2743287,'alt'=>'Serene waterfall'],
            ['id'=>346529,'alt'=>'Calm lake reflection'],
            ['id'=>1032650,'alt'=>'Ocean waves']
        ],
        'lakes' => [
            ['id'=>346529,'alt'=>'Calm lake'],
            ['id'=>147411,'alt'=>'Mountain lake'],
            ['id'=>2406389,'alt'=>'River lake']
        ],
        'ocean' => [
            ['id'=>1032650,'alt'=>'Ocean waves'],
            ['id'=>189349,'alt'=>'Ocean horizon'],
            ['id'=>1001682,'alt'=>'Sea waves crashing']
        ],
        'waterfalls' => [
            ['id'=>2743287,'alt'=>'Serene waterfall'],
            ['id'=>2406389,'alt'=>'Waterfall in nature']
        ],
        'sunsets' => [
            ['id'=>36717,'alt'=>'Spectacular sunrise'],
            ['id'=>209831,'alt'=>'Purple sunset'],
            ['id'=>2559484,'alt'=>'Sunbeams through clouds'],
            ['id'=>36744,'alt'=>'Golden sunset meditation']
        ],
        'sunrise' => [
            ['id'=>36717,'alt'=>'Spectacular sunrise'],
            ['id'=>2098427,'alt'=>'Mountain sunrise'],
            ['id'=>2559484,'alt'=>'Morning sunbeams']
        ],
        'sky' => [
            ['id'=>53594,'alt'=>'Blue sky with fluffy clouds'],
            ['id'=>209831,'alt'=>'Colorful sky'],
            ['id'=>2559484,'alt'=>'Sunbeams in sky']
        ],
        'stars' => [
            ['id'=>1252890,'alt'=>'Starry night sky'],
            ['id'=>1229042,'alt'=>'Milky way stars'],
            ['id'=>1694000,'alt'=>'Night sky stars']
        ],
        'forests' => [
            ['id'=>15286,'alt'=>'Lush green forest'],
            ['id'=>167698,'alt'=>'Light rays in forest'],
            ['id'=>1578750,'alt'=>'Forest path'],
            ['id'=>1423600,'alt'=>'Misty forest']
        ],
        'trees' => [
            ['id'=>15286,'alt'=>'Forest trees'],
            ['id'=>1578750,'alt'=>'Tree-lined path'],
            ['id'=>167698,'alt'=>'Sunlight through trees']
        ],
        'fields' => [
            ['id'=>1166209,'alt'=>'Green valley'],
            ['id'=>462118,'alt'=>'Wildflowers field'],
            ['id'=>265216,'alt'=>'Golden wheat field'],
            ['id'=>1227513,'alt'=>'Rolling green hills']
        ],
        'flowers' => [
            ['id'=>462118,'alt'=>'Wildflowers in meadow'],
            ['id'=>56866,'alt'=>'Beautiful flower close-up'],
            ['id'=>931177,'alt'=>'Flower garden'],
            ['id'=>36764,'alt'=>'Sunflower']
        ],
        'desert' => [
            ['id'=>1529881,'alt'=>'Desert sand dunes'],
            ['id'=>1766838,'alt'=>'Desert landscape'],
            ['id'=>2559941,'alt'=>'Rocky desert']
        ],
        'snow' => [
            ['id'=>417173,'alt'=>'Snowy mountains'],
            ['id'=>688660,'alt'=>'Snow covered trees'],
            ['id'=>1684187,'alt'=>'Winter snowfall']
        ],
        'rain' => [
            ['id'=>1423600,'alt'=>'Misty rainy forest'],
            ['id'=>1032650,'alt'=>'Rain on water'],
            ['id'=>167698,'alt'=>'Rain light in forest']
        ],
        'eagles' => [
            ['id'=>2662434,'alt'=>'Eagle soaring in sky'],
            ['id'=>3397939,'alt'=>'Eagle portrait'],
            ['id'=>53581,'alt'=>'Bird of prey flying']
        ],
        'doves' => [
            ['id'=>1661179,'alt'=>'White dove in flight'],
            ['id'=>1556707,'alt'=>'Dove of peace'],
            ['id'=>792416,'alt'=>'White bird flying']
        ],
        'lambs' => [
            ['id'=>288621,'alt'=>'Lamb in green meadow'],
            ['id'=>693776,'alt'=>'Sheep in field'],
            ['id'=>1472445,'alt'=>'Flock of sheep']
        ],
        'lions' => [
            ['id'=>247502,'alt'=>'Majestic lion'],
            ['id'=>2220336,'alt'=>'Lion portrait'],
            ['id'=>1598377,'alt'=>'Lion in nature']
        ],
        'deer' => [
            ['id'=>1054655,'alt'=>'Deer in meadow at dawn'],
            ['id'=>1618606,'alt'=>'Deer in forest'],
            ['id'=>2131635,'alt'=>'Deer portrait']
        ],
        'butterflies' => [
            ['id'=>326055,'alt'=>'Monarch butterfly'],
            ['id'=>672142,'alt'=>'Butterfly on flower'],
            ['id'=>1028225,'alt'=>'Colorful butterfly']
        ],
        'bees' => [
            ['id'=>144252,'alt'=>'Bee collecting pollen'],
            ['id'=>460961,'alt'=>'Bee on flower'],
            ['id'=>1308526,'alt'=>'Honeybee at work'],
            ['id'=>1406954,'alt'=>'Bee on blossom']
        ],
        'horses' => [
            ['id'=>1996333,'alt'=>'Horse in field'],
            ['id'=>1714211,'alt'=>'Wild horse running'],
            ['id'=>635499,'alt'=>'Horse portrait']
        ],
        'fish' => [
            ['id'=>128756,'alt'=>'Tropical fish underwater'],
            ['id'=>2765872,'alt'=>'Fish in clear water'],
            ['id'=>1145274,'alt'=>'Colorful fish']
        ],
        'wolves' => [
            ['id'=>1587300,'alt'=>'Wolf in nature'],
            ['id'=>2361952,'alt'=>'Wolf portrait'],
            ['id'=>1302290,'alt'=>'Wolf in snow']
        ]
    ];

    $images = [];
    $usedIds = [];

    foreach ($categories as $cat) {
        $cat = strtolower(trim($cat));
        $pool = $db[$cat] ?? $db['sunsets']; // fallback to sunsets

        // Pick a random image from this category, avoid duplicates
        shuffle($pool);
        $picked = null;
        foreach ($pool as $img) {
            if (!in_array($img['id'], $usedIds)) {
                $picked = $img;
                $usedIds[] = $img['id'];
                break;
            }
        }
        if (!$picked) $picked = $pool[0];

        $images[] = [
            'url' => "https://images.pexels.com/photos/{$picked['id']}/pexels-photo-{$picked['id']}.jpeg?auto=compress&cs=tinysrgb&w=1280&h=720&fit=crop",
            'alt' => $picked['alt'],
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

// Fetch images based on AI-selected categories
$images = getImagesByCategory($aiResult['imageCategories'] ?? ['sunsets', 'mountains', 'rivers', 'forests', 'fields']);

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
