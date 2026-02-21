<?php
// Generate instrumental catalog entries for all genres
// Uses the same audio sources as pickAudio (Incompetech + Pixabay - royalty free)
header('Content-Type: text/plain');
set_time_limit(300);

$baseDir = __DIR__ . '/..';
$songsDir = $baseDir . '/media/audio/songs';
$metaDir = $baseDir . '/data/songs';
$baseUrl = 'https://cristianos.centralchat.pro';

if (!is_dir($songsDir)) mkdir($songsDir, 0755, true);
if (!is_dir($metaDir)) mkdir($metaDir, 0755, true);

$km = 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/';
$px = 'https://cdn.pixabay.com/audio/';

// All available instrumentals organized by genre
$library = [
    'country' => [
        ['url'=>$km.'Americana.mp3','name'=>'Americana Cristiana'],
        ['url'=>$km.'Daily%20Beetle.mp3','name'=>'Country Folk'],
        ['url'=>$km.'On%20My%20Way.mp3','name'=>'En Mi Camino'],
        ['url'=>$km.'Easy%20Lemon.mp3','name'=>'País Tranquilo'],
        ['url'=>$km.'Local%20Forecast.mp3','name'=>'Brisa del Campo'],
    ],
    'rock' => [
        ['url'=>$km.'Inspired.mp3','name'=>'Roca Inspirada'],
        ['url'=>$px.'2022/01/18/audio_d0a13f69d2.mp3','name'=>'Rock Épico'],
        ['url'=>$px.'2022/11/22/audio_febc508520.mp3','name'=>'Himno de Roca'],
        ['url'=>$km.'Life%20of%20Riley.mp3','name'=>'Vida en la Roca'],
    ],
    'gospel' => [
        ['url'=>$km.'Wholesome.mp3','name'=>'Gospel Pleno'],
        ['url'=>$km.'Amazing%20Plan.mp3','name'=>'Plan Asombroso'],
        ['url'=>$km.'Groove%20Grove.mp3','name'=>'Gospel Groove'],
        ['url'=>$km.'Impact%20Prelude.mp3','name'=>'Preludio de Impacto'],
    ],
    'folk' => [
        ['url'=>$km.'Americana.mp3','name'=>'Folk Cristiano'],
        ['url'=>$km.'Garden%20Music.mp3','name'=>'Jardín Musical'],
        ['url'=>$km.'Perspectives.mp3','name'=>'Perspectivas de Fe'],
        ['url'=>$km.'Dirt%20Rhodes.mp3','name'=>'Sendero Acústico'],
    ],
    'worship' => [
        ['url'=>$km.'Gymnopedie%20No%201.mp3','name'=>'Piano de Adoración'],
        ['url'=>$km.'Peaceful%20Desolation.mp3','name'=>'Adoración Pacífica'],
        ['url'=>$px.'2022/02/22/audio_d1718ab41b.mp3','name'=>'Meditación Celestial'],
        ['url'=>$px.'2021/11/25/audio_91b32e02f9.mp3','name'=>'Adoración Ambiental'],
        ['url'=>$km.'Eternal%20Hope.mp3','name'=>'Esperanza Eterna'],
    ],
    'ballad' => [
        ['url'=>$px.'2022/08/03/audio_54ca0ffa52.mp3','name'=>'Balada de Piano'],
        ['url'=>$km.'At%20Rest.mp3','name'=>'Descanso en Dios'],
        ['url'=>$km.'Eternal%20Hope.mp3','name'=>'Balada de Esperanza'],
        ['url'=>$px.'2022/05/27/audio_1808fbf07a.mp3','name'=>'Balada Pacífica'],
        ['url'=>$px.'2021/11/25/audio_91b32e02f9.mp3','name'=>'Emociones del Alma'],
    ],
];

// Titles per genre (will combine with moods for variety)
$titles = [
    'country' => ['Campos de Gracia','Senderos del Señor','Cosecha Divina','Atardecer Sagrado','Praderas de Fe',
        'Camino de Bendición','Valle Celestial','Rocío de la Mañana','Río Sereno','Horizonte de Paz',
        'Tierra Prometida','Sembrador de Amor','Lluvia de Bondad','Cielo Abierto','Cabaña del Pastor',
        'Trigo del Espíritu','Maíz Dorado','Viento del Sur','Frontera Sagrada','Galope de Fe',
        'Puente de Gracia','Amanecer Rural','Estrellas del Rancho','Colina de Esperanza','Senda Luminosa',
        'Raíces Profundas','Surcos de Oración','Girasol del Alma','Sol de Justicia','Primavera Divina',
        'Campos Eternos','Rebaño Fiel','Lluvia Temprana'],
    'rock' => ['Roca Firme','Escudo de Fe','Guerrero del Cielo','Trueno de Gloria','Fuego del Espíritu',
        'Montaña Invencible','León de Judá','Espada del Señor','Terremoto de Fe','Despertar Poderoso',
        'Tormenta Sagrada','Armadura de Dios','Batalla Celestial','Victoria Final','Poder Infinito',
        'Fortaleza Divina','Cadenas Rotas','Libertad en Cristo','Rugido del Rey','Llamarada de Fe',
        'Muralla Inquebrantable','Ejército Celestial','Trueno Sagrado','Volcán de Alabanza','Acero Divino',
        'Relámpago de Gracia','Impacto Celestial','Fuerza Interior','Coraza del Señor','Tsunami de Fe',
        'Avalancha de Gloria','Tornado Divino','Cañón de Alabanza'],
    'gospel' => ['Aleluya Poderoso','Coros del Cielo','Gloria al Rey','Manos Alzadas','Júbilo Eterno',
        'Alabanza sin Fin','Voces Celestiales','Trompetas de Sion','Gozo del Señor','Celebración Divina',
        'Danza del Espíritu','Palmas al Cielo','Hosanna','Proclamación de Fe','Fiesta en el Cielo',
        'Ovación Celestial','Cántico Nuevo','Grito de Victoria','Regocijo Sagrado','Explosión de Gozo',
        'Maranata','Pentecostés','Avivamiento','Unción Fresca','Fuego Nuevo',
        'Río de Alabanza','Lluvia del Espíritu','Corona de Gozo','Bandera de Victoria','Tambores de Sion',
        'Cuerdas del Alma','Pandero Sagrado','Shofar Celestial'],
    'folk' => ['Bosque del Alma','Hojas de Oración','Río Cristalino','Sendero del Peregrino','Brisa del Edén',
        'Arroyo de Paz','Raíces de Fe','Fogata Sagrada','Caminar con Dios','Puente de Madera',
        'Cabaña en el Monte','Flor Silvestre','Nido del Espíritu','Pluma del Ángel','Madera Noble',
        'Cuerdas del Corazón','Guitarra del Pastor','Hamaca del Cielo','Columpio del Alma','Cascabel Divino',
        'Telar de Gracia','Bordado Celestial','Arcilla del Alfarero','Cerámica del Señor','Canasta de Frutas',
        'Molino de Viento','Cántaro Sagrado','Vela Encendida','Huerto del Señor','Tejido de Amor',
        'Canoa de Fe','Velero del Espíritu','Anzuelo de Gracia'],
    'worship' => ['Altar de Adoración','Trono de Gracia','Santo Santo Santo','Presencia Divina','Río de Adoración',
        'Santuario del Alma','Incienso Celestial','Susurro de Dios','Intimidad Sagrada','Velo Rasgado',
        'Arca de la Alianza','Tabernáculo','Cáliz de Amor','Manto de Gloria','Shekinah',
        'Majestad Eterna','Rostro del Señor','Pies del Maestro','Ofrenda del Corazón','Nube de Gloria',
        'Fuente de Vida','Agua Viva','Pan del Cielo','Luz del Mundo','Lirio del Valle',
        'Rosa de Sarón','Estrella de la Mañana','Príncipe de Paz','Cordero Santo','Emanuel',
        'Nombre sobre Todo','Alfa y Omega','Rey de Reyes'],
    'ballad' => ['Lágrimas de Gozo','Abrazo del Padre','Canción de Cuna','Carta al Cielo','Suspiro del Alma',
        'Promesa Cumplida','Esperando al Señor','Noche Estrellada','Luna de Gracia','Reflejo Divino',
        'Silueta del Ángel','Sombra del Altísimo','Fragancia de Cristo','Perla del Cielo','Diamante Eterno',
        'Cristal del Alma','Terciopelo Sagrado','Seda del Espíritu','Brisa Nocturna','Amanecer de Amor',
        'Atardecer Sagrado','Crepúsculo Divino','Aurora de Fe','Madrugada Santa','Mediodía del Alma',
        'Tarde de Gracia','Noche de Paz','Hora Sagrada','Momento Divino','Instante Eterno',
        'Eco del Cielo','Melodía del Padre','Arrullo Celestial'],
];

$moods = ['peaceful','joyful','powerful','reflective','uplifting'];
$themes = ['faith','hope','love','peace','gratitude','strength'];

$created = 0;
$failed = 0;
$genreCount = [];

foreach ($library as $genre => $tracks) {
    $genreTitles = $titles[$genre];
    $titleIdx = 0;
    
    // Create ~33 entries per genre (200 / 6 genres)
    for ($i = 0; $i < 33 && $titleIdx < count($genreTitles); $i++) {
        $track = $tracks[$i % count($tracks)]; // Cycle through available tracks
        $title = $genreTitles[$titleIdx++];
        $mood = $moods[$i % count($moods)];
        $theme = $themes[$i % count($themes)];
        
        // Generate unique ID
        $id = substr(md5($genre . $title . $i), 0, 10);
        $metaFile = $metaDir . '/' . $id . '.json';
        
        // Skip if already exists
        if (file_exists($metaFile)) {
            echo "SKIP: $title (exists)\n";
            $created++;
            continue;
        }
        
        // Download audio
        $audioFile = $songsDir . '/' . $id . '.mp3';
        if (!file_exists($audioFile)) {
            $ch = curl_init($track['url']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0'
            ]);
            $data = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if (!$data || $code !== 200 || strlen($data) < 10000) {
                echo "FAIL: $title ($genre) - download failed (code $code, size " . strlen($data ?: '') . ")\n";
                $failed++;
                continue;
            }
            file_put_contents($audioFile, $data);
        }
        
        // Get duration
        $dur = 0;
        $probeDur = trim(shell_exec('ffprobe -v error -show_entries format=duration -of csv=p=0 ' . escapeshellarg($audioFile) . ' 2>/dev/null') ?: '');
        if ($probeDur) $dur = floatval($probeDur);
        
        // Build metadata
        $meta = [
            'id' => $id,
            'title' => $title,
            'lyrics' => '',
            'tags' => $genre . ', christian, instrumental, ' . $mood,
            'duration' => $dur,
            'audioUrl' => $baseUrl . '/media/audio/songs/' . $id . '.mp3',
            'videoUrl' => '',
            'imageUrl' => '',
            'shareUrl' => $baseUrl . '/share.php?id=' . $id,
            'slideImages' => [],
            'creator' => 'FaithTunes',
            'createdAt' => date('Y-m-d H:i:s'),
            'taskId' => '',
            'genre' => $genre,
            'mood' => $mood,
            'theme' => $theme,
            'instrumental' => true,
        ];
        
        file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $created++;
        if (!isset($genreCount[$genre])) $genreCount[$genre] = 0;
        $genreCount[$genre]++;
        
        echo "OK: [$genre] $title ($id) - " . round($dur) . "s\n";
    }
}

echo "\n=== DONE ===\n";
echo "Created: $created\n";
echo "Failed: $failed\n";
foreach ($genreCount as $g => $c) echo "  $g: $c\n";
