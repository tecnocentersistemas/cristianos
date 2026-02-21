<?php
// GitHub Webhook - Auto deploy on push
$secret = trim(@file_get_contents(__DIR__.'/../.webhook-secret') ?: '');
$sig = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$body = file_get_contents('php://input');
if ($secret && $sig) {
    $expected = 'sha256=' . hash_hmac('sha256', $body, $secret);
    if (!hash_equals($expected, $sig)) { http_response_code(403); exit('Invalid signature'); }
}
$payload = json_decode($body, true);
if (($payload['ref'] ?? '') === 'refs/heads/main') {
    $output = shell_exec('git config --global --add safe.directory /var/www/cristianos 2>/dev/null; cd /var/www/cristianos && git pull origin main 2>&1');
    echo "Deployed:\n" . $output;
} else {
    echo 'Not main branch, skipping.';
}
