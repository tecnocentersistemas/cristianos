<?php
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

// ===== PEXELS API: Dynamic image search =====
function searchPexels($terms, $perTerm = 2) {
    $key = loadKey('pexels-key');
    if (!$key) return null;
    $images = []; $usedIds = [];
    foreach ($terms as $term) {
        $url = 'https://api.pexels.com/v1/search?' . http_build_query(['query'=>$term,'per_page'=>$perTerm+3,'orientation'=>'landscape','size'=>'large']);
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HTTPHEADER=>['Authorization: '.$key], CURLOPT_TIMEOUT=>8]);
        $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($code !== 200) continue;
        $data = json_decode($resp, true); $count = 0;
        foreach (($data['photos'] ?? []) as $photo) {
            if (in_array($photo['id'], $usedIds)) continue;
            $usedIds[] = $photo['id'];
            $images[] = ['url'=>$photo['src']['landscape'] ?? $photo['src']['large2x'] ?? $photo['src']['large'], 'alt'=>$photo['alt'] ?: ucfirst($term), 'credit'=>$photo['photographer'] ?? ''];
            if (++$count >= $perTerm) break;
        }
    }
    return count($images) >= 3 ? $images : null;
}

// ===== FALLBACK: Static Pexels URLs =====
function staticImages($cats) {
    $P = 'https://images.pexels.com/photos/'; $S = '?auto=compress&cs=tinysrgb&w=1280&h=720&fit=crop';
    $db = [
        'mountains'=>[$P.'417173/pexels-photo-417173.jpeg'.$S,$P.'2098427/pexels-photo-2098427.jpeg'.$S,$P.'1054218/pexels-photo-1054218.jpeg'.$S,$P.'147411/italy-mountains-dawn-daybreak-147411.jpeg'.$S,$P.'2559941/pexels-photo-2559941.jpeg'.$S],
        'rivers'=>[$P.'2406389/pexels-photo-2406389.jpeg'.$S,$P.'2743287/pexels-photo-2743287.jpeg'.$S,$P.'346529/pexels-photo-346529.jpeg'.$S,$P.'1032650/pexels-photo-1032650.jpeg'.$S],
        'sunsets'=>[$P.'36717/amazing-animal-beautiful-beauty.jpg'.$S,$P.'36744/sunset-cloud-meditation-yoga.jpg'.$S,$P.'209831/pexels-photo-209831.jpeg'.$S,$P.'2559484/pexels-photo-2559484.jpeg'.$S],
        'forests'=>[$P.'15286/pexels-photo.jpg'.$S,$P.'167698/pexels-photo-167698.jpeg'.$S,$P.'1578750/pexels-photo-1578750.jpeg'.$S,$P.'1423600/pexels-photo-1423600.jpeg'.$S],
        'fields'=>[$P.'1166209/pexels-photo-1166209.jpeg'.$S,$P.'462118/pexels-photo-462118.jpeg'.$S,$P.'265216/pexels-photo-265216.jpeg'.$S],
        'sky'=>[$P.'53594/blue-clouds-day-fluffy-53594.jpeg'.$S,$P.'209831/pexels-photo-209831.jpeg'.$S],
        'stars'=>[$P.'1252890/pexels-photo-1252890.jpeg'.$S],
        'flowers'=>[$P.'462118/pexels-photo-462118.jpeg'.$S,$P.'56866/pexels-photo-56866.jpeg'.$S],
        'eagles'=>[$P.'2662434/pexels-photo-2662434.jpeg'.$S,$P.'1054655/pexels-photo-1054655.jpeg'.$S],
        'doves'=>[$P.'1661179/pexels-photo-1661179.jpeg'.$S,$P.'326055/pexels-photo-326055.jpeg'.$S],
        'lambs'=>[$P.'288621/pexels-photo-288621.jpeg'.$S],
        'lions'=>[$P.'247502/pexels-photo-247502.jpeg'.$S],
        'ocean'=>[$P.'1032650/pexels-photo-1032650.jpeg'.$S,$P.'2559484/pexels-photo-2559484.jpeg'.$S],
        'horses'=>[$P.'1227513/pexels-photo-1227513.jpeg'.$S,$P.'265216/pexels-photo-265216.jpeg'.$S],
        'snow'=>[$P.'417173/pexels-photo-417173.jpeg'.$S,$P.'2559941/pexels-photo-2559941.jpeg'.$S],
    ];
    $imgs = []; $used = [];
    foreach ($cats as $cat) {
        $cat = strtolower(trim($cat)); $pool = $db[$cat] ?? $db['sunsets']; shuffle($pool);
        $pick = null; foreach ($pool as $u) { if (!in_array($u,$used)) { $pick=$u; $used[]=$u; break; } }
        if (!$pick) $pick = $pool[0];
        $imgs[] = ['url'=>$pick, 'alt'=>ucfirst($cat), 'credit'=>''];
    }
    return $imgs;
}

// ===== AUDIO: Genre + mood matching =====
function pickAudio($genre, $mood = 'peaceful') {
    $px = 'https://cdn.pixabay.com/audio/';
    $km = 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/';
    $lib = [
        'country'=>[
            ['url'=>$km.'Americana.mp3','name'=>'Country Americana','mood'=>'peaceful'],
            ['url'=>$km.'Daily%20Beetle.mp3','name'=>'Country Folk','mood'=>'joyful'],
            ['url'=>$km.'On%20My%20Way.mp3','name'=>'On My Way','mood'=>'uplifting'],
            ['url'=>$km.'Easy%20Lemon.mp3','name'=>'Easy Country','mood'=>'joyful'],
            ['url'=>$km.'Local%20Forecast.mp3','name'=>'Country Breeze','mood'=>'peaceful'],
        ],
        'rock'=>[
            ['url'=>$km.'Inspired.mp3','name'=>'Inspired Rock','mood'=>'powerful'],
            ['url'=>$px.'2022/01/18/audio_d0a13f69d2.mp3','name'=>'Epic Rock Inspiration','mood'=>'powerful'],
            ['url'=>$px.'2022/11/22/audio_febc508520.mp3','name'=>'Rock Anthem','mood'=>'uplifting'],
            ['url'=>$km.'Life%20of%20Riley.mp3','name'=>'Rock of Life','mood'=>'joyful'],
        ],
        'gospel'=>[
            ['url'=>$km.'Wholesome.mp3','name'=>'Wholesome Gospel','mood'=>'joyful'],
            ['url'=>$km.'Amazing%20Plan.mp3','name'=>'Amazing Gospel Plan','mood'=>'uplifting'],
            ['url'=>$km.'Groove%20Grove.mp3','name'=>'Gospel Groove','mood'=>'joyful'],
            ['url'=>$px.'2022/11/22/audio_febc508520.mp3','name'=>'Gospel Power','mood'=>'powerful'],
        ],
        'folk'=>[
            ['url'=>$km.'Americana.mp3','name'=>'Folk Americana','mood'=>'peaceful'],
            ['url'=>$km.'Garden%20Music.mp3','name'=>'Folk Garden','mood'=>'peaceful'],
            ['url'=>$km.'Perspectives.mp3','name'=>'Folk Perspectives','mood'=>'reflective'],
            ['url'=>$km.'Dirt%20Rhodes.mp3','name'=>'Folk Rhodes','mood'=>'joyful'],
        ],
        'worship'=>[
            ['url'=>$km.'Gymnopedie%20No%201.mp3','name'=>'Worship Piano','mood'=>'peaceful'],
            ['url'=>$km.'Peaceful%20Desolation.mp3','name'=>'Peaceful Worship','mood'=>'reflective'],
            ['url'=>$px.'2022/02/22/audio_d1718ab41b.mp3','name'=>'Peaceful Meditation','mood'=>'peaceful'],
            ['url'=>$px.'2021/11/25/audio_91b32e02f9.mp3','name'=>'Ambient Worship','mood'=>'peaceful'],
            ['url'=>$km.'Eternal%20Hope.mp3','name'=>'Eternal Hope','mood'=>'uplifting'],
        ],
        'ballad'=>[
            ['url'=>$px.'2022/08/03/audio_54ca0ffa52.mp3','name'=>'Gentle Piano Ballad','mood'=>'reflective'],
            ['url'=>$km.'At%20Rest.mp3','name'=>'Piano At Rest','mood'=>'peaceful'],
            ['url'=>$km.'Eternal%20Hope.mp3','name'=>'Hopeful Ballad','mood'=>'uplifting'],
            ['url'=>$px.'2022/05/27/audio_1808fbf07a.mp3','name'=>'Peaceful Ballad','mood'=>'peaceful'],
            ['url'=>$px.'2021/11/25/audio_91b32e02f9.mp3','name'=>'Soft Emotional','mood'=>'reflective'],
        ],
    ];
    $pool = $lib[$genre] ?? $lib['worship'];
    $moodPool = array_values(array_filter($pool, function($t) use ($mood) { return $t['mood'] === $mood; }));
    if (!empty($moodPool)) return $moodPool[array_rand($moodPool)];
    return $pool[array_rand($pool)];
}

// ===== OpenAI =====
function callOpenAI($apiKey, $prompt) {
    $system = 'You are FaithTunes, a Christian music video creator. Return ONLY valid JSON (no markdown, no ```):
{"title":"Creative title","theme":"faith|hope|love|peace|gratitude|strength","mood":"peaceful|joyful|powerful|reflective|uplifting","genre":"country|rock|gospel|folk|worship|ballad","poem":["l1","l2","l3","l4","l5","l6"],"verses":[{"ref":"Book Ch:Vs","text":"Full text"},{"ref":"...","text":"..."},{"ref":"...","text":"..."}],"imageSearchTerms":["term1","term2","term3","term4","term5"],"description":"Brief desc"}
RULES:
- imageSearchTerms: EXACTLY 5 photo search queries ALWAYS IN ENGLISH regardless of user language. Be VERY specific to what user asked for. If user says "abejas" search "honey bee pollinating flower close up". If "montañas" search "majestic mountain landscape sunrise". If "caballos" search "wild horses running meadow". MUST be in English. MUST match the subject the user requested.
- genre: MUST match user request. country=country, rock=rock. Default worship only if unspecified.
- poem: 6 lines original Christian lyrics in USER LANGUAGE.
- verses: 3 REAL Bible verses in USER LANGUAGE.
- ONLY JSON output.';

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['model'=>'gpt-4o-mini','response_format'=>['type'=>'json_object'],'messages'=>[['role'=>'system','content'=>$system],['role'=>'user','content'=>$prompt]],'temperature'=>0.7,'max_tokens'=>2000]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json','Authorization: Bearer '.$apiKey],
        CURLOPT_TIMEOUT => 45
    ]);
    $resp = curl_exec($ch); $err = curl_error($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($err) return ['error'=>'Curl error: '.$err];
    if ($code !== 200) return ['error'=>'OpenAI error '.$code];
    $data = json_decode($resp, true);
    if (!$data || !isset($data['choices'][0]['message']['content'])) return ['error'=>'Empty OpenAI response'];
    $content = trim($data['choices'][0]['message']['content']);
    // Strip markdown fences
    if (strpos($content, '```') !== false) { preg_match('/```(?:json)?\s*(.*?)\s*```/s', $content, $m); if (!empty($m[1])) $content = $m[1]; }
    // Strip leading/trailing non-JSON chars
    $content = trim($content);
    $start = strpos($content, '{');
    $end = strrpos($content, '}');
    if ($start !== false && $end !== false) { $content = substr($content, $start, $end - $start + 1); }
    $result = json_decode($content, true);
    if (!$result) {
        // Retry: attempt to fix common issues (trailing commas, etc)
        $content = preg_replace('/,\s*([}\]])/', '$1', $content);
        $result = json_decode($content, true);
    }
    return $result ?: ['error'=>'JSON parse failed','raw'=>substr($content, 0, 200)];
}

// ===== MAIN =====
$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';
$lang = $input['lang'] ?? 'es';
$sunoMode = $input['sunoMode'] ?? false;
if (!$prompt) { http_response_code(400); echo json_encode(['error'=>'Prompt required']); exit; }
$openaiKey = loadKey('openai-key');
if (!$openaiKey) { http_response_code(500); echo json_encode(['error'=>'No API key']); exit; }

// Append language instruction to prompt
$langNames = ['es'=>'Spanish','en'=>'English','pt'=>'Portuguese'];
$langHint = $langNames[$lang] ?? 'Spanish';
$fullPrompt = $prompt . "\n\n[IMPORTANT: All text output (title, poem, verses, description) MUST be in " . $langHint . ". The song lyrics MUST be in " . $langHint . ".]";

$ai = callOpenAI($openaiKey, $fullPrompt);
// Retry once if failed
if (isset($ai['error'])) {
    sleep(2);
    $ai = callOpenAI($openaiKey, $prompt);
}
if (isset($ai['error'])) { http_response_code(500); echo json_encode($ai); exit; }

$searchTerms = $ai['imageSearchTerms'] ?? [];
$images = !empty($searchTerms) ? searchPexels($searchTerms) : null;
if (!$images) { $images = staticImages($ai['imageCategories'] ?? ['sunsets','mountains','rivers','forests','fields']); }

$genre = $ai['genre'] ?? 'worship'; $mood = $ai['mood'] ?? 'peaceful';

// In Suno mode, skip instrumental audio selection
if ($sunoMode) {
    $audioUrl = ''; $audioName = '';
} else {
    $audio = pickAudio($genre, $mood);
    $audioUrl = $audio['url']; $audioName = $audio['name'];
}

echo json_encode(['success'=>true,'video'=>[
    'title'=>$ai['title'] ?? 'FaithTunes', 'theme'=>$ai['theme'] ?? 'faith', 'mood'=>$mood, 'genre'=>$genre,
    'description'=>$ai['description'] ?? '', 'poem'=>$ai['poem'] ?? [], 'verses'=>$ai['verses'] ?? [],
    'images'=>$images, 'audio'=>$audioUrl, 'audioName'=>$audioName, 'lang'=>$lang
]], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
