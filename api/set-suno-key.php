<?php
// One-time key update - DELETE THIS FILE AFTER USE
$key = 'b8d1f947fc3e3d15ecb3fe30976f40b3';
$path = __DIR__ . '/../.suno-key';
file_put_contents($path, $key);
echo json_encode(['ok' => true, 'keySet' => substr($key, 0, 8) . '...', 'path' => $path]);
