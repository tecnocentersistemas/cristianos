<?php
// Suno API - Generate real sung songs
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

function loadKey($name) {
    $paths = [__DIR__.'/../.'.$name, '/var/www/cristianos/.'.$name];
    foreach ($paths as $p) { if (file_exists($p)) { $k = trim(file_get_contents($p)); if ($k) return $k; } }
    return getenv(strtoupper(str_replace('-','_',$name))) ?: null;
}

$sunoKey = loadKey('suno-key');
if (!$sunoKey) { http_response_code(500); echo json_encode(['error'=>'No Suno API key configured']); exit; }

$cacheDir = __DIR__ . '/../data/suno-cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// ===== GET: Check task status =====
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $taskId = $_GET['taskId'] ?? '';
    if (!$taskId) { echo json_encode(['error'=>'taskId required']); exit; }

    $cacheFile = $cacheDir . '/' . $taskId . '.json';
    if (!file_exists($cacheFile)) {
        echo json_encode(['status'=>'processing','taskId'=>$taskId]);
        exit;
    }

    $data = json_decode(file_get_contents($cacheFile), true);
    if (!$data) {
        echo json_encode(['status'=>'processing','taskId'=>$taskId]);
        exit;
    }

    // Extract song data from Suno callback
    // Suno callback structure: { code, msg, data: { taskId, callBackUrl, data: [ { id, audioUrl, ... } ] } }
    $songs = $data['data']['data'] ?? $data['data'] ?? [];
    if (is_array($songs) && !empty($songs)) {
        $result = [];
        foreach ($songs as $song) {
            if (is_array($song) && isset($song['audioUrl'])) {
                $result[] = [
                    'id' => $song['id'] ?? '',
                    'audioUrl' => $song['audioUrl'] ?? '',
                    'streamAudioUrl' => $song['streamAudioUrl'] ?? '',
                    'imageUrl' => $song['imageUrl'] ?? '',
                    'title' => $song['title'] ?? '',
                    'tags' => $song['tags'] ?? '',
                    'duration' => $song['duration'] ?? 0,
                    'createTime' => $song['createTime'] ?? '',
                ];
            }
        }
        if (!empty($result)) {
            echo json_encode(['status'=>'complete','taskId'=>$taskId,'songs'=>$result]);
            exit;
        }
    }

    // If we have data but no songs yet, it might be an error or still processing
    $code = $data['code'] ?? null;
    $msg = $data['msg'] ?? '';
    if ($code && $code !== 200) {
        echo json_encode(['status'=>'error','taskId'=>$taskId,'error'=>$msg]);
        exit;
    }

    echo json_encode(['status'=>'processing','taskId'=>$taskId,'raw'=>$data]);
    exit;
}

// ===== POST: Start generation =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';
$style = $input['style'] ?? '';
$title = $input['title'] ?? '';
$customMode = !empty($style);

if (!$prompt && !$title) { http_response_code(400); echo json_encode(['error'=>'Prompt or title required']); exit; }

// Determine callback URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'cristianos.centralchat.pro';
$callbackUrl = $protocol . '://' . $host . '/api/suno-callback.php';

// Build Suno API request
$sunoPayload = [
    'callBackUrl' => $callbackUrl,
    'model' => 'V3_5', // Cheapest model for testing
];

if ($customMode && $style) {
    // Custom mode: provide lyrics + style tag
    $sunoPayload['customMode'] = true;
    $sunoPayload['instrumental'] = false;
    $sunoPayload['prompt'] = $prompt; // Lyrics
    $sunoPayload['style'] = $style;   // Style tag like "country, christian, worship"
    if ($title) $sunoPayload['title'] = $title;
} else {
    // Simple mode: just a description
    $sunoPayload['customMode'] = false;
    $sunoPayload['instrumental'] = false;
    $sunoPayload['prompt'] = $prompt;
}

$ch = curl_init('https://apibox.erweima.ai/api/v1/generate');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($sunoPayload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $sunoKey
    ],
    CURLOPT_TIMEOUT => 30
]);
$resp = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) { http_response_code(500); echo json_encode(['error'=>'Curl error: '.$err]); exit; }

$result = json_decode($resp, true);
if (!$result || ($result['code'] ?? 0) !== 200) {
    http_response_code(500);
    echo json_encode(['error'=>$result['msg'] ?? 'Suno API error','raw'=>$result]);
    exit;
}

$taskId = $result['data']['taskId'] ?? '';
echo json_encode([
    'success' => true,
    'taskId' => $taskId,
    'message' => 'Song generation started. It takes 1-3 minutes.',
    'pollUrl' => '/api/suno-generate.php?taskId=' . $taskId
]);
