<?php
// Suno API Callback - receives generated song results
header('Content-Type: application/json');

$body = file_get_contents('php://input');
$data = json_decode($body, true);

// Log the callback for debugging
$logDir = __DIR__ . '/../data/suno-cache';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

// Save the full callback payload
$taskId = $data['data']['taskId'] ?? $data['taskId'] ?? 'unknown_' . time();
$logFile = $logDir . '/' . $taskId . '.json';
file_put_contents($logFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Also log to a general debug file
file_put_contents($logDir . '/last_callback.json', json_encode([
    'received_at' => date('Y-m-d H:i:s'),
    'taskId' => $taskId,
    'payload' => $data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['status' => 'ok']);
