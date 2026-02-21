<?php
header('Content-Type: application/json');
$dir = __DIR__ . '/../data/songs';
$files = glob($dir . '/*.json');
$songs = [];
foreach ($files as $f) {
    $d = json_decode(file_get_contents($f), true);
    if ($d) $songs[] = ['id'=>$d['id']??'','title'=>$d['title']??'','tags'=>$d['tags']??''];
}
echo json_encode($songs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
