<?php
// Recover a song from suno-cache callback data
header('Content-Type: text/plain');
set_time_limit(120);

$taskId = $_GET['taskId'] ?? '';
if (!$taskId) { echo "Need ?taskId=xxx"; exit; }

$baseDir = __DIR__ . '/..';
$cacheDir = $baseDir . '/data/suno-cache';
$songsDir = $baseDir . '/media/audio/songs';
$slidesDir = $baseDir . '/media/images/slides';
$videosDir = $baseDir . '/media/videos';
$metaDir = $baseDir . '/data/songs';
$baseUrl = 'https://cristianos.centralchat.pro';

// Read callback
$cbFile = $cacheDir . '/' . $taskId . '_complete.json';
if (!file_exists($cbFile)) $cbFile = $cacheDir . '/' . $taskId . '.json';
if (!file_exists($cbFile)) { echo "Callback not found for $taskId"; exit; }

$cb = json_decode(file_get_contents($cbFile), true);
$songs = $cb['data']['data'] ?? [];
if (empty($songs)) { echo "No songs in callback"; exit; }

$song = $songs[0]; // Take first
$title = $song['title'] ?? 'Untitled';
$audioUrl = $song['audio_url'] ?? $song['audioUrl'] ?? '';
$streamUrl = $song['stream_audio_url'] ?? $song['streamAudioUrl'] ?? '';
$imageUrl = $song['image_url'] ?? $song['imageUrl'] ?? '';
$tags = $song['tags'] ?? '';
$duration = $song['duration'] ?? 0;
$prompt = $song['prompt'] ?? '';

echo "Title: $title\n";
echo "Audio: " . substr($audioUrl, 0, 60) . "\n";
echo "Duration: {$duration}s\n";
echo "Tags: $tags\n\n";

// Generate ID
$id = substr(md5($taskId), 0, 10);
$metaFile = $metaDir . '/' . $id . '.json';
if (file_exists($metaFile)) { echo "Song $id already exists!"; exit; }

// Download audio
$audioFile = $songsDir . '/' . $id . '.mp3';
echo "Downloading audio...\n";
$ch = curl_init($audioUrl ?: $streamUrl);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_TIMEOUT=>60, CURLOPT_SSL_VERIFYPEER=>false]);
$data = curl_exec($ch); curl_close($ch);
if (!$data || strlen($data) < 10000) { echo "Audio download failed"; exit; }
file_put_contents($audioFile, $data);
echo "Audio saved: " . strlen($data) . " bytes\n";

// Get real duration
$ffprobe = trim(shell_exec('which ffprobe 2>/dev/null') ?: '');
if ($ffprobe) {
    $realDur = floatval(trim(shell_exec($ffprobe . ' -v error -show_entries format=duration -of csv=p=0 ' . escapeshellarg($audioFile) . ' 2>/dev/null')));
    if ($realDur > 0) $duration = $realDur;
}
echo "Real duration: {$duration}s\n";

// Download cover from Suno
$coverFile = $songsDir . '/' . $id . '_cover.jpg';
if ($imageUrl) {
    $ch = curl_init($imageUrl);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_TIMEOUT=>15, CURLOPT_SSL_VERIFYPEER=>false]);
    $img = curl_exec($ch); curl_close($ch);
    if ($img && strlen($img) > 5000) {
        file_put_contents($coverFile, $img);
        echo "Cover saved: " . strlen($img) . " bytes\n";
    }
}

// Build imageUrl
$ogImage = '';
if (file_exists($coverFile) && filesize($coverFile) > 5000) {
    $ogImage = $baseUrl . '/media/audio/songs/' . $id . '_cover.jpg?v=' . time();
}

// Save metadata
$meta = [
    'id' => $id,
    'title' => $title,
    'lyrics' => $prompt,
    'tags' => $tags,
    'duration' => $duration,
    'audioUrl' => $baseUrl . '/media/audio/songs/' . $id . '.mp3',
    'videoUrl' => '',
    'imageUrl' => $ogImage,
    'shareUrl' => $baseUrl . '/share.php?id=' . $id,
    'slideImages' => [],
    'creator' => '',
    'createdAt' => date('Y-m-d H:i:s'),
    'taskId' => $taskId,
];
file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nSong saved as $id\n";
echo "Share: $baseUrl/share.php?id=$id\n";
echo "SUCCESS\n";
