<?php
// Get timestamped lyrics from Suno API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

function loadKey($name) {
    $paths = [__DIR__.'/../.'.$name, '/var/www/cristianos/.'.$name];
    foreach ($paths as $p) { if (file_exists($p)) { $k = trim(file_get_contents($p)); if ($k) return $k; } }
    return getenv(strtoupper(str_replace('-','_',$name))) ?: null;
}

$sunoKey = loadKey('suno-key');
if (!$sunoKey) { echo json_encode(['error'=>'No API key']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$taskId = $input['taskId'] ?? '';
$audioId = $input['audioId'] ?? '';

if (!$taskId || !$audioId) {
    echo json_encode(['error'=>'taskId and audioId required']);
    exit;
}

$ch = curl_init('https://apibox.erweima.ai/api/v1/generate/get-timestamped-lyrics');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['taskId' => $taskId, 'audioId' => $audioId]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $sunoKey],
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => false
]);
$resp = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $code !== 200) {
    echo json_encode(['error' => 'API error: ' . ($err ?: $code)]);
    exit;
}

$data = json_decode($resp, true);
if (!$data || ($data['code'] ?? 0) !== 200) {
    echo json_encode(['error' => $data['msg'] ?? 'Unknown error']);
    exit;
}

// Parse aligned words into lines with timestamps
$words = $data['data']['alignedWords'] ?? [];
$lines = [];
$currentLine = '';
$lineStart = null;
$lineEnd = 0;

foreach ($words as $w) {
    $word = $w['word'] ?? '';
    $start = $w['startS'] ?? 0;
    $end = $w['endS'] ?? 0;

    if ($lineStart === null) $lineStart = $start;
    $lineEnd = $end;

    // Check if word ends with newline
    if (strpos($word, "\n") !== false) {
        $currentLine .= str_replace("\n", '', $word);
        $currentLine = trim($currentLine);
        if ($currentLine !== '') {
            $lines[] = ['text' => $currentLine, 'startS' => $lineStart, 'endS' => $lineEnd];
        }
        $currentLine = '';
        $lineStart = null;
    } else {
        $currentLine .= $word;
    }
}
// Last line
$currentLine = trim($currentLine);
if ($currentLine !== '') {
    $lines[] = ['text' => $currentLine, 'startS' => $lineStart, 'endS' => $lineEnd];
}

// Build full lyrics text
$fullLyrics = implode("\n", array_map(function($l) { return $l['text']; }, $lines));

echo json_encode([
    'success' => true,
    'lyrics' => $fullLyrics,
    'lines' => $lines
], JSON_UNESCAPED_UNICODE);
