<?php

date_default_timezone_set('Asia/Jakarta');

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Type: application/json; charset=utf-8');

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Pantau GET /pub/chat di terminal tanpa memasukkannya ke log POST.
if ($method === 'GET' && $requestPath === '/pub/chat') {
    $debug = [
        'time' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '/',
        'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        'headers' => function_exists('getallheaders') ? getallheaders() : [],
    ];

    error_log(
        '[AiFace PUB CHAT] ' .
        json_encode($debug, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

// GET tidak disimpan ke aiface.jsonl.
if ($method === 'GET') {
    echo json_encode([
        'result' => true,
        'msg' => 'Connection successful.'
    ]);
    exit;
}

// Hanya POST yang disimpan.
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'result' => false,
        'msg' => 'Method not allowed.'
    ]);
    exit;
}

// Simpan raw body POST tanpa filter, parsing, atau pemetaan.
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

// Satu POST = satu baris JSONL, body tetap utuh sebagai string.
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
