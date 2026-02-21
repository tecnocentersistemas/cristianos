<?php
// API to list AI songs, optionally filtered by genre
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$metaDir = __DIR__ . '/../data/songs';
$genre = strtolower($_GET['genre'] ?? '');

$songs = [];
$files = glob($metaDir . '/*.json');
foreach ($files as $f) {
    $d = json_decode(file_get_contents($f), true);
    if (!$d || empty($d['id'])) continue;
    // Determine genre from tags or genre field
    $songGenre = $d['genre'] ?? '';
    if (!$songGenre && !empty($d['tags'])) {
        $tags = strtolower($d['tags']);
        foreach (['country','rock','gospel','folk','worship','ballad'] as $g) {
            if (strpos($tags, $g) !== false) { $songGenre = $g; break; }
        }
    }
    if ($genre && $songGenre !== $genre) continue;
    $songs[] = [
        'id' => $d['id'],
        'title' => $d['title'] ?? '',
        'genre' => $songGenre,
        'tags' => $d['tags'] ?? '',
        'duration' => $d['duration'] ?? 0,
        'audioUrl' => $d['audioUrl'] ?? '',
        'videoUrl' => $d['videoUrl'] ?? '',
        'imageUrl' => $d['imageUrl'] ?? '',
        'shareUrl' => $d['shareUrl'] ?? '',
        'creator' => $d['creator'] ?? '',
    ];
}

usort($songs, function($a, $b) { return strcmp($b['id'], $a['id']); }); // newest first
echo json_encode(['total' => count($songs), 'songs' => $songs], JSON_UNESCAPED_UNICODE);
