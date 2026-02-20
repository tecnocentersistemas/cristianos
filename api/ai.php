<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error' => 'Method not allowed']); http_response_code(405); exit; }
function getOpenAIKey() {
    $paths = [__DIR__ . '/../.openai-key', '/var/www/cristianos/.openai-key'];
    foreach ($paths as $p) { if (file_exists($p)) { $k = trim(file_get_contents($p)); if ($k && strpos($k, 'sk-') === 0) return $k; } }
    return getenv('OPENAI_API_KEY') ?: null;
}
function getImageDB() {
    $P = 'https://images.pexels.com/photos/'; $S = '?auto=compress&cs=tinysrgb&w=1280&h=720&fit=crop';
    return [
        'mountains' => [$P.'417173/pexels-photo-417173.jpeg'.$S, $P.'2098427/pexels-photo-2098427.jpeg'.$S, $P.'1054218/pexels-photo-1054218.jpeg'.$S, $P.'147411/italy-mountains-dawn-daybreak-147411.jpeg'.$S, $P.'2113566/pexels-photo-2113566.jpeg'.$S, $P.'2559941/pexels-photo-2559941.jpeg'.$S],
        'rivers' => [$P.'2406389/pexels-photo-2406389.jpeg'.$S, $P.'2743287/pexels-photo-2743287.jpeg'.$S, $P.'346529/pexels-photo-346529.jpeg'.$S, $P.'2438/nature-forest-waves-trees.jpg'.$S, $P.'1032650/pexels-photo-1032650.jpeg'.$S],
        'sunsets' => [$P.'36717/amazing-animal-beautiful-beauty.jpg'.$S, $P.'36744/sunset-cloud-meditation-yoga.jpg'.$S, $P.'209831/pexels-photo-209831.jpeg'.$S, $P.'2559484/pexels-photo-2559484.jpeg'.$S],
        'sky' => [$P.'53594/blue-clouds-day-fluffy-53594.jpeg'.$S, $P.'209831/pexels-photo-209831.jpeg'.$S, $P.'2559484/pexels-photo-2559484.jpeg'.$S],
        'stars' => [$P.'1252890/pexels-photo-1252890.jpeg'.$S, $P.'2559484/pexels-photo-2559484.jpeg'.$S],
        'forests' => [$P.'15286/pexels-photo.jpg'.$S, $P.'167698/pexels-photo-167698.jpeg'.$S, $P.'1578750/pexels-photo-1578750.jpeg'.$S, $P.'1423600/pexels-photo-1423600.jpeg'.$S],
        'trees' => [$P.'15286/pexels-photo.jpg'.$S, $P.'167698/pexels-photo-167698.jpeg'.$S, $P.'1578750/pexels-photo-1578750.jpeg'.$S],
        'fields' => [$P.'1166209/pexels-photo-1166209.jpeg'.$S, $P.'462118/pexels-photo-462118.jpeg'.$S, $P.'265216/pexels-photo-265216.jpeg'.$S, $P.'1227513/pexels-photo-1227513.jpeg'.$S],
        'flowers' => [$P.'462118/pexels-photo-462118.jpeg'.$S, $P.'56866/pexels-photo-56866.jpeg'.$S, $P.'1166209/pexels-photo-1166209.jpeg'.$S],
        'eagles' => [$P.'2662434/pexels-photo-2662434.jpeg'.$S, $P.'1054655/pexels-photo-1054655.jpeg'.$S],
        'doves' => [$P.'1661179/pexels-photo-1661179.jpeg'.$S, $P.'326055/pexels-photo-326055.jpeg'.$S],
        'lambs' => [$P.'288621/pexels-photo-288621.jpeg'.$S, $P.'1166209/pexels-photo-1166209.jpeg'.$S],
        'lions' => [$P.'247502/pexels-photo-247502.jpeg'.$S, $P.'2662434/pexels-photo-2662434.jpeg'.$S],
        'deer' => [$P.'1054655/pexels-photo-1054655.jpeg'.$S, $P.'1578750/pexels-photo-1578750.jpeg'.$S],
        'butterflies' => [$P.'326055/pexels-photo-326055.jpeg'.$S, $P.'462118/pexels-photo-462118.jpeg'.$S],
        'bees' => [$P.'462118/pexels-photo-462118.jpeg'.$S, $P.'56866/pexels-photo-56866.jpeg'.$S, $P.'326055/pexels-photo-326055.jpeg'.$S],
        'horses' => [$P.'1227513/pexels-photo-1227513.jpeg'.$S, $P.'265216/pexels-photo-265216.jpeg'.$S],
        'ocean' => [$P.'1032650/pexels-photo-1032650.jpeg'.$S, $P.'2559484/pexels-photo-2559484.jpeg'.$S],
        'lakes' => [$P.'346529/pexels-photo-346529.jpeg'.$S, $P.'147411/italy-mountains-dawn-daybreak-147411.jpeg'.$S],
        'waterfalls' => [$P.'2743287/pexels-photo-2743287.jpeg'.$S, $P.'2406389/pexels-photo-2406389.jpeg'.$S],
        'snow' => [$P.'417173/pexels-photo-417173.jpeg'.$S, $P.'2559941/pexels-photo-2559941.jpeg'.$S],
        'rain' => [$P.'1423600/pexels-photo-1423600.jpeg'.$S, $P.'167698/pexels-photo-167698.jpeg'.$S],
        'desert' => [$P.'2559941/pexels-photo-2559941.jpeg'.$S, $P.'2113566/pexels-photo-2113566.jpeg'.$S],
        'sunrise' => [$P.'36717/amazing-animal-beautiful-beauty.jpg'.$S, $P.'2098427/pexels-photo-2098427.jpeg'.$S],
        'wolves' => [$P.'247502/pexels-photo-247502.jpeg'.$S],
        'fish' => [$P.'1032650/pexels-photo-1032650.jpeg'.$S, $P.'346529/pexels-photo-346529.jpeg'.$S],
    ];
}
function pickImages($categories) {
    $db = getImageDB(); $imgs = []; $used = [];
    foreach ($categories as $cat) {
        $cat = strtolower(trim($cat)); $pool = $db[$cat] ?? $db['sunsets']; shuffle($pool);
        $pick = null; foreach ($pool as $u) { if (!in_array($u, $used)) { $pick = $u; $used[] = $u; break; } }
        if (!$pick) $pick = $pool[0];
        $imgs[] = ['url' => $pick, 'alt' => ucfirst($cat)];
    }
    return $imgs;
}
function pickAudio($genre) {
    $t = [
        'country' => [['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Americana.mp3','name'=>'Americana - Country'], ['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Daily%20Beetle.mp3','name'=>'Daily Beetle - Country']],
        'rock' => [['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Inspired.mp3','name'=>'Inspired - Rock'], ['url'=>'https://cdn.pixabay.com/audio/2022/01/18/audio_d0a13f69d2.mp3','name'=>'Epic Rock'], ['url'=>'https://cdn.pixabay.com/audio/2022/11/22/audio_febc508520.mp3','name'=>'Rock Anthem']],
        'gospel' => [['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Wholesome.mp3','name'=>'Wholesome - Gospel'], ['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Amazing%20Plan.mp3','name'=>'Amazing Plan - Gospel'], ['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Groove%20Grove.mp3','name'=>'Groove Grove - Gospel']],
        'folk' => [['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Americana.mp3','name'=>'Americana - Folk'], ['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Daily%20Beetle.mp3','name'=>'Daily Beetle - Folk']],
        'worship' => [['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Eternal%20Hope.mp3','name'=>'Eternal Hope - Worship'], ['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/At%20Rest.mp3','name'=>'At Rest - Worship Piano'], ['url'=>'https://cdn.pixabay.com/audio/2022/02/22/audio_d1718ab41b.mp3','name'=>'Peaceful Worship']],
        'ballad' => [['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/At%20Rest.mp3','name'=>'At Rest - Ballad'], ['url'=>'https://incompetech.com/music/royalty-free/mp3-royaltyfree/Eternal%20Hope.mp3','name'=>'Eternal Hope - Ballad'], ['url'=>'https://cdn.pixabay.com/audio/2021/11/25/audio_91b32e02f9.mp3','name'=>'Soft Ballad']],
    ];
    $pool = $t[$genre] ?? $t['worship']; return $pool[array_rand($pool)];
}
function callOpenAI($apiKey, $prompt) {
    $cats = 'mountains, rivers, lakes, ocean, waterfalls, sunsets, sunrise, sky, stars, forests, trees, fields, flowers, desert, snow, rain, eagles, doves, lambs, lions, deer, butterflies, bees, horses, fish, wolves';
    $sys = 'You are FaithTunes, a Christian music video creator. Return ONLY valid JSON: {"title":"Title","theme":"faith|hope|love|peace|gratitude|strength","mood":"peaceful|joyful|powerful|reflective|uplifting","genre":"country|rock|gospel|folk|worship|ballad","poem":["l1","l2","l3","l4","l5","l6"],"verses":[{"ref":"Ref","text":"text"},{"ref":"Ref2","text":"text2"},{"ref":"Ref3","text":"text3"}],"imageCategories":["c1","c2","c3","c4","c5"],"description":"desc"} RULES: imageCategories EXACTLY 5 from ONLY: '.$cats.'. Match user request: bees=bees, mountains=mountains. genre must match user request. poem 6 lines Christian in user language. verses 3 REAL Bible verses in user language. ONLY JSON no markdown.';
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode(['model'=>'gpt-4o-mini','messages'=>[['role'=>'system','content'=>$sys],['role'=>'user','content'=>$prompt]],'temperature'=>0.7,'max_tokens'=>1000]), CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$apiKey], CURLOPT_TIMEOUT=>30]);
    $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code !== 200) return ['error'=>'OpenAI error '.$code];
    $r = json_decode($resp, true); $c = trim($r['choices'][0]['message']['content'] ?? '');
    if (strpos($c, '```') !== false) { preg_match('/```(?:json)?\s*(.*?)\s*```/s', $c, $m); $c = $m[1] ?? $c; }
    return json_decode($c, true) ?: ['error'=>'Parse failed'];
}
$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';
if (!$prompt) { echo json_encode(['error'=>'Prompt required']); http_response_code(400); exit; }
$key = getOpenAIKey();
if (!$key) { echo json_encode(['error'=>'No API key']); http_response_code(500); exit; }
$ai = callOpenAI($key, $prompt);
if (isset($ai['error'])) { echo json_encode($ai); http_response_code(500); exit; }
$images = pickImages($ai['imageCategories'] ?? ['sunsets','mountains','rivers','forests','fields']);
$audio = pickAudio($ai['genre'] ?? 'worship');
echo json_encode(['success'=>true,'video'=>['title'=>$ai['title'] ?? 'FaithTunes','theme'=>$ai['theme'] ?? 'faith','mood'=>$ai['mood'] ?? 'peaceful','genre'=>$ai['genre'] ?? 'worship','description'=>$ai['description'] ?? '','poem'=>$ai['poem'] ?? [],'verses'=>$ai['verses'] ?? [],'images'=>$images,'audio'=>$audio['url'],'audioName'=>$audio['name']]], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);