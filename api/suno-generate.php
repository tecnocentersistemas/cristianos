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

function extractSongs($songsRaw) {
    $result = [];
    if (!is_array($songsRaw)) return $result;
    foreach ($songsRaw as $song) {
        if (!is_array($song)) continue;
        // Support both camelCase (API poll) and snake_case (callback) field names
        $audioUrl = $song['audioUrl'] ?? $song['audio_url'] ?? '';
        $streamAudioUrl = $song['streamAudioUrl'] ?? $song['stream_audio_url'] ?? '';
        if (!$audioUrl && !$streamAudioUrl) continue;
        $result[] = [
            'id' => $song['id'] ?? '',
            'audioUrl' => $audioUrl,
            'streamAudioUrl' => $streamAudioUrl,
            'imageUrl' => $song['imageUrl'] ?? $song['image_url'] ?? '',
            'title' => $song['title'] ?? '',
            'tags' => $song['tags'] ?? '',
            'prompt' => $song['prompt'] ?? '',
            'duration' => $song['duration'] ?? 0,
        ];
    }
    return $result;
}

// ===== GET: Check task status =====
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $taskId = $_GET['taskId'] ?? '';
    if (!$taskId) { echo json_encode(['error'=>'taskId required']); exit; }

    // FIRST: Check if callback already received data (fastest path)
    $callbackFile = $cacheDir . '/' . $taskId . '.json';
    $firstFile = $cacheDir . '/' . $taskId . '_first.json';
    // Check _first callback too (arrives ~30-40s before _complete)
    $checkFiles = [];
    if (file_exists($callbackFile)) $checkFiles[] = $callbackFile;
    if (file_exists($firstFile)) $checkFiles[] = $firstFile;
    foreach ($checkFiles as $cf) {
        $cbData = json_decode(file_get_contents($cf), true);
        // Extract songs from callback data - try multiple structures
        $songsRaw = $cbData['data']['response']['sunoData']
            ?? $cbData['data']['data']
            ?? $cbData['response']['sunoData']
            ?? [];
        $result = extractSongs($songsRaw);
        if (!empty($result)) {
            echo json_encode(['status'=>'complete','taskId'=>$taskId,'songs'=>$result], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // FALLBACK: Poll Suno API directly
    $pollUrl = 'https://apibox.erweima.ai/api/v1/generate/record-info?taskId=' . urlencode($taskId);
    $ch = curl_init($pollUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $sunoKey],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $code !== 200) {
        echo json_encode(['status'=>'processing','taskId'=>$taskId]);
        exit;
    }

    $data = json_decode($resp, true);
    if (!$data) {
        echo json_encode(['status'=>'processing','taskId'=>$taskId]);
        exit;
    }

    // Check status
    $taskData = $data['data'] ?? [];
    $status = $taskData['status'] ?? '';

    if ($status === 'CREATE_FAIL' || !empty($taskData['errorMessage'])) {
        echo json_encode(['status'=>'error','taskId'=>$taskId,'error'=>$taskData['errorMessage'] ?? 'Generation failed']);
        exit;
    }

    // Extract songs - try even if status isn't SUCCESS yet (stream URLs available early)
    $songsRaw = $taskData['response']['sunoData'] ?? $taskData['data'] ?? [];
    $result = extractSongs($songsRaw);

    if (!empty($result)) {
        echo json_encode(['status'=>'complete','taskId'=>$taskId,'songs'=>$result], JSON_UNESCAPED_UNICODE);
    } elseif ($status === 'SUCCESS') {
        echo json_encode(['status'=>'processing','taskId'=>$taskId,'sunoStatus'=>'SUCCESS_NO_SONGS']);
    } else {
        echo json_encode(['status'=>'processing','taskId'=>$taskId,'sunoStatus'=>$status]);
    }
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

// Determine callback URL (always HTTPS)
$host = $_SERVER['HTTP_HOST'] ?? 'cristianos.centralchat.pro';
$callbackUrl = 'https://' . $host . '/api/suno-callback.php';

// Build Suno API request
$sunoPayload = [
    'callBackUrl' => $callbackUrl,
    'model' => 'V5', // V5 - fastest generation
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
