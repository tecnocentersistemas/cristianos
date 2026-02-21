<?php
header('Content-Type: application/json');
$dir = __DIR__ . '/../data/suno-cache';
$fixed = [];
foreach (glob($dir . '/unknown_*.json') as $f) {
    $d = json_decode(file_get_contents($f), true);
    $tid = $d['data']['task_id'] ?? $d['payload']['data']['task_id'] ?? null;
    if ($tid) {
        $newFile = $dir . '/' . $tid . '.json';
        if (!file_exists($newFile)) {
            copy($f, $newFile);
            $fixed[] = basename($f) . ' -> ' . $tid . '.json';
        }
    }
}
echo json_encode(['fixed' => $fixed]);
