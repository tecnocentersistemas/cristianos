<?php
// FaithTunes - Audio transcription using OpenAI Whisper
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'POST only']); exit; }

function loadKey($name) {
    $paths = [__DIR__.'/../.'.$name, '/var/www/cristianos/.'.$name];
    foreach ($paths as $p) { if (file_exists($p)) { $k = trim(file_get_contents($p)); if ($k) return $k; } }
    return getenv(strtoupper(str_replace('-','_',$name))) ?: null;
}

$apiKey = loadKey('openai-key');
if (!$apiKey) { http_response_code(500); echo json_encode(['error'=>'No API key']); exit; }

if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error'=>'No audio file']); exit;
}

$tmpFile = $_FILES['audio']['tmp_name'];
$lang = $_POST['lang'] ?? 'es';
$langMap = ['es'=>'es','en'=>'en','pt'=>'pt','de'=>'de','fr'=>'fr','it'=>'it','pl'=>'pl','ru'=>'ru','uk'=>'uk','sv'=>'sv','fi'=>'fi','nb'=>'no','lv'=>'lv','sl'=>'sl','ja'=>'ja','ko'=>'ko','zh'=>'zh','ar'=>'ar','fa'=>'fa'];
$whisperLang = $langMap[$lang] ?? 'es';

$cFile = new CURLFile($tmpFile, 'audio/webm', 'voice.webm');
$ch = curl_init('https://api.openai.com/v1/audio/transcriptions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'file' => $cFile,
        'model' => 'whisper-1',
        'language' => $whisperLang,
        'response_format' => 'json'
    ],
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey],
    CURLOPT_TIMEOUT => 30
]);
$resp = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $code !== 200) { echo json_encode(['error'=>'Transcription failed']); exit; }

$data = json_decode($resp, true);
$text = $data['text'] ?? '';

echo json_encode(['success'=>true, 'text'=>$text], JSON_UNESCAPED_UNICODE);
