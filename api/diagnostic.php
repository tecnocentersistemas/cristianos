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
$audioSongsDir = __DIR__ . '/../media/audio/songs/';
$videoDir = __DIR__ . '/../media/videos/';
$result['audio_files_root'] = array_map('basename', glob($audioDir . '*.mp3'));
$result['audio_files_songs'] = array_map('basename', glob($audioSongsDir . '*'));
$result['video_files'] = array_map('basename', glob($videoDir . '*.mp4'));
$result['audio_songs_dir_exists'] = is_dir($audioSongsDir);

// 5. Check local audio/video for each song
foreach ($result['songs_details'] as &$song) {
    $sid = $song['id'];
    if ($sid !== 'N/A') {
        $song['hasLocalAudio'] = file_exists($audioSongsDir . $sid . '.mp3');
        $song['hasLocalVideo'] = file_exists($videoDir . $sid . '.mp4');
    }
}
unset($song);

// 6. Read ALL suno-cache files - show top-level keys and audio URLs
$result['cache_details'] = [];
foreach ($cacheFiles as $cf) {
    $raw = file_get_contents($cf);
    $data = json_decode($raw, true);
    $info = [
        'file' => basename($cf),
        'size_bytes' => strlen($raw),
        'top_keys' => $data ? array_keys($data) : ['PARSE_ERROR'],
    ];
    if ($data) {
        // Look for audio URLs in various possible structures
        if (isset($data['data'])) {
            $info['has_data_key'] = true;
            if (is_array($data['data'])) {
                $info['data_keys'] = array_keys($data['data']);
                // Check if data contains clips array
                if (isset($data['data']['clips'])) {
                    $info['clips_count'] = count($data['data']['clips']);
                    foreach ($data['data']['clips'] as $i => $clip) {
                        $info['clips'][$i] = [
                            'id' => $clip['id'] ?? 'N/A',
                            'audio_url' => isset($clip['audio_url']) ? substr($clip['audio_url'], 0, 120) : 'NONE',
                            'title' => $clip['title'] ?? 'N/A',
                            'status' => $clip['status'] ?? 'N/A',
                        ];
                    }
                }
                if (isset($data['data'][0])) {
                    $info['data_is_array'] = true;
                    $info['first_item_keys'] = is_array($data['data'][0]) ? array_keys($data['data'][0]) : 'scalar';
                    if (isset($data['data'][0]['audio_url'])) {
                        $info['first_audio_url'] = substr($data['data'][0]['audio_url'], 0, 120);
                    }
                }
            }
        }
        // Direct clips
        if (isset($data['clips'])) {
            $info['direct_clips_count'] = count($data['clips']);
        }
        // Task ID variants
        $info['task_id'] = $data['task_id'] ?? ($data['id'] ?? ($data['taskId'] ?? 'N/A'));
        $info['status'] = $data['status'] ?? ($data['data']['status'] ?? 'N/A');
    }
    $result['cache_details'][] = $info;
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
