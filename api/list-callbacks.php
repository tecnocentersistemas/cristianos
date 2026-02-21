<?php
header('Content-Type: application/json');
$dir = __DIR__ . '/../data/suno-cache';
$files = glob($dir . '/*.json');
$result = [];
foreach ($files as $f) {
    $name = basename($f);
    $size = filesize($f);
    $d = json_decode(file_get_contents($f), true);
    $type = $d['data']['callbackType'] ?? $d['callbackType'] ?? 'unknown';
    $title = '';
    $songs = $d['data']['data'] ?? $d['data']['response']['sunoData'] ?? [];
    if (is_array($songs) && !empty($songs[0]['title'])) $title = $songs[0]['title'];
    $result[] = ['file'=>$name, 'size'=>$size, 'type'=>$type, 'title'=>$title];
}
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
