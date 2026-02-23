<?php
/**
 * Pre-generate enhanced catalog with Pexels images for each song.
 * Run ONCE: curl https://cristianos.centralchat.pro/api/generate-catalog.php
 * This populates data/catalog-enhanced.json with real images per song.
 */
header('Content-Type: application/json; charset=utf-8');
set_time_limit(300);

function loadKey($name) {
    $paths = [__DIR__.'/../.'.$name, '/var/www/cristianos/.'.$name];
    foreach ($paths as $p) { if (file_exists($p)) { $k = trim(file_get_contents($p)); if ($k) return $k; } }
    return getenv(strtoupper(str_replace('-','_',$name))) ?: null;
}

function searchPexels($key, $query, $count = 5) {
    $url = 'https://api.pexels.com/v1/search?' . http_build_query([
        'query' => $query, 'per_page' => $count + 3, 'orientation' => 'landscape', 'size' => 'large'
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Authorization: ' . $key], CURLOPT_TIMEOUT => 10]);
    $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code !== 200) return [];
    $data = json_decode($resp, true);
    $images = [];
    foreach (($data['photos'] ?? []) as $photo) {
        $images[] = [
            'url' => $photo['src']['landscape'] ?? $photo['src']['large'],
            'alt' => $photo['alt'] ?: $query,
            'credit' => $photo['photographer'] ?? ''
        ];
        if (count($images) >= $count) break;
    }
    return $images;
}

$pexelsKey = loadKey('pexels-key');
if (!$pexelsKey) { echo json_encode(['error' => 'No Pexels API key']); exit; }

// Base Incompetech URL
$km = 'https://incompetech.com/music/royalty-free/mp3-royaltyfree/';

// Audio library - carefully matched to genre feel
$audioLib = [
    'country' => [
        $km.'Americana.mp3',
        $km.'Fretless.mp3',
        $km.'Americana.mp3',
        $km.'Bass%20Walker.mp3',
        $km.'Americana.mp3',
    ],
    'rock' => [
        $km.'Crusade.mp3',
        $km.'Five%20Armies.mp3',
        $km.'Hero%20Theme.mp3',
        $km.'Impact%20Prelude.mp3',
        $km.'Volatile%20Reaction.mp3',
    ],
    'gospel' => [
        $km.'Wholesome.mp3',
        $km.'Amazing%20Plan.mp3',
        $km.'Bright%20Wish.mp3',
        $km.'Sovereign.mp3',
        $km.'Lasting%20Hope.mp3',
    ],
    'folk' => [
        $km.'Garden%20Music.mp3',
        $km.'Perspectives.mp3',
        $km.'Thatched%20Villagers.mp3',
        $km.'Dreamer.mp3',
        $km.'Carefree.mp3',
    ],
    'worship' => [
        $km.'Gymnopedie%20No%201.mp3',
        $km.'Canon%20in%20D%20Major.mp3',
        $km.'Peaceful%20Desolation.mp3',
        $km.'Eternal%20Hope.mp3',
        $km.'Bathed%20in%20the%20Light.mp3',
    ],
    'ballad' => [
        $km.'Heartbreaking.mp3',
        $km.'Bittersweet.mp3',
        $km.'At%20Rest.mp3',
        $km.'Gymnopedie%20No%201.mp3',
        $km.'Dreamer.mp3',
    ],
];

// Search terms per song (English, for Pexels API)
$searchTerms = [
    // Country (5)
    1 => ['snowy mountain landscape sunrise', 'mountain valley panoramic view', 'golden sunrise mountain peaks', 'countryside ranch field', 'rural road landscape'],
    2 => ['green valley wildflowers field', 'rolling hills spring flowers', 'countryside meadow sunlight', 'pastoral landscape green', 'wildflower field sunset'],
    3 => ['deer meadow morning dawn', 'deer forest golden light', 'misty morning prairie', 'dew grass sunrise field', 'peaceful nature morning'],
    4 => ['wheat field golden harvest', 'vineyard ripe grapes autumn', 'barn countryside golden hour', 'harvest abundance farmland', 'autumn fields countryside'],
    5 => ['desert bloom wildflowers', 'cactus flower sunset desert', 'desert oasis landscape', 'arid landscape sunset colors', 'desert sunrise beauty'],
    // Rock (5)
    6 => ['eagle soaring mountain sky', 'bird of prey flying dramatic clouds', 'eagle wings spread flight', 'dramatic sky mountain valley', 'majestic eagle freedom'],
    7 => ['dramatic sky infinite clouds', 'sunbeams through storm clouds', 'epic sky landscape power', 'thunderstorm dramatic light', 'majestic sky panorama'],
    8 => ['imposing rock cliff formation', 'dramatic canyon landscape', 'rugged cliff face stone', 'mountain rock fortress wall', 'stone arch natural monument'],
    9 => ['snowy mountain summit victory', 'climber mountain peak triumph', 'panoramic mountain view clouds', 'mountain range snow epic', 'summit sunrise golden'],
    10 => ['powerful waterfall torrent river', 'river rapids rushing water', 'cascading water rocks power', 'canyon river flowing fast', 'waterfall mist power nature'],
    // Gospel (5)
    11 => ['lamb meadow green peaceful', 'sheep grazing pastoral light', 'golden meadow sunlight lamb', 'pastoral scene countryside', 'peaceful farmland animals'],
    12 => ['white dove flying blue sky', 'peace dove olive branch', 'doves flight serene garden', 'white bird flying freedom', 'peaceful garden birds'],
    13 => ['majestic lion savanna sunset', 'lion golden mane pride', 'african savanna lion portrait', 'powerful lion dramatic light', 'lion king wilderness'],
    14 => ['rainbow valley after rain storm', 'double rainbow landscape mountains', 'rainbow sky dramatic clouds', 'beautiful rainbow countryside', 'storm clearing rainbow hope'],
    15 => ['sheep flock green hills shepherd', 'pastoral valley sheep grazing', 'hillside flock peaceful countryside', 'shepherd caring flock valley', 'green rolling hills livestock'],
    // Folk (5)
    16 => ['lush forest light rays trees', 'sunbeams forest path mystical', 'enchanted forest trail green', 'tall trees forest canopy light', 'misty forest morning'],
    17 => ['forest trail illuminated path', 'sunlit woodland path autumn', 'forest bridge stream crossing', 'nature trail peaceful forest', 'woods path golden light'],
    18 => ['flowering garden butterflies colorful', 'beautiful garden roses path', 'garden bench peaceful flowers', 'butterfly flower macro nature', 'serene garden fountain'],
    19 => ['hummingbird tropical flower vibrant', 'colorful bird flower garden', 'tropical flowers exotic garden', 'nature garden vibrant colors', 'hummingbird hovering nectar'],
    20 => ['monarch butterfly metamorphosis flower', 'blue butterfly sunlight nature', 'butterfly field wildflowers', 'beautiful butterfly garden spring', 'transformation nature beauty'],
    // Worship (5)
    21 => ['crystal clear river forest', 'waterfall serene tranquil lake', 'peaceful stream forest calm', 'river valley mountain peaceful', 'pristine water nature calm'],
    22 => ['golden sunset dramatic sky purple', 'sunset ocean purple gold', 'wheat field sunset golden', 'dramatic sunset clouds colorful', 'sunset silhouette worship'],
    23 => ['majestic waterfall tropical lush', 'cascade crystal water rocks', 'waterfall mist vegetation green', 'flowing water grace nature', 'beautiful waterfall landscape'],
    24 => ['ocean waves horizon infinity', 'rocky coast dramatic seascape', 'sea horizon sunrise peaceful', 'ocean sunset beach golden', 'vast ocean mercy majesty'],
    25 => ['aurora borealis northern lights sky', 'nordic sky aurora green purple', 'ice landscape aurora borealis', 'celestial night sky northern lights', 'divine sky lights nature'],
    // Ballad (5)
    26 => ['spectacular sunrise colorful sky', 'dawn colors morning horizon', 'sunrise clouds orange purple', 'morning sky golden horizon', 'beautiful sunrise nature'],
    27 => ['golden wheat field wind endless', 'harvest wheat abundant field', 'farmland golden hour beauty', 'wheat field open sky blessing', 'countryside fields gratitude'],
    28 => ['starry night sky milky way', 'moonlight landscape peaceful night', 'galaxy stars night sky', 'starry sky mountain silhouette', 'night sky constellations beauty'],
    29 => ['bird nest caring chicks tree', 'birds nesting protective tree', 'baby birds nest nature', 'ancient oak tree protective', 'mother bird feeding chicks'],
    30 => ['rural road warm sunset home', 'country house light evening', 'path home sunset warm', 'evening glow countryside road', 'peaceful village sunset walk'],
];

// Bible verses with full text per song
$verseTexts = [
    1 => [['ref'=>'Hebreos 11:1','text'=>'La fe es la certeza de lo que se espera, la convicción de lo que no se ve.'],['ref'=>'Proverbios 3:5','text'=>'Confía en el Señor de todo corazón, y no te apoyes en tu propio entendimiento.'],['ref'=>'Salmo 121:1-2','text'=>'Alzaré mis ojos a los montes; ¿de dónde vendrá mi socorro? Mi socorro viene del Señor.']],
    2 => [['ref'=>'1 Juan 4:16','text'=>'Dios es amor, y el que permanece en amor, permanece en Dios y Dios en él.'],['ref'=>'Romanos 8:38-39','text'=>'Nada nos podrá separar del amor de Dios que es en Cristo Jesús.'],['ref'=>'Sofonías 3:17','text'=>'El Señor tu Dios está en medio de ti como guerrero victorioso. Se deleitará en ti con gozo.']],
    3 => [['ref'=>'Habacuc 3:19','text'=>'El Señor es mi fortaleza; da a mis pies la ligereza del ciervo.'],['ref'=>'Isaías 40:31','text'=>'Los que esperan en el Señor renovarán sus fuerzas; volarán como las águilas.'],['ref'=>'Lamentaciones 3:22-23','text'=>'Las misericordias del Señor son nuevas cada mañana; grande es tu fidelidad.']],
    4 => [['ref'=>'Gálatas 6:9','text'=>'No nos cansemos de hacer el bien, porque a su debido tiempo cosecharemos.'],['ref'=>'Salmo 126:5','text'=>'Los que sembraron con lágrimas, con regocijo segarán.'],['ref'=>'Salmo 65:11','text'=>'Tú coronas el año con tus bienes, y tus sendas destilan abundancia.']],
    5 => [['ref'=>'Isaías 43:19','text'=>'He aquí que yo hago cosa nueva; ahora saldrá a luz. ¿No la conoceréis?'],['ref'=>'Salmo 62:5','text'=>'Alma mía, en Dios solamente reposa, porque de él es mi esperanza.'],['ref'=>'Apocalipsis 21:4','text'=>'Enjugará Dios toda lágrima de los ojos de ellos; ya no habrá muerte.']],
    6 => [['ref'=>'Isaías 40:31','text'=>'Los que esperan en el Señor renovarán sus fuerzas; volarán como las águilas.'],['ref'=>'Jeremías 29:11','text'=>'Porque yo sé los pensamientos que tengo acerca de vosotros, pensamientos de paz y no de mal.'],['ref'=>'Salmo 27:1','text'=>'El Señor es mi luz y mi salvación; ¿de quién temeré?']],
    7 => [['ref'=>'Salmo 46:10','text'=>'Estad quietos y conoced que yo soy Dios.'],['ref'=>'Josué 1:9','text'=>'Sé fuerte y valiente; no temas. El Señor tu Dios estará contigo.'],['ref'=>'2 Timoteo 1:7','text'=>'No nos ha dado Dios espíritu de cobardía, sino de poder, de amor y de dominio propio.']],
    8 => [['ref'=>'Salmo 18:2','text'=>'El Señor es mi roca, mi fortaleza y mi libertador.'],['ref'=>'Salmo 46:1','text'=>'Dios es nuestro amparo y fortaleza, nuestro pronto auxilio en las tribulaciones.'],['ref'=>'Efesios 6:10','text'=>'Fortaleceos en el Señor y en el poder de su fuerza.']],
    9 => [['ref'=>'1 Corintios 15:57','text'=>'Gracias sean dadas a Dios, que nos da la victoria por medio de nuestro Señor Jesucristo.'],['ref'=>'Salmo 31:24','text'=>'Esforzaos todos vosotros los que esperáis en el Señor, y tome aliento vuestro corazón.'],['ref'=>'Deuteronomio 31:6','text'=>'Esforzaos y cobrad ánimo; el Señor tu Dios es el que va contigo.']],
    10 => [['ref'=>'Salmo 95:2','text'=>'Lleguemos ante su presencia con alabanza; aclamémosle con cánticos.'],['ref'=>'Salmo 150:6','text'=>'Todo lo que respira alabe al Señor. ¡Aleluya!'],['ref'=>'Efesios 5:20','text'=>'Dando siempre gracias por todo al Dios y Padre, en el nombre de nuestro Señor Jesucristo.']],
    11 => [['ref'=>'Hebreos 11:6','text'=>'Sin fe es imposible agradar a Dios; es necesario que el que se acerca a Dios crea que él existe.'],['ref'=>'2 Corintios 5:7','text'=>'Porque por fe andamos, no por vista.'],['ref'=>'Efesios 2:8','text'=>'Por gracia sois salvos por medio de la fe; y esto no de vosotros, pues es don de Dios.']],
    12 => [['ref'=>'Mateo 5:9','text'=>'Bienaventurados los pacificadores, porque serán llamados hijos de Dios.'],['ref'=>'Colosenses 3:15','text'=>'La paz de Cristo gobierne en vuestros corazones.'],['ref'=>'Salmo 34:14','text'=>'Busca la paz y síguela.']],
    13 => [['ref'=>'Filipenses 4:13','text'=>'Todo lo puedo en Cristo que me fortalece.'],['ref'=>'Isaías 41:10','text'=>'No temas, porque yo estoy contigo; no desmayes, porque yo soy tu Dios.'],['ref'=>'Nehemías 8:10','text'=>'El gozo del Señor es vuestra fuerza.']],
    14 => [['ref'=>'Romanos 5:5','text'=>'La esperanza no avergüenza; porque el amor de Dios ha sido derramado en nuestros corazones.'],['ref'=>'Tito 2:13','text'=>'Aguardando la esperanza bienaventurada y la manifestación gloriosa.'],['ref'=>'Salmo 71:5','text'=>'Porque tú, oh Señor, eres mi esperanza; seguridad mía desde mi juventud.']],
    15 => [['ref'=>'Salmo 23:1-3','text'=>'El Señor es mi pastor; nada me faltará. En lugares de verdes pastos me hará descansar.'],['ref'=>'Juan 10:11','text'=>'Yo soy el buen pastor; el buen pastor da su vida por las ovejas.'],['ref'=>'Isaías 40:11','text'=>'Como pastor apacentará su rebaño; llevará los corderitos en su seno.']],
    16 => [['ref'=>'Jeremías 29:11','text'=>'Porque yo sé los pensamientos que tengo acerca de vosotros, pensamientos de paz.'],['ref'=>'Isaías 43:19','text'=>'He aquí que yo hago cosa nueva; ahora saldrá a luz.'],['ref'=>'1 Pedro 1:3','text'=>'Nos hizo renacer para una esperanza viva.']],
    17 => [['ref'=>'Salmo 119:105','text'=>'Lámpara es a mis pies tu palabra, y lumbrera a mi camino.'],['ref'=>'Juan 8:12','text'=>'Yo soy la luz del mundo; el que me sigue no andará en tinieblas.'],['ref'=>'Salmo 25:4','text'=>'Muéstrame, oh Señor, tus caminos; enséñame tus sendas.']],
    18 => [['ref'=>'Salmo 4:8','text'=>'En paz me acostaré y así también dormiré; porque solo tú, Señor, me haces vivir confiado.'],['ref'=>'Isaías 32:17','text'=>'El efecto de la justicia será paz; la labor de la justicia, reposo y seguridad.'],['ref'=>'Romanos 8:6','text'=>'La intención del Espíritu es vida y paz.']],
    19 => [['ref'=>'Salmo 145:3','text'=>'Grande es el Señor y digno de suprema alabanza; su grandeza es inescrutable.'],['ref'=>'Salmo 9:1','text'=>'Te alabaré, oh Señor, con todo mi corazón; contaré todas tus maravillas.'],['ref'=>'1 Crónicas 16:34','text'=>'Alabad al Señor porque él es bueno; porque su misericordia es eterna.']],
    20 => [['ref'=>'2 Corintios 5:17','text'=>'Si alguno está en Cristo, nueva criatura es; las cosas viejas pasaron, todas son hechas nuevas.'],['ref'=>'Romanos 12:2','text'=>'Transformaos por la renovación de vuestro entendimiento.'],['ref'=>'Efesios 4:24','text'=>'Vestíos del nuevo hombre, creado según Dios en justicia y santidad.']],
    21 => [['ref'=>'Juan 14:27','text'=>'La paz os dejo, mi paz os doy; no como el mundo la da. No se turbe vuestro corazón.'],['ref'=>'Filipenses 4:7','text'=>'La paz de Dios, que sobrepasa todo entendimiento, guardará vuestros corazones.'],['ref'=>'Isaías 26:3','text'=>'Tú guardarás en completa paz a aquel cuyo pensamiento en ti persevera.']],
    22 => [['ref'=>'Salmo 100:4','text'=>'Entrad por sus puertas con acción de gracias, por sus atrios con alabanza.'],['ref'=>'1 Tesalonicenses 5:18','text'=>'Dad gracias en todo, porque esta es la voluntad de Dios para con vosotros.'],['ref'=>'Salmo 118:24','text'=>'Este es el día que hizo el Señor; nos gozaremos y alegraremos en él.']],
    23 => [['ref'=>'Efesios 3:19','text'=>'Conocer el amor de Cristo, que excede a todo conocimiento, para que seáis llenos de la plenitud de Dios.'],['ref'=>'1 Juan 4:19','text'=>'Nosotros amamos porque él nos amó primero.'],['ref'=>'Juan 15:13','text'=>'Nadie tiene mayor amor que este, que uno ponga su vida por sus amigos.']],
    24 => [['ref'=>'Salmo 36:7','text'=>'¡Cuán preciosa es tu misericordia, oh Dios! Los hijos de los hombres se amparan bajo la sombra de tus alas.'],['ref'=>'Cantares 8:7','text'=>'Las muchas aguas no podrán apagar el amor, ni lo ahogarán los ríos.'],['ref'=>'Deuteronomio 7:9','text'=>'Dios fiel, que guarda el pacto y la misericordia a los que le aman.']],
    25 => [['ref'=>'Salmo 19:1','text'=>'Los cielos cuentan la gloria de Dios, y el firmamento anuncia la obra de sus manos.'],['ref'=>'Romanos 1:20','text'=>'Las cosas invisibles de Dios se hacen claramente visibles desde la creación del mundo.'],['ref'=>'Job 37:14','text'=>'Escucha esto; detente y considera las maravillas de Dios.']],
    26 => [['ref'=>'1 Corintios 13:4-5','text'=>'El amor es sufrido, es benigno; el amor no tiene envidia; no es jactancioso, no se envanece.'],['ref'=>'Juan 3:16','text'=>'Porque de tal manera amó Dios al mundo, que ha dado a su Hijo unigénito.'],['ref'=>'Romanos 5:8','text'=>'Mas Dios muestra su amor para con nosotros en que siendo aún pecadores, Cristo murió por nosotros.']],
    27 => [['ref'=>'Salmo 107:1','text'=>'Alabad al Señor, porque él es bueno; porque para siempre es su misericordia.'],['ref'=>'Santiago 1:17','text'=>'Toda buena dádiva y todo don perfecto desciende de lo alto, del Padre de las luces.'],['ref'=>'Salmo 103:2','text'=>'Bendice, alma mía, al Señor, y no olvides ninguno de sus beneficios.']],
    28 => [['ref'=>'Salmo 19:1','text'=>'Los cielos cuentan la gloria de Dios, y el firmamento anuncia la obra de sus manos.'],['ref'=>'Salmo 147:4','text'=>'Él cuenta el número de las estrellas; a todas ellas llama por sus nombres.'],['ref'=>'Salmo 8:3-4','text'=>'Cuando veo tus cielos, obra de tus dedos, la luna y las estrellas que tú formaste.']],
    29 => [['ref'=>'Salmo 91:4','text'=>'Con sus plumas te cubrirá, y debajo de sus alas estarás seguro.'],['ref'=>'Salmo 36:7','text'=>'Los hijos de los hombres se amparan bajo la sombra de tus alas.'],['ref'=>'Mateo 23:37','text'=>'¡Cuántas veces quise juntar a tus hijos, como la gallina junta sus polluelos bajo las alas!']],
    30 => [['ref'=>'Juan 14:2-3','text'=>'En la casa de mi Padre muchas moradas hay; voy a preparar lugar para vosotros.'],['ref'=>'Salmo 84:1-2','text'=>'¡Cuán amables son tus moradas, oh Señor de los ejércitos!'],['ref'=>'Filipenses 3:20','text'=>'Nuestra ciudadanía está en los cielos, de donde esperamos al Salvador.']],
];

// Read current catalog
$catalogPath = __DIR__ . '/../data/catalog.json';
$catalog = json_decode(file_get_contents($catalogPath), true);
if (!$catalog || !$catalog['videos']) { echo json_encode(['error' => 'Cannot read catalog']); exit; }

$enhanced = [];
$genreAudioIdx = [];

foreach ($catalog['videos'] as $song) {
    $id = $song['id'];
    $genre = $song['genre'];

    // Get images from Pexels
    $terms = $searchTerms[$id] ?? ['christian nature landscape', 'peaceful nature scene', 'beautiful sky landscape', 'mountain sunrise golden', 'nature water peaceful'];
    $images = [];
    foreach ($terms as $term) {
        $results = searchPexels($pexelsKey, $term, 1);
        if (!empty($results)) {
            $images[] = $results[0];
        }
        usleep(200000); // 200ms delay to respect rate limit
    }

    // Fallback if Pexels fails
    if (count($images) < 3) {
        $P = 'https://images.pexels.com/photos/';
        $S = '?auto=compress&cs=tinysrgb&w=1280&h=720&fit=crop';
        $fallbacks = [
            $P.'417173/pexels-photo-417173.jpeg'.$S,
            $P.'2098427/pexels-photo-2098427.jpeg'.$S,
            $P.'36717/amazing-animal-beautiful-beauty.jpg'.$S,
            $P.'15286/pexels-photo.jpg'.$S,
            $P.'1166209/pexels-photo-1166209.jpeg'.$S,
        ];
        foreach ($fallbacks as $fb) {
            if (count($images) >= 5) break;
            $images[] = ['url' => $fb, 'alt' => 'Nature', 'credit' => ''];
        }
    }

    // Pick audio for genre
    if (!isset($genreAudioIdx[$genre])) $genreAudioIdx[$genre] = 0;
    $idx = $genreAudioIdx[$genre] % count($audioLib[$genre]);
    $audioUrl = $audioLib[$genre][$idx];
    $genreAudioIdx[$genre]++;

    // Enhanced song
    $song['images'] = $images;
    $song['audioUrl'] = $audioUrl;
    $song['verses'] = $verseTexts[$id] ?? $song['verses'] ?? [];
    $enhanced[] = $song;

    echo json_encode(['progress' => $id, 'title' => $song['title']['es'] ?? '', 'images' => count($images)]) . "\n";
    flush();
}

// Save enhanced catalog
$outputPath = __DIR__ . '/../data/catalog-enhanced.json';
$output = [
    'version' => '3.0.0',
    'lastUpdated' => date('Y-m-d'),
    'totalVideos' => count($enhanced),
    'videos' => $enhanced
];
file_put_contents($outputPath, json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "\n" . json_encode(['success' => true, 'total' => count($enhanced), 'file' => 'data/catalog-enhanced.json']);
