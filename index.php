<?php

date_default_timezone_set('Asia/Jakarta');

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Type: application/json; charset=utf-8');

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');

// GET hanya untuk mengecek koneksi, tidak disimpan.
if ($method === 'GET') {
    echo json_encode([
        'result' => true,
        'msg' => 'Connection successful.'
    ]);
    exit;
}

// Hanya request POST yang disimpan.
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'result' => false,
        'msg' => 'Method not allowed.'
    ]);
    exit;
}

// Ambil seluruh body POST tanpa memfilter atau mengubah isinya.
$rawBody = file_get_contents('php://input');
$rawBody = ($rawBody === false) ? '' : $rawBody;

$logDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
$logFile = $logDirectory . DIRECTORY_SEPARATOR . 'aiface.jsonl';

if (!is_dir($logDirectory) && !mkdir($logDirectory, 0775, true) && !is_dir($logDirectory)) {
    http_response_code(500);
    echo json_encode([
        'result' => false,
        'msg' => 'Failed to create log directory.'
    ]);
    exit;
}

// Satu request POST = satu baris baru di aiface.jsonl.
$logEntry = [
    'received_at' => date('Y-m-d H:i:s'),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    'request_uri' => $_SERVER['REQUEST_URI'] ?? '/',
    'headers' => function_exists('getallheaders') ? getallheaders() : [],
    'body' => $rawBody
];

$encoded = json_encode(
    $logEntry,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
);

$saved = $encoded !== false
    && file_put_contents($logFile, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;

if (!$saved) {
    http_response_code(500);
    echo json_encode([
        'result' => false,
        'msg' => 'Failed to save request.'
    ]);
    exit;
}

echo json_encode([
    'result' => true,
    'msg' => 'Request received and saved.'
]);
