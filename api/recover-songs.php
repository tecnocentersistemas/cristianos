<?php
// ONE-TIME script: Recover all songs from suno-cache into data/songs
set_time_limit(300);
header('Content-Type: application/json; charset=utf-8');

$cacheDir = __DIR__ . '/../data/suno-cache';
$metaDir  = __DIR__ . '/../data/songs';
$songsDir = __DIR__ . '/../media/audio/songs';
$videosDir = __DIR__ . '/../media/videos';
$imgsDir  = __DIR__ . '/../media/images/slides';
foreach ([$metaDir, $songsDir, $videosDir, $imgsDir] as $d) { if (!is_dir($d)) mkdir($d, 0755, true); }

$baseUrl = 'https://yeshuacristiano.com';

// Already saved songs (by taskId) - skip duplicates
$existingTasks = [];
foreach (glob($metaDir . '/*.json') as $f) {
    $s = json_decode(file_get_contents($f), true);
    if ($s && !empty($s['taskId'])) $existingTasks[$s['taskId']] = $s['id'];
}

function dlFile($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_TIMEOUT=>60, CURLOPT_SSL_VERIFYPEER=>false]);
    $data = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
    return ($err || !$data) ? null : $data;
}

$recovered = [];
$skipped = [];
$errors = [];

// Scan ALL cache files
$files = glob($cacheDir . '/*.json');
foreach ($files as $f) {
    $basename = basename($f, '.json');
    // Skip non-song files
    if ($basename === 'last_callback' || $basename === 'test123') continue;
    
    $raw = json_decode(file_get_contents($f), true);
    if (!$raw) continue;
    
    // Extract taskId from filename
    $taskId = preg_replace('/(_complete|_first|_text)$/', '', $basename);
    
    // Skip if already saved
    if (isset($existingTasks[$taskId])) {
        $skipped[] = $taskId;
        continue;
    }
    
    // Try to find song data in various structures
    $songsRaw = $raw['data']['response']['sunoData']
        ?? $raw['data']['data']
        ?? $raw['response']['sunoData']
        ?? $raw['data'] ?? [];
    
    if (!is_array($songsRaw)) continue;
    
    // If it's a single song object (has audioUrl directly)
    if (isset($songsRaw['audioUrl']) || isset($songsRaw['audio_url'])) {
        $songsRaw = [$songsRaw];
    }
    
    // Also check if songs are nested in 'response'
    if (empty($songsRaw) && isset($raw['response'])) {
        $songsRaw = $raw['response']['sunoData'] ?? $raw['response']['data'] ?? [];
        if (isset($songsRaw['audioUrl']) || isset($songsRaw['audio_url'])) {
            $songsRaw = [$songsRaw];
        }
    }
    
    foreach ($songsRaw as $song) {
        if (!is_array($song)) continue;
        $audioUrl = $song['audioUrl'] ?? $song['audio_url'] ?? '';
        $streamUrl = $song['streamAudioUrl'] ?? $song['stream_audio_url'] ?? '';
        $srcUrl = $audioUrl ?: $streamUrl;
        if (!$srcUrl) continue;
        
        $title = $song['title'] ?? 'FaithTunes Song';
        $tags = $song['tags'] ?? '';
        $lyrics = $song['prompt'] ?? '';
        $duration = floatval($song['duration'] ?? 0);
        $imageUrl = $song['imageUrl'] ?? $song['image_url'] ?? '';
        $songId = $song['id'] ?? '';
        
        // Derive genre from tags
        $genre = '';
        $tagsLower = strtolower($tags);
        foreach (['country','rock','gospel','folk','worship','ballad'] as $g) {
            if (strpos($tagsLower, $g) !== false) { $genre = $g; break; }
        }
        
        // Unique ID from taskId
        $id = substr(md5($taskId . $songId), 0, 10);
        
        // Skip if this ID already exists
        if (file_exists($metaDir . '/' . $id . '.json')) {
            $skipped[] = $taskId . '/' . $songId;
            continue;
        }
        
        // Download audio
        $audioFile = $songsDir . '/' . $id . '.mp3';
        if (!file_exists($audioFile)) {
            $data = dlFile($srcUrl);
            if (!$data || strlen($data) < 10000) {
                $errors[] = ['taskId'=>$taskId, 'title'=>$title, 'error'=>'Audio download failed or too small', 'url'=>substr($srcUrl,0,80)];
                continue;
            }
            file_put_contents($audioFile, $data);
        }
        
        // Download cover
        $coverPath = '';
        if ($imageUrl) {
            $coverFile = $songsDir . '/' . $id . '_cover.jpg';
            if (!file_exists($coverFile)) {
                $img = dlFile($imageUrl);
                if ($img && strlen($img) > 5000) file_put_contents($coverFile, $img);
            }
            if (file_exists($coverFile)) $coverPath = $baseUrl . '/media/audio/songs/' . $id . '_cover.jpg';
        }
        
        // Try to load slide images from _text cache file
        $slideImages = [];
        $textFile = $cacheDir . '/' . $taskId . '_text.json';
        if (file_exists($textFile)) {
            $textData = json_decode(file_get_contents($textFile), true);
            if ($textData && !empty($textData['slideImages'])) {
                $slideImages = $textData['slideImages'];
            }
        }
        
        // Build metadata
        $meta = [
            'id' => $id,
            'title' => $title,
            'lyrics' => $lyrics,
            'tags' => $tags,
            'genre' => $genre,
            'duration' => $duration,
            'audioUrl' => $baseUrl . '/media/audio/songs/' . $id . '.mp3',
            'videoUrl' => '',
            'imageUrl' => $coverPath ?: ($imageUrl ?: ''),
            'shareUrl' => $baseUrl . '/share.php?id=' . $id,
            'slideImages' => $slideImages,
            'creator' => '',
            'createdAt' => date('Y-m-d H:i:s', filemtime($f)),
            'taskId' => $taskId
        ];
        
        file_put_contents($metaDir . '/' . $id . '.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $existingTasks[$taskId] = $id;
        $recovered[] = ['id'=>$id, 'title'=>$title, 'taskId'=>$taskId];
    }
}

echo json_encode([
    'recovered' => count($recovered),
    'skipped' => count($skipped),
    'errors' => count($errors),
    'songs' => $recovered,
    'errorDetails' => $errors
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
