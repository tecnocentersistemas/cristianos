<?php
// API to list songs by genre, theme, or all
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$metaDir = __DIR__ . '/../data/songs';
$genre = $_GET['genre'] ?? '';
$theme = $_GET['theme'] ?? '';
$limit = min(intval($_GET['limit'] ?? 50), 200);
$offset = max(intval($_GET['offset'] ?? 0), 0);

$songs = [];
$files = glob($metaDir . '/*.json');

foreach ($files as $f) {
    $d = json_decode(file_get_contents($f), true);
    if (!$d || empty($d['id'])) continue;
    if ($genre && ($d['genre'] ?? '') !== $genre) continue;
    if ($theme && ($d['theme'] ?? '') !== $theme) continue;
    $songs[] = [
        'id' => $d['id'],
        'title' => $d['title'] ?? '',
        'genre' => $d['genre'] ?? $d['tags'] ?? '',
        'theme' => $d['theme'] ?? '',
        'mood' => $d['mood'] ?? '',
        'duration' => $d['duration'] ?? 0,
        'audioUrl' => $d['audioUrl'] ?? '',
        'videoUrl' => $d['videoUrl'] ?? '',
        'imageUrl' => $d['imageUrl'] ?? '',
        'shareUrl' => $d['shareUrl'] ?? '',
        'instrumental' => $d['instrumental'] ?? false,
        'creator' => $d['creator'] ?? '',
    ];
}

// Sort by title
usort($songs, function($a, $b) { return strcmp($a['title'], $b['title']); });

$total = count($songs);
$songs = array_slice($songs, $offset, $limit);

echo json_encode([
    'total' => $total,
    'songs' => $songs,
], JSON_UNESCAPED_UNICODE);
