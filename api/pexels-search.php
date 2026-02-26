<?php
// FaithTunes - Pexels image search (async, called from client)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'POST only']); exit; }

function loadKey($name) {
    $paths = [__DIR__.'/../.'.$name, '/var/www/cristianos/.'.$name];
    foreach ($paths as $p) { if (file_exists($p)) { $k = trim(file_get_contents($p)); if ($k) return $k; } }
    return getenv(strtoupper(str_replace('-','_',$name))) ?: null;
}

$pexelsKey = loadKey('pexels-key');
if (!$pexelsKey) { echo json_encode(['images'=>[]]); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$terms = $input['terms'] ?? [];
if (!is_array($terms) || !count($terms)) { echo json_encode(['images'=>[]]); exit; }

$images = []; $usedIds = [];
foreach (array_slice($terms, 0, 5) as $term) {
    $url = 'https://api.pexels.com/v1/search?' . http_build_query(['query'=>$term,'per_page'=>2,'orientation'=>'landscape','size'=>'large']);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HTTPHEADER=>['Authorization: '.$pexelsKey], CURLOPT_TIMEOUT=>6]);
    $r = curl_exec($ch); $rc = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($rc !== 200) continue;
    $d = json_decode($r, true);
    foreach (($d['photos'] ?? []) as $photo) {
        if (in_array($photo['id'], $usedIds)) continue;
        $usedIds[] = $photo['id'];
        $images[] = ['url'=>$photo['src']['landscape'] ?? $photo['src']['large'], 'alt'=>$photo['alt'] ?: $term, 'credit'=>$photo['photographer'] ?? ''];
        if (count($images) >= 6) break 2;
    }
}

// Fallback
if (count($images) < 3) {
    $P = 'https://images.pexels.com/photos/'; $S = '?auto=compress&cs=tinysrgb&w=1280&h=720&fit=crop';
    $fb = [$P.'36717/amazing-animal-beautiful-beauty.jpg'.$S,$P.'209831/pexels-photo-209831.jpeg'.$S,$P.'2559484/pexels-photo-2559484.jpeg'.$S,$P.'167698/pexels-photo-167698.jpeg'.$S];
    foreach ($fb as $f) $images[] = ['url'=>$f,'alt'=>'Nature','credit'=>''];
}

echo json_encode(['images'=>$images]);
