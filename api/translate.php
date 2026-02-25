<?php
// FaithTunes - Translate lyrics using OpenAI
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }

function loadKey($name) {
    $paths = [__DIR__.'/../.'.$name, '/var/www/cristianos/.'.$name];
    foreach ($paths as $p) { if (file_exists($p)) { $k = trim(file_get_contents($p)); if ($k) return $k; } }
    return getenv(strtoupper(str_replace('-','_',$name))) ?: null;
}

$apiKey = loadKey('openai-key');
if (!$apiKey) { http_response_code(500); echo json_encode(['error'=>'No API key']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$text = $input['text'] ?? '';
$targetLang = $input['lang'] ?? 'en';

if (!$text || strlen($text) < 5) { echo json_encode(['error'=>'No text']); exit; }
if (strlen($text) > 5000) { echo json_encode(['error'=>'Text too long']); exit; }

$langNames = [
    'es'=>'Spanish','en'=>'English','pt'=>'Portuguese','de'=>'German','fr'=>'French',
    'it'=>'Italian','pl'=>'Polish','ru'=>'Russian','uk'=>'Ukrainian','sv'=>'Swedish',
    'fi'=>'Finnish','nb'=>'Norwegian','lv'=>'Latvian','sl'=>'Slovenian',
    'ja'=>'Japanese','ko'=>'Korean','zh'=>'Chinese','ar'=>'Arabic','fa'=>'Persian'
];
$langName = $langNames[$targetLang] ?? 'English';

// Check cache
$cacheDir = __DIR__ . '/../data/translate-cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
$cacheKey = md5($text . '|' . $targetLang);
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
if (file_exists($cacheFile)) {
    $cached = json_decode(file_get_contents($cacheFile), true);
    if ($cached && !empty($cached['translated'])) {
        echo json_encode(['success'=>true, 'translated'=>$cached['translated'], 'cached'=>true]);
        exit;
    }
}

$prompt = "Translate the following song lyrics to {$langName}. Keep the structure (verse, chorus markers like [Verse], [Chorus] etc). Only return the translated lyrics, nothing else.\n\n{$text}";

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role'=>'system', 'content'=>'You are a professional song lyrics translator. Translate accurately, preserving poetic style and structure markers.'],
            ['role'=>'user', 'content'=>$prompt]
        ],
        'max_tokens' => 2000,
        'temperature' => 0.3
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_TIMEOUT => 30
]);
$resp = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) { echo json_encode(['error'=>'Translation failed']); exit; }

$data = json_decode($resp, true);
$translated = $data['choices'][0]['message']['content'] ?? '';

if (!$translated) { echo json_encode(['error'=>'Empty translation']); exit; }

// Cache result
file_put_contents($cacheFile, json_encode(['translated'=>$translated, 'lang'=>$targetLang, 'ts'=>time()]));

echo json_encode(['success'=>true, 'translated'=>$translated], JSON_UNESCAPED_UNICODE);
