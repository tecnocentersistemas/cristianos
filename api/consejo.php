<?php
// FaithTunes - Biblical Counselor: GPT counsel + OpenAI TTS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }

function loadKey($name) {
    $paths = [__DIR__.'/../.'.$name, '/var/www/cristianos/.'.$name];
    foreach ($paths as $p) { if (file_exists($p)) { $k = trim(file_get_contents($p)); if ($k) return $k; } }
    return getenv(strtoupper(str_replace('-','_',$name))) ?: null;
}

$apiKey = loadKey('openai-key');
if (!$apiKey) { http_response_code(500); echo json_encode(['error'=>'No API key']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$topic = $input['topic'] ?? '';
$lang = $input['lang'] ?? 'es';
if (!$topic || strlen($topic) < 3) { echo json_encode(['error'=>'Topic required']); exit; }
if (strlen($topic) > 2000) { echo json_encode(['error'=>'Topic too long']); exit; }

$langNames = ['es'=>'Spanish','en'=>'English','pt'=>'Portuguese','de'=>'German','fr'=>'French','it'=>'Italian','pl'=>'Polish','ru'=>'Russian','uk'=>'Ukrainian','sv'=>'Swedish','fi'=>'Finnish','nb'=>'Norwegian','lv'=>'Latvian','sl'=>'Slovenian','ja'=>'Japanese','ko'=>'Korean','zh'=>'Chinese','ar'=>'Arabic','fa'=>'Persian'];
$langName = $langNames[$lang] ?? 'Spanish';

// Voice map per language
$voiceMap = ['es'=>'nova','en'=>'nova','pt'=>'nova','de'=>'onyx','fr'=>'shimmer','it'=>'shimmer','ja'=>'nova','ko'=>'nova','zh'=>'nova','ar'=>'onyx','fa'=>'onyx'];
$voice = $voiceMap[$lang] ?? 'nova';

// ===== Step 1: Generate counsel with GPT =====
$system = 'You are a compassionate Christian biblical counselor. You provide wise, loving counsel based on Scripture. Return ONLY valid JSON:
{"title":"Brief title for this counsel","counsel":"A warm, empathetic 3-4 paragraph counsel (150-250 words). Start by acknowledging the person\'s feelings. Then provide biblical wisdom. End with encouragement and hope. Use a warm conversational tone, as if speaking to a friend.","verses":[{"ref":"Book Ch:Vs","text":"Full verse text"},{"ref":"...","text":"..."},{"ref":"...","text":"..."}],"prayer":"A short prayer (2-3 sentences) relevant to their situation.","imageSearchTerms":["term1","term2","term3","term4","term5"]}
RULES:
- ALL text MUST be in ' . $langName . '.
- counsel: Warm, empathetic, biblically grounded. NOT preachy. Like a wise friend.
- verses: 3 REAL Bible verses directly relevant to the topic. In ' . $langName . '.
- prayer: Short, heartfelt prayer. In ' . $langName . '.
- imageSearchTerms: 5 calming nature/landscape photo terms IN ENGLISH (sunsets, peaceful lake, etc).
- ONLY JSON output.';

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'gpt-4o-mini',
        'response_format' => ['type' => 'json_object'],
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $topic]
        ],
        'temperature' => 0.7,
        'max_tokens' => 1500
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
    CURLOPT_TIMEOUT => 30
]);
$resp = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $code !== 200) { echo json_encode(['error' => 'GPT error']); exit; }

$data = json_decode($resp, true);
$content = trim($data['choices'][0]['message']['content'] ?? '');
if (strpos($content, '```') !== false) { preg_match('/```(?:json)?\s*(.*?)\s*```/s', $content, $m); if (!empty($m[1])) $content = $m[1]; }
$start = strpos($content, '{'); $end = strrpos($content, '}');
if ($start !== false && $end !== false) $content = substr($content, $start, $end - $start + 1);
$result = json_decode($content, true);
if (!$result) { $content = preg_replace('/,\s*([}\]])/', '$1', $content); $result = json_decode($content, true); }
if (!$result) { echo json_encode(['error' => 'Parse error']); exit; }

// ===== Step 2: Generate spoken audio with OpenAI TTS =====
$ttsText = $result['counsel'];
if (!empty($result['prayer'])) $ttsText .= "\n\n" . $result['prayer'];

$audioDir = __DIR__ . '/../media/audio/counsel';
if (!is_dir($audioDir)) mkdir($audioDir, 0755, true);
$audioId = 'counsel_' . substr(md5($ttsText . $lang), 0, 12);
$audioFile = $audioDir . '/' . $audioId . '.mp3';
$audioUrl = '';

// Check cache
if (file_exists($audioFile) && filesize($audioFile) > 1000) {
    $host = $_SERVER['HTTP_HOST'] ?? 'yeshuacristiano.com';
    $audioUrl = 'https://' . $host . '/media/audio/counsel/' . $audioId . '.mp3';
} else {
    $ch = curl_init('https://api.openai.com/v1/audio/speech');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'tts-1',
            'voice' => $voice,
            'input' => $ttsText,
            'response_format' => 'mp3'
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
        CURLOPT_TIMEOUT => 60
    ]);
    $audioData = curl_exec($ch);
    $ttsErr = curl_error($ch);
    $ttsCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$ttsErr && $ttsCode === 200 && strlen($audioData) > 1000) {
        file_put_contents($audioFile, $audioData);
        $host = $_SERVER['HTTP_HOST'] ?? 'yeshuacristiano.com';
        $audioUrl = 'https://' . $host . '/media/audio/counsel/' . $audioId . '.mp3';
    }
}

// ===== Step 3: Get images from Pexels =====
$images = [];
$pexelsKey = loadKey('pexels-key');
$searchTerms = $result['imageSearchTerms'] ?? ['peaceful sunset','calm lake','green meadow','morning light forest','mountain sunrise'];
if ($pexelsKey) {
    foreach ($searchTerms as $term) {
        $url = 'https://api.pexels.com/v1/search?' . http_build_query(['query'=>$term,'per_page'=>2,'orientation'=>'landscape','size'=>'large']);
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HTTPHEADER=>['Authorization: '.$pexelsKey], CURLOPT_TIMEOUT=>8]);
        $r = curl_exec($ch); $rc = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($rc === 200) {
            $d = json_decode($r, true);
            foreach (($d['photos'] ?? []) as $photo) {
                $images[] = ['url'=>$photo['src']['landscape'] ?? $photo['src']['large'], 'alt'=>$photo['alt'] ?: $term, 'credit'=>$photo['photographer'] ?? ''];
                if (count($images) >= 6) break 2;
            }
        }
    }
}
// Fallback images
if (count($images) < 3) {
    $P = 'https://images.pexels.com/photos/'; $S = '?auto=compress&cs=tinysrgb&w=1280&h=720&fit=crop';
    $fallback = [$P.'36717/amazing-animal-beautiful-beauty.jpg'.$S,$P.'209831/pexels-photo-209831.jpeg'.$S,$P.'2559484/pexels-photo-2559484.jpeg'.$S,$P.'167698/pexels-photo-167698.jpeg'.$S,$P.'1423600/pexels-photo-1423600.jpeg'.$S];
    foreach ($fallback as $fb) { $images[] = ['url'=>$fb,'alt'=>'Nature','credit'=>'']; }
}

echo json_encode([
    'success' => true,
    'title' => $result['title'] ?? '',
    'counsel' => $result['counsel'] ?? '',
    'verses' => $result['verses'] ?? [],
    'prayer' => $result['prayer'] ?? '',
    'audioUrl' => $audioUrl,
    'images' => $images
], JSON_UNESCAPED_UNICODE);
