<?php
/**
 * FaithTunes - Song Recovery & Video Generation
 *
 * Actions (GET ?action=...):
 *   status        - Show current state (duplicates, missing videos, etc.)
 *   clean         - Remove duplicates, fix domains, keep 1 song per taskId
 *   generate_next - Find next song without video, fetch Pexels images, launch FFmpeg
 *   generate_all  - Process ALL songs without video (background chain)
 */
set_time_limit(120);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$cacheDir  = __DIR__ . '/../data/suno-cache';
$metaDir   = __DIR__ . '/../data/songs';
$songsDir  = __DIR__ . '/../media/audio/songs';
$videosDir = __DIR__ . '/../media/videos';
$imgsDir   = __DIR__ . '/../media/images/slides';
foreach ([$metaDir, $songsDir, $videosDir, $imgsDir] as $d) {
    if (!is_dir($d)) mkdir($d, 0755, true);
}

$baseUrl = 'https://yeshuacristiano.com';
$action  = $_GET['action'] ?? 'status';

// ===================== HELPERS =====================

function loadKey($name) {
    $paths = [__DIR__ . '/../.' . $name, '/var/www/cristianos/.' . $name];
    foreach ($paths as $p) {
        if (file_exists($p)) { $k = trim(file_get_contents($p)); if ($k) return $k; }
    }
    return getenv(strtoupper(str_replace('-', '_', $name))) ?: null;
}

function dlFile($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $data = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    return ($err || !$data) ? null : $data;
}

function searchPexels($query, $count = 6) {
    $key = loadKey('pexels-key');
    if (!$key) return [];
    $url = 'https://api.pexels.com/v1/search?' . http_build_query([
        'query' => $query, 'per_page' => $count,
        'orientation' => 'landscape', 'size' => 'large'
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: ' . $key],
        CURLOPT_TIMEOUT        => 8
    ]);
    $r  = curl_exec($ch);
    $rc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($rc !== 200) return [];
    $d = json_decode($r, true);
    $images = [];
    foreach (($d['photos'] ?? []) as $photo) {
        $images[] = $photo['src']['landscape'] ?? $photo['src']['large'];
    }
    return $images;
}

function getAllSongs($metaDir) {
    $songs = [];
    foreach (glob($metaDir . '/*.json') as $f) {
        $s = json_decode(file_get_contents($f), true);
        if ($s && !empty($s['id'])) {
            $s['_file'] = $f;
            $songs[$s['id']] = $s;
        }
    }
    return $songs;
}

function getProtectedIds($videosDir) {
    $ids = [];
    foreach (glob($videosDir . '/*.mp4') as $f) {
        $ids[] = pathinfo($f, PATHINFO_FILENAME);
    }
    return $ids;
}

// ===================== ACTION: STATUS =====================
if ($action === 'status') {
    $songs        = getAllSongs($metaDir);
    $protectedIds = getProtectedIds($videosDir);

    $byTask = [];
    foreach ($songs as $s) {
        $tid = $s['taskId'] ?? 'unknown';
        $byTask[$tid][] = $s['id'];
    }
    $duplicates = array_filter($byTask, fn($ids) => count($ids) > 1);

    $withVideo = 0; $withoutVideo = 0; $oldDomain = 0;
    foreach ($songs as $s) {
        if (!empty($s['videoUrl'])) $withVideo++; else $withoutVideo++;
        foreach (['audioUrl','videoUrl','imageUrl'] as $k) {
            if (!empty($s[$k]) && strpos($s[$k], 'centralchat.pro') !== false) {
                $oldDomain++; break;
            }
        }
    }

    echo json_encode([
        'total_songs'       => count($songs),
        'with_video'        => $withVideo,
        'without_video'     => $withoutVideo,
        'protected_ids'     => $protectedIds,
        'duplicate_taskIds' => $duplicates,
        'old_domain_count'  => $oldDomain,
        'cache_files'       => count(glob($cacheDir . '/*.json')),
    ], JSON_PRETTY_PRINT);
    exit;
}

// ===================== ACTION: CLEAN =====================
if ($action === 'clean') {
    $songs        = getAllSongs($metaDir);
    $protectedIds = getProtectedIds($videosDir);
    $log = ['fixed_domains' => [], 'removed_duplicates' => [], 'kept' => []];

    // 1) Fix domain URLs in ALL songs
    foreach ($songs as $id => &$s) {
        $changed = false;
        foreach (['audioUrl', 'videoUrl', 'imageUrl', 'shareUrl'] as $k) {
            if (!empty($s[$k]) && strpos($s[$k], 'centralchat.pro') !== false) {
                $s[$k] = preg_replace('#https?://[^/]+/#', $baseUrl . '/', $s[$k], 1);
                $changed = true;
            }
        }
        if ($changed) {
            file_put_contents($s['_file'], json_encode(
                array_diff_key($s, ['_file' => 1]),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            ));
            $log['fixed_domains'][] = $id;
        }
    }
    unset($s);

    // 2) Remove duplicates: group by taskId, keep the one with video or first
    $byTask = [];
    foreach ($songs as $s) {
        $tid = $s['taskId'] ?? 'unknown';
        $byTask[$tid][] = $s;
    }

    foreach ($byTask as $tid => $group) {
        if (count($group) <= 1) { $log['kept'][] = $group[0]['id']; continue; }

        usort($group, function ($a, $b) use ($protectedIds) {
            $aP = in_array($a['id'], $protectedIds);
            $bP = in_array($b['id'], $protectedIds);
            if ($aP !== $bP) return $aP ? -1 : 1;
            $aV = !empty($a['videoUrl']);
            $bV = !empty($b['videoUrl']);
            if ($aV !== $bV) return $aV ? -1 : 1;
            return strcmp($a['id'], $b['id']);
        });

        $log['kept'][] = $group[0]['id'];
        for ($i = 1; $i < count($group); $i++) {
            $rid = $group[$i]['id'];
            if (in_array($rid, $protectedIds)) { $log['kept'][] = $rid; continue; }
            @unlink($metaDir  . '/' . $rid . '.json');
            @unlink($songsDir . '/' . $rid . '.mp3');
            @unlink($songsDir . '/' . $rid . '_cover.jpg');
            $log['removed_duplicates'][] = $rid;
        }
    }

    // 3) Fix empty genres from tags
    $genreMap = ['country'=>'country','rock'=>'rock','gospel'=>'gospel',
        'folk'=>'folk','worship'=>'worship','ballad'=>'ballad',
        'pop'=>'pop','reggae'=>'reggae','jazz'=>'jazz'];
    $remaining   = getAllSongs($metaDir);
    $genresFixed = 0;
    foreach ($remaining as $s) {
        $needsSave = false;
        if (empty($s['genre']) && !empty($s['tags'])) {
            $tagsLower = strtolower($s['tags']);
            foreach ($genreMap as $kw => $g) {
                if (strpos($tagsLower, $kw) !== false) { $s['genre'] = $g; $needsSave = true; break; }
            }
            if (!$needsSave) { $s['genre'] = 'worship'; $needsSave = true; }
        }
        if ($needsSave) {
            $genresFixed++;
            file_put_contents($s['_file'], json_encode(
                array_diff_key($s, ['_file' => 1]),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            ));
        }
    }

    $log['genres_fixed']    = $genresFixed;
    $log['songs_remaining'] = count(glob($metaDir . '/*.json'));
    echo json_encode($log, JSON_PRETTY_PRINT);
    exit;
}

// ===================== ACTION: GENERATE_NEXT / GENERATE_ALL =====================
if ($action === 'generate_next' || $action === 'generate_all') {
    $songs        = getAllSongs($metaDir);
    $protectedIds = getProtectedIds($videosDir);
    $targetId     = $_GET['id'] ?? '';

    // Find songs that need video
    $pending = [];
    foreach ($songs as $s) {
        if (!empty($s['videoUrl']) && file_exists($videosDir . '/' . $s['id'] . '.mp4')) continue;
        if (!file_exists($songsDir . '/' . $s['id'] . '.mp3')) continue;
        if ($targetId && $s['id'] !== $targetId) continue;
        $pending[] = $s;
    }

    if (empty($pending)) {
        echo json_encode(['message' => 'All songs already have video', 'total' => count($songs)]);
        exit;
    }

    $song      = $pending[0];
    $id        = $song['id'];
    $audioFile = $songsDir  . '/' . $id . '.mp3';
    $videoFile = $videosDir . '/' . $id . '.mp4';

    // 1) Pexels search
    $pexelsQuery  = ($song['title'] ?? 'nature') . ' nature landscape';
    $pexelsImages = searchPexels($pexelsQuery, 8);

    if (count($pexelsImages) < 3) {
        $fallbacks = ['nature landscape mountain', 'sunset ocean peaceful', 'forest light morning'];
        foreach ($fallbacks as $fbq) {
            $pexelsImages = array_merge($pexelsImages, searchPexels($fbq, 3));
            if (count($pexelsImages) >= 6) break;
        }
    }
    if (count($pexelsImages) < 2) {
        echo json_encode(['error' => 'Not enough Pexels images', 'id' => $id]);
        exit;
    }

    // 2) Download slides
    $localSlides = [];
    foreach ($pexelsImages as $idx => $imgUrl) {
        $slideFile = $imgsDir . '/' . $id . '_' . $idx . '.jpg';
        if (!file_exists($slideFile)) {
            $img = dlFile($imgUrl);
            if ($img && strlen($img) > 5000) file_put_contents($slideFile, $img);
        }
        if (file_exists($slideFile)) $localSlides[] = $slideFile;
    }
    if (count($localSlides) < 2) {
        echo json_encode(['error' => 'Failed to download slides', 'id' => $id]);
        exit;
    }

    // 3) Update song metadata
    $song['slideImages'] = $pexelsImages;
    if (empty($song['imageUrl']) || strpos($song['imageUrl'] ?? '', 'removeai.ai') !== false) {
        $song['imageUrl'] = $pexelsImages[0];
    }
    unset($song['_file']);
    file_put_contents($metaDir . '/' . $id . '.json',
        json_encode($song, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // 4) Launch FFmpeg video generation in background
    launchVideoGeneration($localSlides, $audioFile, $videoFile,
        floatval($song['duration'] ?? 0), $id, $metaDir);

    $result = [
        'processed'         => $id,
        'title'             => $song['title'],
        'slides_downloaded' => count($localSlides),
        'video_generating'  => true,
        'pending_remaining' => count($pending) - 1,
    ];

    // Chain: if generate_all, queue the rest via background curl calls
    if ($action === 'generate_all' && count($pending) > 1) {
        $chainScript   = $videosDir . '/chain_generate.sh';
        $scriptContent = "#!/bin/bash\nsleep 120\n";
        for ($i = 1; $i < count($pending); $i++) {
            $nid = $pending[$i]['id'];
            $scriptContent .= "curl -s 'https://yeshuacristiano.com/api/recover-songs.php?action=generate_next&id={$nid}' > /dev/null 2>&1\nsleep 120\n";
        }
        $scriptContent .= "rm -f " . escapeshellarg($chainScript) . "\n";
        file_put_contents($chainScript, $scriptContent);
        chmod($chainScript, 0755);
        shell_exec('nohup bash ' . escapeshellarg($chainScript) . ' > /dev/null 2>&1 &');
        $result['chain_started'] = true;
        $result['chain_songs']   = count($pending) - 1;
    }

    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

echo json_encode(['error' => 'Unknown action. Use: status, clean, generate_next, generate_all']);

// ===================== VIDEO GENERATION (background) =====================
function launchVideoGeneration($slides, $audioFile, $outputFile, $duration, $id, $metaDir) {
    $ffmpeg = trim(shell_exec('which ffmpeg 2>/dev/null') ?: '');
    if (!$ffmpeg) return;

    $ffprobe = trim(shell_exec('which ffprobe 2>/dev/null') ?: '');
    if ($ffprobe) {
        $probeDur = trim(shell_exec($ffprobe . ' -v error -show_entries format=duration -of csv=p=0 '
            . escapeshellarg($audioFile) . ' 2>/dev/null') ?: '');
        $real = floatval($probeDur);
        if ($real > 0) $duration = $real;
    }
    if ($duration <= 0) $duration = 180;

    $numSlides   = count($slides);
    $durPerSlide = max(5, min(30, $duration / $numSlides));
    if ($durPerSlide * $numSlides < $duration && $numSlides > 0) {
        $durPerSlide = ceil($duration / $numSlides * 10) / 10;
    }

    $listFile    = dirname($outputFile) . '/' . basename($outputFile, '.mp4') . '_list.txt';
    $listContent = '';
    foreach ($slides as $sl) {
        $listContent .= "file " . escapeshellarg($sl) . "\nduration " . round($durPerSlide, 1) . "\n";
    }
    $listContent .= "file " . escapeshellarg($slides[count($slides) - 1]) . "\n";
    file_put_contents($listFile, $listContent);

    $cmd = $ffmpeg . ' -f concat -safe 0 -i ' . escapeshellarg($listFile)
        . ' -i ' . escapeshellarg($audioFile)
        . ' -vf "scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2,setsar=1"'
        . ' -c:v libx264 -preset fast -crf 23 -r 24 -pix_fmt yuv420p'
        . ' -c:a aac -b:a 192k -movflags +faststart'
        . ' -y ' . escapeshellarg($outputFile) . ' 2>/dev/null';

    $baseUrl      = 'https://yeshuacristiano.com';
    $updateScript = dirname($outputFile) . '/' . $id . '_gen.sh';
    $metaFile     = $metaDir . '/' . $id . '.json';
    $thumbFile    = dirname($audioFile) . '/' . $id . '_cover.jpg';

    $sc  = "#!/bin/bash\n{$cmd}\n";
    $sc .= "if [ -f " . escapeshellarg($outputFile) . " ]; then\n";
    $sc .= "  {$ffmpeg} -i " . escapeshellarg($outputFile) . " -ss 5 -vframes 1 -q:v 2 -y " . escapeshellarg($thumbFile) . " 2>/dev/null\n";
    $sc .= "  php -r '\$f=\"" . addslashes($metaFile) . "\";\$m=json_decode(file_get_contents(\$f),true);"
        . "\$m[\"videoUrl\"]=\"{$baseUrl}/media/videos/{$id}.mp4\";"
        . "if(file_exists(\"" . addslashes($thumbFile) . "\")&&filesize(\"" . addslashes($thumbFile) . "\")>5000)"
        . "{\$m[\"imageUrl\"]=\"{$baseUrl}/media/audio/songs/{$id}_cover.jpg?v=\".time();}"
        . "file_put_contents(\$f,json_encode(\$m,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));'\n";
    $sc .= "fi\n";
    $sc .= "rm -f " . escapeshellarg($listFile) . " " . escapeshellarg($updateScript) . "\n";
    file_put_contents($updateScript, $sc);
    chmod($updateScript, 0755);

    shell_exec('nohup bash ' . escapeshellarg($updateScript) . ' > /dev/null 2>&1 &');
}
