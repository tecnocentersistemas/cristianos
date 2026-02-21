<?php
// Share page - serves Open Graph meta tags for social media
$id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['id'] ?? '');
$metaDir = __DIR__ . '/data/songs';
$song = null;
if ($id && file_exists($metaDir . '/' . $id . '.json')) {
    $song = json_decode(file_get_contents($metaDir . '/' . $id . '.json'), true);
}
$title = $song['title'] ?? 'FaithTunes';
$desc = 'Cancion cristiana creada con IA en FaithTunes';
$audio = $song['audioUrl'] ?? '';
$image = $song['imageUrl'] ?? 'https://cristianos.centralchat.pro/media/images/og-default.jpg';
$url = 'https://cristianos.centralchat.pro/share.php?id=' . $id;
$lyrics = $song['lyrics'] ?? '';
// Clean lyrics for description
$lyricsClean = preg_replace('/\[[^\]]+\]\n?/', '', $lyrics);
$lyricsClean = str_replace("\n", ' | ', trim($lyricsClean));
if (strlen($lyricsClean) > 200) $lyricsClean = substr($lyricsClean, 0, 197) . '...';
if ($lyricsClean) $desc = $lyricsClean;
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?> - FaithTunes</title>
  <!-- Open Graph -->
  <meta property="og:type" content="music.song">
  <meta property="og:title" content="<?= htmlspecialchars($title) ?> - FaithTunes">
  <meta property="og:description" content="<?= htmlspecialchars($desc) ?>">
  <meta property="og:url" content="<?= htmlspecialchars($url) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($image) ?>">
  <meta property="og:audio" content="<?= htmlspecialchars($audio) ?>">
  <meta property="og:audio:type" content="audio/mpeg">
  <meta property="og:site_name" content="FaithTunes">
  <!-- Twitter Card -->
  <meta name="twitter:card" content="player">
  <meta name="twitter:title" content="<?= htmlspecialchars($title) ?> - FaithTunes">
  <meta name="twitter:description" content="<?= htmlspecialchars($desc) ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($image) ?>">
  <meta name="twitter:player" content="<?= htmlspecialchars($audio) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Inter',sans-serif;background:#0f172a;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
    .card{max-width:480px;width:100%;background:#1e293b;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
    .card-img{width:100%;aspect-ratio:1;object-fit:cover;background:#334155}
    .card-body{padding:1.5rem}
    .card-title{font-size:1.3rem;font-weight:700;margin-bottom:0.3rem}
    .card-brand{color:#d97706;font-size:0.8rem;font-weight:600;margin-bottom:1rem}
    audio{width:100%;margin:1rem 0;border-radius:8px}
    .lyrics{background:rgba(217,119,6,0.08);border-left:3px solid #d97706;padding:0.75rem 1rem;border-radius:0 8px 8px 0;font-size:0.82rem;line-height:1.7;max-height:200px;overflow-y:auto;margin-bottom:1rem;white-space:pre-line}
    .btns{display:flex;gap:0.5rem}
    .btn{flex:1;padding:0.7rem;border:none;border-radius:10px;font-family:inherit;font-weight:600;font-size:0.85rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.4rem;text-decoration:none}
    .btn-primary{background:linear-gradient(135deg,#d97706,#7c3aed);color:white}
    .btn-secondary{background:rgba(255,255,255,0.1);color:#f1f5f9}
  </style>
</head>
<body>
  <div class="card">
    <?php if ($image): ?><img class="card-img" src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>"><?php endif; ?>
    <div class="card-body">
      <div class="card-brand"><i class="fas fa-music"></i> FaithTunes - Musica Cristiana con IA</div>
      <div class="card-title"><?= htmlspecialchars($title) ?></div>
      <?php if ($audio): ?><audio controls preload="auto" src="<?= htmlspecialchars($audio) ?>"></audio><?php endif; ?>
      <?php if ($lyrics): ?><div class="lyrics"><?= nl2br(htmlspecialchars(preg_replace('/\[[^\]]+\]\n?/', '', $lyrics))) ?></div><?php endif; ?>
      <div class="btns">
        <?php if ($audio): ?><a class="btn btn-primary" href="<?= htmlspecialchars($audio) ?>" download><i class="fas fa-download"></i> Descargar</a><?php endif; ?>
        <a class="btn btn-secondary" href="https://cristianos.centralchat.pro/creator.html"><i class="fas fa-plus"></i> Crear otra</a>
      </div>
    </div>
  </div>
</body>
</html>
