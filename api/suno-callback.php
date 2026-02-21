<?php
// Suno API Callback - receives generated song results at stages: text, first, complete
header('Content-Type: application/json');

$body = file_get_contents('php://input');
$data = json_decode($body, true);

$logDir = __DIR__ . '/../data/suno-cache';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

$taskId = $data['data']['taskId'] ?? $data['taskId'] ?? 'unknown_' . time();
$callbackType = $data['data']['callbackType'] ?? $data['callbackType'] ?? 'unknown';

// Save each callback stage to its own file AND overwrite the main file
$logFile = $logDir . '/' . $taskId . '.json';
$stageFile = $logDir . '/' . $taskId . '_' . $callbackType . '.json';
file_put_contents($stageFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Only overwrite main file if this stage has audio data (first or complete)
if ($callbackType === 'first' || $callbackType === 'complete') {
    file_put_contents($logFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Debug log
file_put_contents($logDir . '/last_callback.json', json_encode([
    'received_at' => date('Y-m-d H:i:s'),
    'taskId' => $taskId,
    'callbackType' => $callbackType,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['status' => 'ok']);
