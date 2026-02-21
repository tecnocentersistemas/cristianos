<?php
// Fix cover image for a song by downloading from Pexels
header('Content-Type: text/plain');

$id = $_GET['id'] ?? '';
if (!$id) { echo "Need ?id=xxx"; exit; }

$metaFile = __DIR__ . '/../data/songs/' . $id . '.json';
if (!file_exists($metaFile)) { echo "Song not found"; exit; }

$meta = json_decode(file_get_contents($metaFile), true);
echo "Song: " . $meta['title'] . "\n";

// Try to get cover from Suno callback
$cacheDir = __DIR__ . '/../data/suno-cache';
$taskId = $meta['taskId'] ?? '';
$imageUrl = '';

if ($taskId) {
    $cbFile = $cacheDir . '/' . $taskId . '_complete.json';
    if (!file_exists($cbFile)) $cbFile = $cacheDir . '/' . $taskId . '.json';
    if (file_exists($cbFile)) {
        $cb = json_decode(file_get_contents($cbFile), true);
        $songs = $cb['data']['data'] ?? [];
        if (!empty($songs[0])) {
            $imageUrl = $songs[0]['image_url'] ?? $songs[0]['imageUrl'] ?? '';
            echo "Suno image URL: $imageUrl\n";
        }
    }
}

// Download image
$coverFile = __DIR__ . '/../media/audio/songs/' . $id . '_cover.jpg';
$ok = false;

if ($imageUrl) {
    $ch = curl_init($imageUrl);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_TIMEOUT=>15, CURLOPT_SSL_VERIFYPEER=>false]);
    $img = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($img && strlen($img) > 5000 && $code === 200) {
        file_put_contents($coverFile, $img);
        $ok = true;
        echo "Suno cover saved: " . strlen($img) . " bytes\n";
    } else {
        echo "Suno cover download failed (code $code, size " . strlen($img ?: '') . ")\n";
    }
}

// Fallback: use Pexels
if (!$ok) {
    $pexelsKey = trim(file_get_contents(__DIR__ . '/../.pexels-key') ?: '');
    if ($pexelsKey) {
        $query = 'christian worship light';
        $ch = curl_init('https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=5&orientation=landscape');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HTTPHEADER=>['Authorization: ' . $pexelsKey], CURLOPT_TIMEOUT=>10]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $photos = $resp['photos'] ?? [];
        if (!empty($photos)) {
            $photo = $photos[array_rand($photos)];
            $pxUrl = $photo['src']['large'] ?? $photo['src']['medium'] ?? '';
            echo "Pexels URL: $pxUrl\n";
            if ($pxUrl) {
                $ch = curl_init($pxUrl);
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_TIMEOUT=>15, CURLOPT_SSL_VERIFYPEER=>false]);
                $img = curl_exec($ch);
                curl_close($ch);
                if ($img && strlen($img) > 5000) {
                    file_put_contents($coverFile, $img);
                    $ok = true;
                    echo "Pexels cover saved: " . strlen($img) . " bytes\n";
                }
            }
        }
    }
}

if ($ok) {
    $baseUrl = 'https://cristianos.centralchat.pro';
    $meta['imageUrl'] = $baseUrl . '/media/audio/songs/' . $id . '_cover.jpg?v=' . time();
    file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "\nMetadata updated with cover\n";
    echo "Image: " . $meta['imageUrl'] . "\n";
    echo "SUCCESS\n";
} else {
    echo "FAILED to get cover image\n";
}
