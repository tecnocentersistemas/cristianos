<?php
// Regenerate video for a song with correct duration
$id = $_GET['id'] ?? '';
if (!$id) { echo "Need ?id=xxx"; exit; }

$baseDir = __DIR__ . '/..';
$audioFile = $baseDir . '/media/audio/songs/' . $id . '.mp3';
$videoFile = $baseDir . '/media/videos/' . $id . '.mp4';
$metaDir = $baseDir . '/data/songs';
$slidesDir = $baseDir . '/media/images/slides';

if (!file_exists($audioFile)) { echo "Audio not found"; exit; }

// Get real audio duration
$dur = floatval(trim(shell_exec('ffprobe -v error -show_entries format=duration -of csv=p=0 ' . escapeshellarg($audioFile) . ' 2>/dev/null')));
echo "Audio duration: {$dur}s\n";

// Find slides
$slides = glob($slidesDir . '/' . $id . '_*.jpg');
sort($slides);
echo "Slides: " . count($slides) . "\n";
if (count($slides) < 2) { echo "Not enough slides"; exit; }

// Delete old video
if (file_exists($videoFile)) unlink($videoFile);

// Calculate slide duration
$numSlides = count($slides);
$durPerSlide = ceil($dur / $numSlides * 10) / 10;
echo "Duration per slide: {$durPerSlide}s\n";
echo "Total slides duration: " . ($durPerSlide * $numSlides) . "s\n";

// Create concat file
$listFile = '/tmp/' . $id . '_list.txt';
$listContent = '';
foreach ($slides as $slide) {
    $listContent .= "file " . escapeshellarg($slide) . "\nduration " . $durPerSlide . "\n";
}
$listContent .= "file " . escapeshellarg($slides[count($slides)-1]) . "\n";
file_put_contents($listFile, $listContent);

// FFmpeg - NO -shortest
$cmd = 'ffmpeg -f concat -safe 0 -i ' . escapeshellarg($listFile)
    . ' -i ' . escapeshellarg($audioFile)
    . ' -vf "scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2,setsar=1"'
    . ' -c:v libx264 -preset fast -crf 23 -r 24 -pix_fmt yuv420p'
    . ' -c:a aac -b:a 192k -movflags +faststart'
    . ' -y ' . escapeshellarg($videoFile) . ' 2>&1';

echo "Generating video...\n";
$output = shell_exec($cmd);

// Extract thumbnail from video
$thumbFile = $baseDir . '/media/audio/songs/' . $id . '_cover.jpg';
if (file_exists($videoFile)) {
    shell_exec('ffmpeg -i ' . escapeshellarg($videoFile) . ' -ss 5 -vframes 1 -q:v 2 -y ' . escapeshellarg($thumbFile) . ' 2>/dev/null');
    
    $newDur = floatval(trim(shell_exec('ffprobe -v error -show_entries format=duration -of csv=p=0 ' . escapeshellarg($videoFile) . ' 2>/dev/null')));
    echo "New video duration: {$newDur}s\n";
    echo "Thumbnail: " . (file_exists($thumbFile) ? filesize($thumbFile) . " bytes" : "FAILED") . "\n";

    // Update metadata
    $metaFile = $metaDir . '/' . $id . '.json';
    $meta = json_decode(file_get_contents($metaFile), true);
    $meta['videoUrl'] = 'https://cristianos.centralchat.pro/media/videos/' . $id . '.mp4';
    $meta['duration'] = $dur;
    if (file_exists($thumbFile) && filesize($thumbFile) > 5000) {
        $meta['imageUrl'] = 'https://cristianos.centralchat.pro/media/audio/songs/' . $id . '_cover.jpg?v=' . time();
    }
    file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Metadata updated\n";
    echo "SUCCESS\n";
} else {
    echo "FAILED - FFmpeg output:\n" . substr($output, -500);
}

unlink($listFile);
