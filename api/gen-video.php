<?php
// Generate video for a song that never had one
header('Content-Type: text/plain');
set_time_limit(180);

$id = $_GET['id'] ?? '';
if (!$id) { echo "Need ?id=xxx"; exit; }

$baseDir = __DIR__ . '/..';
$metaFile = $baseDir . '/data/songs/' . $id . '.json';
if (!file_exists($metaFile)) { echo "Song not found"; exit; }

$meta = json_decode(file_get_contents($metaFile), true);
$audioFile = $baseDir . '/media/audio/songs/' . $id . '.mp3';
if (!file_exists($audioFile)) { echo "Audio not found"; exit; }

echo "Song: " . $meta['title'] . "\n";

// Get audio duration
$ffprobe = trim(shell_exec('which ffprobe 2>/dev/null'));
$duration = 0;
if ($ffprobe) {
    $duration = floatval(trim(shell_exec($ffprobe . ' -v error -show_entries format=duration -of csv=p=0 ' . escapeshellarg($audioFile))));
}
if ($duration <= 0) $duration = 180;
echo "Duration: {$duration}s\n";

// Download images from Pexels
$pexelsKey = trim(file_get_contents($baseDir . '/.pexels-key') ?: '');
$slidesDir = $baseDir . '/media/images/slides';
if (!is_dir($slidesDir)) mkdir($slidesDir, 0755, true);

$queries = ['christian worship light', 'bible open church', 'sunrise mountain landscape', 'peaceful nature river', 'golden wheat field'];
$slides = [];
$imgIdx = 0;

foreach ($queries as $q) {
    if (count($slides) >= 10) break;
    $ch = curl_init('https://api.pexels.com/v1/search?query=' . urlencode($q) . '&per_page=3&orientation=landscape');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HTTPHEADER=>['Authorization: ' . $pexelsKey], CURLOPT_TIMEOUT=>10]);
    $resp = json_decode(curl_exec($ch), true);
    curl_close($ch);
    foreach (($resp['photos'] ?? []) as $photo) {
        if (count($slides) >= 10) break;
        $url = $photo['src']['large2x'] ?? $photo['src']['large'] ?? '';
        if (!$url) continue;
        $slideFile = $slidesDir . '/' . $id . '_' . $imgIdx . '.jpg';
        if (!file_exists($slideFile)) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_TIMEOUT=>15, CURLOPT_SSL_VERIFYPEER=>false]);
            $img = curl_exec($ch); curl_close($ch);
            if ($img && strlen($img) > 5000) {
                file_put_contents($slideFile, $img);
            }
        }
        if (file_exists($slideFile) && filesize($slideFile) > 5000) {
            $slides[] = $slideFile;
            $imgIdx++;
        }
    }
}

echo "Slides: " . count($slides) . "\n";
if (count($slides) < 2) { echo "Not enough slides"; exit; }

// Calculate slide duration
$durPerSlide = ceil($duration / count($slides) * 10) / 10;
echo "Duration per slide: {$durPerSlide}s\n";

// Create concat file
$listFile = '/tmp/' . $id . '_list.txt';
$listContent = '';
foreach ($slides as $slide) {
    $listContent .= "file " . escapeshellarg($slide) . "\nduration " . $durPerSlide . "\n";
}
$listContent .= "file " . escapeshellarg($slides[count($slides)-1]) . "\n";
file_put_contents($listFile, $listContent);

// FFmpeg
$ffmpeg = trim(shell_exec('which ffmpeg 2>/dev/null'));
$videoFile = $baseDir . '/media/videos/' . $id . '.mp4';
$cmd = $ffmpeg . ' -f concat -safe 0 -i ' . escapeshellarg($listFile)
    . ' -i ' . escapeshellarg($audioFile)
    . ' -vf "scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2,setsar=1"'
    . ' -c:v libx264 -preset fast -crf 23 -r 24 -pix_fmt yuv420p'
    . ' -c:a aac -b:a 192k -movflags +faststart'
    . ' -y ' . escapeshellarg($videoFile) . ' 2>&1';

echo "Generating video...\n";
$output = shell_exec($cmd);

if (file_exists($videoFile) && filesize($videoFile) > 50000) {
    $newDur = 0;
    if ($ffprobe) $newDur = floatval(trim(shell_exec($ffprobe . ' -v error -show_entries format=duration -of csv=p=0 ' . escapeshellarg($videoFile))));
    echo "Video created: " . round(filesize($videoFile)/1024/1024, 1) . "MB, " . round($newDur) . "s\n";

    // Extract thumbnail
    $thumbFile = $baseDir . '/media/audio/songs/' . $id . '_cover.jpg';
    shell_exec($ffmpeg . ' -i ' . escapeshellarg($videoFile) . ' -ss 5 -vframes 1 -q:v 2 -y ' . escapeshellarg($thumbFile) . ' 2>/dev/null');

    // Update metadata
    $baseUrl = 'https://cristianos.centralchat.pro';
    $meta['videoUrl'] = $baseUrl . '/media/videos/' . $id . '.mp4';
    $meta['duration'] = $duration;
    $slideUrls = [];
    foreach ($slides as $s) {
        $slideUrls[] = $baseUrl . '/media/images/slides/' . basename($s);
    }
    $meta['slideImages'] = $slideUrls;
    if (file_exists($thumbFile) && filesize($thumbFile) > 5000) {
        $meta['imageUrl'] = $baseUrl . '/media/audio/songs/' . $id . '_cover.jpg?v=' . time();
    }
    file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Metadata updated\n";
    echo "Video: " . $meta['videoUrl'] . "\n";
    echo "SUCCESS\n";
} else {
    echo "FAILED\n" . substr($output, -500) . "\n";
}
unlink($listFile);
