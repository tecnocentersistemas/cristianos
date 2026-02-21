<?php
$id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id'] ?? '');
$metaDir = __DIR__ . '/data/songs';
$song = ($id && file_exists($metaDir.'/'.$id.'.json')) ? json_decode(file_get_contents($metaDir.'/'.$id.'.json'), true) : null;
$t = $song ? ($song['title'] ?? 'FaithTunes') : 'FaithTunes';
$a = $song['audioUrl'] ?? ''; $v = $song['videoUrl'] ?? ''; $cr = $song['creator'] ?? ''; $ly = $song['lyrics'] ?? '';
$url = 'https://cristianos.centralchat.pro/share.php?id='.$id;
$lc = preg_replace('/\[[^\]]+\]\n?/','',$ly); $lc = str_replace("\n",' / ',trim($lc));
if(strlen($lc)>180) $lc=substr($lc,0,177).'...';
$desc = $lc ?: 'Canción cristiana creada con IA en FaithTunes';

// Best image: check cover first, fallback to slideImages, then default
$im = '';
$coverFile = __DIR__ . '/media/audio/songs/' . $id . '_cover.jpg';
if (file_exists($coverFile) && filesize($coverFile) > 5000) {
    $im = 'https://cristianos.centralchat.pro/media/audio/songs/' . $id . '_cover.jpg';
}
if (!$im && !empty($song['slideImages'][0])) {
    $im = $song['slideImages'][0];
}
if (!$im && !empty($song['imageUrl'])) {
    $im = $song['imageUrl'];
}
if (!$im) {
    $im = 'https://cristianos.centralchat.pro/media/images/og-default.jpg';
}

$hv = !empty($v);
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($t)?> - FaithTunes</title>
<?php if($hv):?>
<meta property="og:type" content="video.other">
<meta property="og:video" content="<?=htmlspecialchars($v)?>">
<meta property="og:video:type" content="video/mp4">
<meta property="og:video:width" content="1280">
<meta property="og:video:height" content="720">
<?php else:?>
<meta property="og:type" content="music.song">
<?php if($a):?><meta property="og:audio" content="<?=htmlspecialchars($a)?>"><?php endif;?>
<?php endif;?>
<meta property="og:title" content="<?=htmlspecialchars($t)?> - FaithTunes">
<meta property="og:description" content="<?=htmlspecialchars($desc)?>">
<meta property="og:url" content="<?=htmlspecialchars($url)?>">
<meta property="og:image" content="<?=htmlspecialchars($im)?>">
<meta property="og:image:width" content="1280">
<meta property="og:image:height" content="720">
<meta property="og:site_name" content="FaithTunes">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?=htmlspecialchars($t)?> - FaithTunes">
<meta name="twitter:description" content="<?=htmlspecialchars($desc)?>">
<meta name="twitter:image" content="<?=htmlspecialchars($im)?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Inter',sans-serif;background:#0f172a;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}.c{max-width:520px;width:100%;background:#1e293b;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5)}.cm{width:100%;background:#000}.cm video,.cm img{width:100%;display:block}.cb{padding:1.5rem}.br{color:#d97706;font-size:.8rem;font-weight:600;margin-bottom:.5rem}.ct{font-size:1.4rem;font-weight:800;margin-bottom:.3rem}.cc{color:#94a3b8;font-size:.8rem;margin-bottom:1rem}audio{width:100%;margin:.5rem 0 1rem}.ly{background:rgba(217,119,6,.08);border-left:3px solid #d97706;padding:.75rem 1rem;border-radius:0 8px 8px 0;font-size:.82rem;line-height:1.7;max-height:400px;overflow-y:auto;margin-bottom:1rem;color:#cbd5e1}.ls{color:#d97706;font-weight:700;font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;margin-top:.6rem;margin-bottom:.2rem}.bs{display:flex;gap:.5rem;flex-wrap:wrap}.b{flex:1;min-width:100px;padding:.7rem;border:none;border-radius:10px;font-family:inherit;font-weight:600;font-size:.85rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem;text-decoration:none;color:#fff}.bv{background:linear-gradient(135deg,#d97706,#7c3aed)}.ba{background:rgba(255,255,255,.1)}.bw{background:#25d366}.bf{background:#1877f2}.bt{background:#1da1f2}.bcopy{background:rgba(255,255,255,.15)}.bc{background:linear-gradient(135deg,#22c55e,#059669);margin-top:.5rem;width:100%}</style>
</head><body><div class="c"><div class="cm">
<?php if($hv):?><video controls poster="<?=htmlspecialchars($im)?>"><source src="<?=htmlspecialchars($v)?>" type="video/mp4"></video>
<?php elseif($im):?><img src="<?=htmlspecialchars($im)?>" alt="<?=htmlspecialchars($t)?>"><?php endif;?>
</div><div class="cb"><div class="br"><i class="fas fa-music"></i> FaithTunes</div><div class="ct"><?=htmlspecialchars($t)?></div>
<?php if($cr):?><div class="cc">Creado por: <?=htmlspecialchars($cr)?></div><?php endif;?>
<?php if($a):?><audio controls src="<?=htmlspecialchars($a)?>"></audio><?php endif;?>
<?php if($ly):?><div class="ly"><?php
  $lyLines = explode("\n", $ly);
  foreach ($lyLines as $line) {
    $line = trim($line);
    if (preg_match('/^\[(.+)\]$/', $line, $m)) {
      echo '<div class="ls">' . htmlspecialchars($m[1]) . '</div>';
    } elseif ($line !== '') {
      echo htmlspecialchars($line) . '<br>';
    } else {
      echo '<br>';
    }
  }
?></div><?php endif;?>
<div class="bs">
<?php if($hv):?><a class="b bv" href="<?=htmlspecialchars($v)?>" download><i class="fas fa-video"></i> Video</a><?php endif;?>
<?php if($a):?><a class="b ba" href="<?=htmlspecialchars($a)?>" download><i class="fas fa-music"></i> Audio</a><?php endif;?>
</div>
<div class="bs" style="margin-top:0.5rem">
<a class="b bw" href="https://wa.me/?text=<?=urlencode($t.' - Canción cristiana con IA'."\n\n".$url)?>" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
<a class="b bf" href="https://www.facebook.com/sharer/sharer.php?u=<?=urlencode($url)?>" target="_blank"><i class="fab fa-facebook"></i> Facebook</a>
</div>
<div class="bs" style="margin-top:0.5rem">
<a class="b bt" href="https://twitter.com/intent/tweet?text=<?=urlencode($t.' - Canción cristiana creada con IA')?>&url=<?=urlencode($url)?>" target="_blank"><i class="fab fa-twitter"></i> Twitter</a>
<button class="b bcopy" onclick="navigator.clipboard.writeText('<?=htmlspecialchars($t)?> - FaithTunes\n<?=htmlspecialchars($url)?>').then(function(){document.querySelector('.bcopy').innerHTML='<i class=\'fas fa-check\'></i> Copiado!'})"><i class="fas fa-link"></i> Copiar</button>
</div>
<a class="b bc" href="/creator.html"><i class="fas fa-plus"></i> Crear mi cancion</a>
</div></div></body></html>
