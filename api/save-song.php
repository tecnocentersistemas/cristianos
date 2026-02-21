<?php
// Save Suno song to VPS for persistence + serve saved songs
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$songsDir = __DIR__ . '/../media/audio/songs';
if (!is_dir($songsDir)) mkdir($songsDir, 0755, true);
$metaDir = __DIR__ . '/../data/songs';
if (!is_dir($metaDir)) mkdir($metaDir, 0755, true);

// GET: serve song info
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id'] ?? '');
    if (!$id) { echo json_encode(['error'=>'id required']); exit; }
    $metaFile = $metaDir . '/' . $id . '.json';
    if (!file_exists($metaFile)) { echo json_encode(['error'=>'not found']); exit; }
    echo file_get_contents($metaFile);
    exit;
}

// POST: save a song
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$input = json_decode(file_get_contents('php://input'), true);
$audioUrl = $input['audioUrl'] ?? '';
$title = $input['title'] ?? 'FaithTunes Song';
$lyrics = $input['lyrics'] ?? '';
$tags = $input['tags'] ?? '';
$duration = $input['duration'] ?? 0;
$imageUrl = $input['imageUrl'] ?? '';
$taskId = $input['taskId'] ?? '';

if (!$audioUrl) { echo json_encode(['error'=>'audioUrl required']); exit; }

// Generate a short unique ID
$id = substr(md5($taskId ?: $audioUrl), 0, 10);

// Download audio to VPS
$localFile = $songsDir . '/' . $id . '.mp3';
if (!file_exists($localFile)) {
    $ch = curl_init($audioUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $audio = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err || !$audio) { echo json_encode(['error'=>'Failed to download audio']); exit; }
    file_put_contents($localFile, $audio);
}

// Download cover image if available
$localImage = '';
if ($imageUrl) {
    $imgFile = $songsDir . '/' . $id . '.jpg';
    if (!file_exists($imgFile)) {
        $ch = curl_init($imageUrl);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_TIMEOUT=>30, CURLOPT_SSL_VERIFYPEER=>false]);
        $img = curl_exec($ch);
        curl_close($ch);
        if ($img) file_put_contents($imgFile, $img);
    }
    if (file_exists($imgFile)) $localImage = '/media/audio/songs/' . $id . '.jpg';
}

// Build public URLs
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'cristianos.centralchat.pro';
$baseUrl = $protocol . '://' . $host;
$publicAudio = $baseUrl . '/media/audio/songs/' . $id . '.mp3';
$publicImage = $localImage ? $baseUrl . $localImage : '';
$shareUrl = $baseUrl . '/share.php?id=' . $id;

// Save metadata
$meta = [
    'id' => $id,
    'title' => $title,
    'lyrics' => $lyrics,
    'tags' => $tags,
    'duration' => $duration,
    'audioUrl' => $publicAudio,
    'imageUrl' => $publicImage,
    'shareUrl' => $shareUrl,
    'createdAt' => date('Y-m-d H:i:s'),
    'taskId' => $taskId
];
file_put_contents($metaDir . '/' . $id . '.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success'=>true, 'song'=>$meta], JSON_UNESCAPED_UNICODE);
