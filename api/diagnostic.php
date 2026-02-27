<?php
// Diagnostic script - temporary, remove after use
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$result = [];

// 1. Count and list suno-cache files
$cacheDir = __DIR__ . '/../data/suno-cache/';
$cacheFiles = glob($cacheDir . '*.json');
$result['suno_cache_count'] = count($cacheFiles);
$result['suno_cache_files'] = array_map('basename', $cacheFiles);

// 2. Count and list songs files
$songsDir = __DIR__ . '/../data/songs/';
$songFiles = glob($songsDir . '*.json');
$result['songs_count'] = count($songFiles);
$result['songs_files'] = array_map('basename', $songFiles);

// 3. Read a sample of songs to check for duplicates and missing data
$result['songs_details'] = [];
foreach ($songFiles as $f) {
    $data = json_decode(file_get_contents($f), true);
    if ($data) {
        $result['songs_details'][] = [
            'file' => basename($f),
            'id' => $data['id'] ?? 'N/A',
            'title' => $data['title'] ?? 'N/A',
            'taskId' => $data['taskId'] ?? ($data['sunoTaskId'] ?? 'N/A'),
            'audioUrl' => !empty($data['audioUrl']) ? substr($data['audioUrl'], 0, 80) : 'EMPTY',
            'videoUrl' => !empty($data['videoUrl']) ? substr($data['videoUrl'], 0, 80) : 'EMPTY',
            'imageUrl' => !empty($data['imageUrl']) ? substr($data['imageUrl'], 0, 80) : 'EMPTY',
            'genre' => $data['genre'] ?? 'N/A',
            'hasLocalAudio' => false,
            'hasLocalVideo' => false,
        ];
    }
}

// 4. Check which audio/video files exist
$audioDir = __DIR__ . '/../media/audio/';
$videoDir = __DIR__ . '/../media/videos/';
$result['audio_files'] = array_map('basename', glob($audioDir . '*.mp3'));
$result['video_files'] = array_map('basename', glob($videoDir . '*.mp4'));

// 5. Check local audio/video for each song
foreach ($result['songs_details'] as &$song) {
    $sid = $song['id'];
    if ($sid !== 'N/A') {
        $song['hasLocalAudio'] = file_exists($audioDir . $sid . '.mp3');
        $song['hasLocalVideo'] = file_exists($videoDir . $sid . '.mp4');
    }
}
unset($song);

// 6. Read first 2 suno-cache files as samples
$result['cache_samples'] = [];
$sampleCount = 0;
foreach ($cacheFiles as $cf) {
    if ($sampleCount >= 3) break;
    $data = json_decode(file_get_contents($cf), true);
    if ($data) {
        // Truncate large fields
        if (isset($data['clips'])) {
            foreach ($data['clips'] as &$clip) {
                if (isset($clip['audio_url'])) $clip['audio_url'] = substr($clip['audio_url'], 0, 100);
                if (isset($clip['video_url'])) $clip['video_url'] = substr($clip['video_url'], 0, 100);
                if (isset($clip['image_url'])) $clip['image_url'] = substr($clip['image_url'], 0, 100);
                if (isset($clip['metadata'])) $clip['metadata'] = '(truncated)';
            }
            unset($clip);
        }
        $result['cache_samples'][] = [
            'file' => basename($cf),
            'task_id' => $data['task_id'] ?? ($data['id'] ?? 'N/A'),
            'status' => $data['status'] ?? 'N/A',
            'clips_count' => isset($data['clips']) ? count($data['clips']) : 0,
            'clips' => $data['clips'] ?? [],
        ];
    }
    $sampleCount++;
}

// 7. Check recover-songs.php exists
$result['recover_exists'] = file_exists(__DIR__ . '/recover-songs.php');

// 8. Disk space
$result['disk_free_mb'] = round(disk_free_space('/') / 1024 / 1024);

// 9. FFmpeg available
$result['ffmpeg'] = trim(shell_exec('which ffmpeg 2>/dev/null') ?? '');

// 10. Find duplicate taskIds in songs
$taskIds = [];
foreach ($result['songs_details'] as $s) {
    $tid = $s['taskId'];
    if ($tid !== 'N/A') {
        $taskIds[$tid] = ($taskIds[$tid] ?? 0) + 1;
    }
}
$result['duplicate_taskIds'] = array_filter($taskIds, fn($c) => $c > 1);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
