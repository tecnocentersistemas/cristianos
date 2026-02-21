<?php
// Save Suno song + generate video + list all songs
set_time_limit(180); // Allow up to 3 min for FFmpeg
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$songsDir = __DIR__ . '/../media/audio/songs';
$videosDir = __DIR__ . '/../media/videos';
$imgsDir = __DIR__ . '/../media/images/slides';
$metaDir = __DIR__ . '/../data/songs';
foreach ([$songsDir, $videosDir, $imgsDir, $metaDir] as $d) { if (!is_dir($d)) mkdir($d, 0755, true); }

$host = $_SERVER['HTTP_HOST'] ?? 'cristianos.centralchat.pro';
$baseUrl = 'https://' . $host;

// ===== GET: list all songs or get one =====
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id'] ?? '');
    $action = $_GET['action'] ?? '';

    // List all songs
    if ($action === 'list') {
        $songs = [];
        $files = glob($metaDir . '/*.json');
        if ($files) {
            // Sort by newest first
            usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
            foreach ($files as $f) {
                $s = json_decode(file_get_contents($f), true);
                if ($s && !empty($s['id'])) $songs[] = $s;
            }
        }
        echo json_encode(['songs' => $songs], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get single song
    if (!$id) { echo json_encode(['error'=>'id required']); exit; }
    $metaFile = $metaDir . '/' . $id . '.json';
    if (!file_exists($metaFile)) { echo json_encode(['error'=>'not found']); exit; }
    echo file_get_contents($metaFile);
    exit;
}

// ===== POST: save a song =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$input = json_decode(file_get_contents('php://input'), true);
$audioUrl = $input['audioUrl'] ?? '';
$title = $input['title'] ?? 'FaithTunes Song';
$lyrics = $input['lyrics'] ?? '';
$tags = $input['tags'] ?? '';
$duration = floatval($input['duration'] ?? 0);
$imageUrl = $input['imageUrl'] ?? '';
$taskId = $input['taskId'] ?? '';
$slideImages = $input['slideImages'] ?? []; // Array of image URLs from slideshow
$creator = $input['creator'] ?? '';

if (!$audioUrl) { echo json_encode(['error'=>'audioUrl required']); exit; }

// Unique ID
$id = substr(md5($taskId ?: $audioUrl . time()), 0, 10);

// 1) Download audio
$audioFile = $songsDir . '/' . $id . '.mp3';
if (!file_exists($audioFile)) {
    $data = dlFile($audioUrl);
    if (!$data) { echo json_encode(['error'=>'Failed to download audio']); exit; }
    file_put_contents($audioFile, $data);
}

// 2) Download cover image
$coverPath = '';
// Use provided imageUrl OR first slide image as cover
$coverSource = $imageUrl;
if (!$coverSource && !empty($slideImages[0])) $coverSource = $slideImages[0];
if ($coverSource) {
    $coverFile = $songsDir . '/' . $id . '_cover.jpg';
    if (!file_exists($coverFile)) {
        $img = dlFile($coverSource);
        if ($img) file_put_contents($coverFile, $img);
    }
    if (file_exists($coverFile)) $coverPath = '/media/audio/songs/' . $id . '_cover.jpg';
}

// 3) Download slide images for video generation
$localSlides = [];
foreach ($slideImages as $idx => $imgUrl) {
    if (!$imgUrl || !is_string($imgUrl)) continue;
    $slideFile = $imgsDir . '/' . $id . '_' . $idx . '.jpg';
    if (!file_exists($slideFile)) {
        $img = dlFile($imgUrl);
        if ($img) file_put_contents($slideFile, $img);
    }
    if (file_exists($slideFile)) $localSlides[] = $slideFile;
}

// 4) Generate video with FFmpeg in BACKGROUND (non-blocking)
$videoPath = '';
$videoFile = $videosDir . '/' . $id . '.mp4';
if (!file_exists($videoFile) && count($localSlides) >= 2 && file_exists($audioFile)) {
    launchVideoGeneration($localSlides, $audioFile, $videoFile, $duration, $id, $metaDir);
}
if (file_exists($videoFile)) $videoPath = '/media/videos/' . $id . '.mp4';

// 5) Build metadata
// Use first slide image as og:image if cover is too small/corrupt
$ogImage = $coverPath;
if ($coverPath && file_exists(__DIR__ . '/..' . $coverPath)) {
    $sz = filesize(__DIR__ . '/..' . $coverPath);
    if ($sz < 5000) $ogImage = ''; // corrupt cover, skip it
}
if (!$ogImage && !empty($slideImages[0])) {
    // Use first Pexels slide image directly (stable URL)
    $ogImage = $slideImages[0];
} elseif ($ogImage) {
    $ogImage = $baseUrl . $ogImage;
}

$meta = [
    'id' => $id,
    'title' => $title,
    'lyrics' => $lyrics,
    'tags' => $tags,
    'duration' => $duration,
    'audioUrl' => $baseUrl . '/media/audio/songs/' . $id . '.mp3',
    'videoUrl' => $videoPath ? $baseUrl . $videoPath : '',
    'imageUrl' => $ogImage ?: ($coverPath ? $baseUrl . $coverPath : ''),
    'shareUrl' => $baseUrl . '/share.php?id=' . $id,
    'slideImages' => $slideImages,
    'creator' => $creator,
    'createdAt' => date('Y-m-d H:i:s'),
    'taskId' => $taskId
];
file_put_contents($metaDir . '/' . $id . '.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success'=>true, 'song'=>$meta], JSON_UNESCAPED_UNICODE);

// ===== Helper functions =====
function dlFile($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_TIMEOUT=>60, CURLOPT_SSL_VERIFYPEER=>false]);
    $data = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
    return ($err || !$data) ? null : $data;
}

function launchVideoGeneration($slides, $audioFile, $outputFile, $duration, $id, $metaDir) {
    $ffmpeg = trim(shell_exec('which ffmpeg 2>/dev/null') ?: '');
    if (!$ffmpeg) return;

    // Get REAL audio duration from file (not from API which may be wrong)
    $ffprobe = trim(shell_exec('which ffprobe 2>/dev/null') ?: '');
    if ($ffprobe) {
        $probeDur = trim(shell_exec($ffprobe . ' -v error -show_entries format=duration -of csv=p=0 ' . escapeshellarg($audioFile) . ' 2>/dev/null') ?: '');
        $realDuration = floatval($probeDur);
        if ($realDuration > 0) $duration = $realDuration;
    }
    if ($duration <= 0) $duration = 180; // fallback 3 min

    $numSlides = count($slides);
    $durPerSlide = $duration / $numSlides;
    $durPerSlide = max(5, min(30, $durPerSlide));

    // Recalculate total to ensure slides cover FULL audio
    $totalSlidesDur = $durPerSlide * $numSlides;
    if ($totalSlidesDur < $duration && $numSlides > 0) {
        $durPerSlide = ceil($duration / $numSlides * 10) / 10; // round up
    }

    // Create a concat file
    $listFile = dirname($outputFile) . '/' . basename($outputFile, '.mp4') . '_list.txt';
    $listContent = '';
    foreach ($slides as $slide) {
        $listContent .= "file " . escapeshellarg($slide) . "\nduration " . round($durPerSlide, 1) . "\n";
    }
    $listContent .= "file " . escapeshellarg($slides[count($slides)-1]) . "\n";
    file_put_contents($listFile, $listContent);

    // Build FFmpeg command — NO -shortest, audio determines total length
    $cmd = $ffmpeg . ' -f concat -safe 0 -i ' . escapeshellarg($listFile)
        . ' -i ' . escapeshellarg($audioFile)
        . ' -vf "scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2,setsar=1"'
        . ' -c:v libx264 -preset fast -crf 23 -r 24 -pix_fmt yuv420p'
        . ' -c:a aac -b:a 192k -movflags +faststart'
        . ' -y ' . escapeshellarg($outputFile) . ' 2>/dev/null';

    // Create a wrapper script that generates video + updates metadata
    $baseUrl = 'https://cristianos.centralchat.pro';
    $updateScript = dirname($outputFile) . '/' . $id . '_gen.sh';
    $metaFile = $metaDir . '/' . $id . '.json';
    $scriptContent = "#!/bin/bash\n" . $cmd . "\n";
    // After video is generated, extract thumbnail frame + update metadata
    $thumbFile = dirname($audioFile) . '/' . $id . '_cover.jpg';
    $scriptContent .= "if [ -f " . escapeshellarg($outputFile) . " ]; then\n";
    // Extract frame at 5 seconds as thumbnail
    $scriptContent .= "  " . $ffmpeg . " -i " . escapeshellarg($outputFile) . " -ss 5 -vframes 1 -q:v 2 -y " . escapeshellarg($thumbFile) . " 2>/dev/null\n";
    $scriptContent .= "  php -r '\$f=\"" . addslashes($metaFile) . "\";\$m=json_decode(file_get_contents(\$f),true);\$m[\"videoUrl\"]=\"" . $baseUrl . "/media/videos/" . $id . ".mp4\";if(file_exists(\"" . addslashes($thumbFile) . "\")&&filesize(\"" . addslashes($thumbFile) . "\")>5000){\$m[\"imageUrl\"]=\"" . $baseUrl . "/media/audio/songs/" . $id . "_cover.jpg?v=\".time();}file_put_contents(\$f,json_encode(\$m,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));'\n";
    $scriptContent .= "fi\n";
    $scriptContent .= "rm -f " . escapeshellarg($listFile) . " " . escapeshellarg($updateScript) . "\n";
    file_put_contents($updateScript, $scriptContent);
    chmod($updateScript, 0755);

    // Launch in background - returns immediately
    shell_exec('nohup bash ' . escapeshellarg($updateScript) . ' > /dev/null 2>&1 &');
}
