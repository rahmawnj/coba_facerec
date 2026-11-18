<?php
/**
 * AiFace BS - penerima request sederhana.
 *
 * Endpoint untuk perangkat:
 * http://192.168.18.17:8000/index.php
 *
 * Semua request disimpan ke SATU file:
 * logs/aiface.jsonl
 *
 * Jalankan server lokal:
 * php -S 0.0.0.0:8000
 */

date_default_timezone_set('Asia/Jakarta');

header('Cache-Control: no-store, no-cache, must-revalidate');

$logDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
$logFile = $logDirectory . DIRECTORY_SEPARATOR . 'aiface.jsonl';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$rawBody = file_get_contents('php://input');
$rawBody = ($rawBody === false) ? '' : $rawBody;

$payload = null;
$jsonError = null;

if ($rawBody !== '') {
    $payload = json_decode($rawBody, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $jsonError = json_last_error_msg();
    }
}

$command = is_array($payload) ? strtolower(trim((string) ($payload['cmd'] ?? ''))) : '';
$serialNumber = is_array($payload) ? (string) ($payload['sn'] ?? '') : '';

/*
 * Simpan semua request (GET maupun POST) ke satu file JSON Lines.
 * Satu request = satu baris JSON.
 */
$logEntry = [
    'received_at' => date('Y-m-d H:i:s'),
    'method' => $method,
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    'request_uri' => $_SERVER['REQUEST_URI'] ?? '/',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
    'cmd' => $command !== '' ? $command : null,
    'sn' => $serialNumber !== '' ? $serialNumber : null,
    'json_valid' => $rawBody === '' ? null : ($jsonError === null && is_array($payload)),
    'json_error' => $jsonError,
    'raw' => $rawBody,
    'payload' => is_array($payload) ? $payload : null,
];

if (!is_dir($logDirectory)) {
    @mkdir($logDirectory, 0775, true);
}

$logSaved = is_dir($logDirectory) && file_put_contents(
    $logFile,
    json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) . PHP_EOL,
    FILE_APPEND | LOCK_EX
) !== false;

/*
 * GET hanya untuk mengecek apakah endpoint hidup.
 * Request GET juga ikut tersimpan ke logs/aiface.jsonl.
 */
if ($method === 'GET') {
    header('Content-Type: text/plain; charset=utf-8');
    echo $logSaved
        ? 'AiFace endpoint is online. Request saved.'
        : 'AiFace endpoint is online, but failed to save request.';
    exit;
}

header('Content-Type: application/json; charset=utf-8');

function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($method !== 'POST') {
    header('Allow: GET, POST');
    respond([
        'result' => false,
        'reason' => 1,
        'msg' => 'Method not allowed. Use POST.'
    ], 405);
}

if (!$logSaved) {
    respond([
        'ret' => $command !== '' ? $command : 'unknown',
        'sn' => $serialNumber,
        'result' => false,
        'reason' => 3,
        'msg' => 'Request received but could not be saved.'
    ], 500);
}

if ($rawBody === '' || $jsonError !== null || !is_array($payload)) {
    respond([
        'ret' => $command !== '' ? $command : 'unknown',
        'sn' => $serialNumber,
        'result' => false,
        'reason' => 4,
        'msg' => $rawBody === '' ? 'Empty request body' : 'Invalid JSON format'
    ], 400);
}

/*
 * Balasan minimum untuk command yang sudah dikenali dari dokumentasi.
 * Data request tetap hanya disimpan ke satu file log.
 */
switch ($command) {
    case 'checklive':
        respond([
            'ret' => 'checklive',
            'cloudtime' => date('Y-m-d H:i:s')
        ]);

    case 'reg':
        respond([
            'ret' => 'reg',
            'sn' => $serialNumber,
            'result' => true
        ]);

    case 'sendlog':
        $records = $payload['record'] ?? [];
        respond([
            'ret' => 'sendlog',
            'sn' => $serialNumber,
            'result' => true,
            'count' => is_array($records) ? count($records) : 0,
            'logindex' => (int) ($payload['logindex'] ?? 0),
            'mark' => true,
            'access' => 0,
            'message' => 'Log received. Access is not granted by this receiver.'
        ]);

    default:
        respond([
            'ret' => $command !== '' ? $command : 'unknown',
            'sn' => $serialNumber,
            'result' => false,
            'reason' => 5,
            'msg' => 'Request saved. This command has not been implemented.'
        ]);
}
