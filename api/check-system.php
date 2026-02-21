<?php
header('Content-Type: application/json');
$ffmpeg = trim(shell_exec('which ffmpeg 2>/dev/null') ?: '');
$version = $ffmpeg ? trim(shell_exec($ffmpeg . ' -version 2>&1 | head -1') ?: '') : 'not installed';
$dirs = [
    'songs' => is_dir(__DIR__ . '/../media/audio/songs') && is_writable(__DIR__ . '/../media/audio/songs'),
    'videos' => is_dir(__DIR__ . '/../media/videos') && is_writable(__DIR__ . '/../media/videos'),
    'slides' => is_dir(__DIR__ . '/../media/images/slides') && is_writable(__DIR__ . '/../media/images/slides'),
    'meta' => is_dir(__DIR__ . '/../data/songs') && is_writable(__DIR__ . '/../data/songs'),
];
$existingSongs = glob(__DIR__ . '/../data/songs/*.json');
echo json_encode([
    'ffmpeg' => $ffmpeg ?: 'not found',
    'version' => $version,
    'dirs' => $dirs,
    'songsCount' => count($existingSongs ?: []),
    'phpUser' => posix_getpwuid(posix_geteuid())['name'] ?? 'unknown'
], JSON_PRETTY_PRINT);
