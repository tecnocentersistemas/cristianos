<?php
// One-time fix: update all song metadata to use https
header('Content-Type: application/json');
$metaDir = __DIR__ . '/../data/songs';
$files = glob($metaDir . '/*.json');
$results = [];
foreach ($files as $f) {
    $raw = file_get_contents($f);
    $newRaw = str_replace('http://cristianos.centralchat.pro', 'https://cristianos.centralchat.pro', $raw);
    if ($raw !== $newRaw) {
        file_put_contents($f, $newRaw);
        $results[] = basename($f) . ' FIXED';
    } else {
        $results[] = basename($f) . ' already ok';
    }
}
echo json_encode(['files' => $results, 'sample' => substr(file_get_contents($files[0] ?? ''), 0, 300)]);
