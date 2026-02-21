<?php
header('Content-Type: application/json');
$f = __DIR__ . '/../data/songs/678d59bb9b.json';
if (!file_exists($f)) { echo json_encode(['error'=>'not found']); exit; }
$d = json_decode(file_get_contents($f), true);
$d['imageUrl'] = 'https://cristianos.centralchat.pro/media/audio/songs/678d59bb9b_cover.jpg?v=' . time();
file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['ok'=>true, 'imageUrl'=>$d['imageUrl']]);
