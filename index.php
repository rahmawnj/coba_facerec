<?php
/**
 * AiFace BS - penerima request tanpa API key.
 *
 * Semua request yang masuk akan langsung disimpan ke:
 * logs/aiface.jsonl
 *
 * Endpoint perangkat:
 * http://192.168.18.17:8000/index.php
 *
 * Jalankan:
 * php -S 0.0.0.0:8000
 */

date_default_timezone_set('Asia/Jakarta');

header('Cache-Control: no-store, no-cache, must-revalidate');

$logDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
$logFile = $logDirectory . DIRECTORY_SEPARATOR . 'aiface.jsonl';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN');
$rawBody = file_get_contents('php://input');
$rawBody = ($rawBody === false) ? '' : $rawBody;

// Coba baca JSON, tetapi JSON tidak valid pun tetap disimpan.
$payload = null;
if ($rawBody !== '') {
    $decoded = json_decode($rawBody, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $payload = $decoded;
    }
}

$command = is_array($payload) ? (string) ($payload['cmd'] ?? '') : '';
$serialNumber = is_array($payload) ? (string) ($payload['sn'] ?? '') : '';

$logEntry = [
    'received_at' => date('Y-m-d H:i:s'),
    'method' => $method,
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    'request_uri' => $_SERVER['REQUEST_URI'] ?? '/',
    'query_string' => $_SERVER['QUERY_STRING'] ?? '',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
    'headers' => function_exists('getallheaders') ? getallheaders() : [],
    'cmd' => $command !== '' ? $command : null,
    'sn' => $serialNumber !== '' ? $serialNumber : null,
    'raw' => $rawBody,
    'payload' => $payload,
];

if (!is_dir($logDirectory)) {
    @mkdir($logDirectory, 0775, true);
}

$encodedLog = json_encode(
    $logEntry,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
);

$logSaved = $encodedLog !== false
    && file_put_contents($logFile, $encodedLog . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;

header('Content-Type: application/json; charset=utf-8');

function reply(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

// Tidak ada API key, token, autentikasi, atau validasi format request.
// Request apa pun yang masuk dicatat terlebih dahulu ke file log.
if (!$logSaved) {
    reply([
        'result' => false,
        'msg' => 'Request diterima tetapi gagal disimpan ke file log.'
    ], 500);
}

// Pertahankan respons dasar protokol untuk command AiFace yang sudah diketahui.
switch (strtolower(trim($command))) {
    case 'checklive':
        reply([
            'ret' => 'checklive',
            'cloudtime' => date('Y-m-d H:i:s')
        ]);

    case 'reg':
        reply([
            'ret' => 'reg',
            'sn' => $serialNumber,
            'result' => true
        ]);

    case 'sendlog':
        $records = is_array($payload['record'] ?? null) ? $payload['record'] : [];
        reply([
            'ret' => 'sendlog',
            'sn' => $serialNumber,
            'result' => true,
            'count' => count($records),
            'logindex' => (int) ($payload['logindex'] ?? 0),
            'mark' => true,
            'access' => 0,
            'message' => 'Log received and saved. Access not granted.'
        ]);

    default:
        // Command tidak dikenal atau request bukan JSON tetap tersimpan.
        reply([
            'ret' => $command !== '' ? $command : 'received',
            'sn' => $serialNumber !== '' ? $serialNumber : null,
            'result' => true,
            'msg' => 'Request received and saved.'
        ]);
}
