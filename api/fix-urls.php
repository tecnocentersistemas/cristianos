<?php
// One-time fix: update all song metadata to use https
$metaDir = __DIR__ . '/../data/songs';
$files = glob($metaDir . '/*.json');
$fixed = 0;
foreach ($files as $f) {
    $data = json_decode(file_get_contents($f), true);
    if (!$data) continue;
    $changed = false;
    foreach (['audioUrl', 'videoUrl', 'imageUrl', 'shareUrl'] as $key) {
        if (!empty($data[$key]) && strpos($data[$key], 'http://') === 0) {
            $data[$key] = str_replace('http://', 'https://', $data[$key]);
            $changed = true;
        }
    }
    if ($changed) {
        file_put_contents($f, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $fixed++;
    }
}
echo json_encode(['fixed' => $fixed, 'total' => count($files)]);
