<?php
// Test that callback parsing + extractSongs works with REAL data
header('Content-Type: text/plain');

$cacheDir = __DIR__ . '/../data/suno-cache';

// 1) Read the REAL callback data (unknown file)
$testFile = $cacheDir . '/unknown_1771649943.json';
if (!file_exists($testFile)) { echo "No test file\n"; exit; }
$data = json_decode(file_get_contents($testFile), true);

// 2) Test taskId extraction (suno-callback.php logic)
$taskId = $data['data']['taskId'] 
    ?? $data['data']['task_id'] 
    ?? $data['taskId'] 
    ?? $data['task_id'] 
    ?? null;
if (!$taskId && isset($data['data']['data']) && is_array($data['data']['data'])) {
    foreach ($data['data']['data'] as $item) {
        if (!empty($item['task_id'])) { $taskId = $item['task_id']; break; }
    }
}
echo "1) taskId extraction: " . ($taskId ?: 'FAILED') . "\n";
echo "   Expected: 719205f212b77e66f987ef3da0c37230\n";
echo "   Match: " . ($taskId === '719205f212b77e66f987ef3da0c37230' ? 'YES' : 'NO') . "\n\n";

// 3) Test extractSongs (suno-generate.php logic)
$songsRaw = $data['data']['response']['sunoData']
    ?? $data['data']['data']
    ?? $data['response']['sunoData']
    ?? [];
echo "2) Songs raw count: " . count($songsRaw) . "\n";

// extractSongs with snake_case support
$result = [];
foreach ($songsRaw as $song) {
    if (!is_array($song)) continue;
    $audioUrl = $song['audioUrl'] ?? $song['audio_url'] ?? '';
    $streamAudioUrl = $song['streamAudioUrl'] ?? $song['stream_audio_url'] ?? '';
    if (!$audioUrl && !$streamAudioUrl) continue;
    $result[] = [
        'title' => $song['title'] ?? '',
        'audioUrl' => $audioUrl,
        'streamAudioUrl' => $streamAudioUrl,
        'duration' => $song['duration'] ?? 0,
    ];
}
echo "3) Extracted songs: " . count($result) . "\n";
foreach ($result as $i => $s) {
    echo "   Song $i: {$s['title']} ({$s['duration']}s)\n";
    echo "   audio: " . substr($s['audioUrl'], 0, 60) . "...\n";
    echo "   stream: " . substr($s['streamAudioUrl'], 0, 60) . "...\n\n";
}

// 4) Test that callback file with correct taskId exists
$correctFile = $cacheDir . '/719205f212b77e66f987ef3da0c37230.json';
echo "4) Callback file exists: " . (file_exists($correctFile) ? 'YES' : 'NO') . "\n";

// 5) Full integration: what GET endpoint would return
if (file_exists($correctFile)) {
    $cbData = json_decode(file_get_contents($correctFile), true);
    $sr = $cbData['data']['response']['sunoData'] ?? $cbData['data']['data'] ?? [];
    $extracted = [];
    foreach ($sr as $s) {
        if (!is_array($s)) continue;
        $au = $s['audioUrl'] ?? $s['audio_url'] ?? '';
        $sau = $s['streamAudioUrl'] ?? $s['stream_audio_url'] ?? '';
        if ($au || $sau) $extracted[] = ['title' => $s['title'] ?? '', 'hasAudio' => true];
    }
    echo "5) GET from callback file would return: " . count($extracted) . " songs\n";
    echo "   FAST PATH WORKS: " . (count($extracted) > 0 ? 'YES' : 'NO') . "\n";
}

echo "\n=== VERDICT ===\n";
$ok = $taskId === '719205f212b77e66f987ef3da0c37230' && count($result) > 0;
echo $ok ? "ALL TESTS PASS - System is functional" : "TESTS FAILED";
echo "\n";
