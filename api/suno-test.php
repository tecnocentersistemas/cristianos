<?php
// Self-contained Suno test - no dependencies on other logic
header('Content-Type: application/json; charset=utf-8');

// Load key
$keyPaths = [__DIR__.'/../.suno-key', '/var/www/cristianos/.suno-key'];
$sunoKey = '';
foreach ($keyPaths as $p) {
    if (file_exists($p)) { $sunoKey = trim(file_get_contents($p)); break; }
}
if (!$sunoKey) { echo json_encode(['error'=>'No suno key found','paths'=>$keyPaths]); exit; }

$action = $_GET['action'] ?? 'status';

// ACTION: generate - create a SHORT 30s test song
if ($action === 'generate') {
    $prompt = $_GET['prompt'] ?? 'a short 30 second christian country song about faith and hope';
    $ch = curl_init('https://apibox.erweima.ai/api/v1/generate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'prompt' => $prompt,
            'customMode' => false,
            'instrumental' => false,
            'model' => 'V3_5',
            'callBackUrl' => 'https://cristianos.centralchat.pro/api/suno-callback.php'
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer '.$sunoKey],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo json_encode(['action'=>'generate','curlCode'=>$code,'curlErr'=>$err,'response'=>json_decode($resp,true),'raw'=>substr($resp,0,500)], JSON_PRETTY_PRINT);
    exit;
}

// ACTION: poll - check task status via Suno API directly
if ($action === 'poll') {
    $taskId = $_GET['taskId'] ?? '';
    if (!$taskId) { echo json_encode(['error'=>'taskId required']); exit; }

    $url = 'https://apibox.erweima.ai/api/v1/generate/record-info?taskId=' . urlencode($taskId);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$sunoKey],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) { echo json_encode(['error'=>'curl failed','curlErr'=>$err,'httpCode'=>$code]); exit; }
    if ($code !== 200) { echo json_encode(['error'=>'http error','httpCode'=>$code,'body'=>substr($resp,0,300)]); exit; }

    $data = json_decode($resp, true);
    if (!$data) { echo json_encode(['error'=>'json parse failed','raw'=>substr($resp,0,500)]); exit; }

    $sunoCode = $data['code'] ?? null;
    $status = $data['data']['status'] ?? 'UNKNOWN';

    if ($sunoCode === 200 && $status === 'SUCCESS') {
        // Extract songs - data is in response.sunoData
        $songs = [];
        $songsRaw = $data['data']['response']['sunoData'] ?? $data['data']['data'] ?? [];
        foreach ($songsRaw as $s) {
            $songs[] = [
                'title' => $s['title'] ?? '',
                'audioUrl' => $s['audioUrl'] ?? '',
                'streamAudioUrl' => $s['streamAudioUrl'] ?? '',
                'imageUrl' => $s['imageUrl'] ?? '',
                'duration' => $s['duration'] ?? 0,
                'tags' => $s['tags'] ?? '',
            ];
        }
        echo json_encode([
            'status'=>'complete',
            'songs'=>$songs,
            'debug'=>[
                'songsCount'=>count($songsRaw),
                'dataKeys'=>array_keys($data['data'] ?? []),
                'firstSongKeys'=>!empty($songsRaw) ? array_keys($songsRaw[0]) : [],
                'rawSnippet'=>substr($resp, 0, 800)
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['status'=>'processing','sunoCode'=>$sunoCode,'sunoStatus'=>$status,'sunoMsg'=>$data['msg'] ?? ''], JSON_PRETTY_PRINT);
    }
    exit;
}

// Default: show status
echo json_encode([
    'ok' => true,
    'keyLoaded' => !empty($sunoKey),
    'keyPrefix' => substr($sunoKey, 0, 8) . '...',
    'usage' => [
        'generate' => '?action=generate&prompt=your+prompt',
        'poll' => '?action=poll&taskId=YOUR_TASK_ID'
    ]
], JSON_PRETTY_PRINT);
