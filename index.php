<?php
/**
 * AiFace BS HTTP/HTTPS Receiver
 *
 * Endpoint tunggal untuk request JSON dari perangkat AiFace.
 * URL contoh: http://192.168.18.17:8000/index.php
 *
 * Jalankan lokal:
 *   php -S 0.0.0.0:8000
 *
 * Semua request (GET/POST), termasuk body mentah, dicatat ke logs/incoming.jsonl.
 * GET digunakan untuk health check. POST digunakan untuk protokol AiFace.
 * Keputusan akses sendlog default DENY (access=0).
 */

date_default_timezone_set('Asia/Jakarta');

$logDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'logs';

if (!is_dir($logDirectory)) {
    if (!@mkdir($logDirectory, 0775, true) && !is_dir($logDirectory)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'log_directory_unavailable',
            'message' => 'Folder logs tidak dapat dibuat.'
        ]);
        exit;
    }
}

function writeLog($file, array $data)
{
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $file;
    $line = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($line === false) {
        return false;
    }

    return file_put_contents($path, $line . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
}

function respond(array $payload, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

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

$receivedAt = date('Y-m-d H:i:s');

$baseLog = [
    'received_at' => $receivedAt,
    'method' => $method,
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
    'request_uri' => $_SERVER['REQUEST_URI'] ?? '/',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
    'content_length' => $_SERVER['CONTENT_LENGTH'] ?? null,
    'json_valid' => ($rawBody === '') ? null : ($jsonError === null && is_array($payload)),
    'json_error' => $jsonError,
    'cmd' => is_array($payload) ? ($payload['cmd'] ?? null) : null,
    'sn' => is_array($payload) ? ($payload['sn'] ?? null) : null,
    'raw' => $rawBody,
    'payload' => is_array($payload) ? $payload : null,
];

$logWritten = writeLog('incoming.jsonl', $baseLog);

if (!$logWritten) {
    writeLog('errors.jsonl', [
        'received_at' => $receivedAt,
        'error' => 'Gagal menulis logs/incoming.jsonl'
    ]);
}

/*
 * Health check browser. GET juga sudah masuk ke incoming.jsonl.
 */
if ($method === 'GET') {
    http_response_code(200);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo "AiFace BS endpoint is online. Send device commands using HTTP POST JSON.";
    exit;
}

if ($method !== 'POST') {
    header('Allow: GET, POST');
    respond([
        'result' => false,
        'reason' => 1,
        'msg' => 'Method not allowed. Use POST with JSON body.'
    ], 405);
}

if ($rawBody === '') {
    respond([
        'ret' => 'unknown',
        'result' => false,
        'reason' => 4,
        'msg' => 'Empty request body'
    ], 400);
}

if ($jsonError !== null || !is_array($payload)) {
    writeLog('errors.jsonl', [
        'received_at' => $receivedAt,
        'error' => 'Invalid JSON body',
        'detail' => $jsonError,
        'raw' => $rawBody,
    ]);

    respond([
        'ret' => 'unknown',
        'result' => false,
        'reason' => 4,
        'msg' => 'Invalid JSON format'
    ], 400);
}

$command = strtolower(trim((string) ($payload['cmd'] ?? '')));
$serialNumber = (string) ($payload['sn'] ?? '');

writeLog('commands.jsonl', [
    'received_at' => $receivedAt,
    'cmd' => $command !== '' ? $command : null,
    'sn' => $serialNumber !== '' ? $serialNumber : null,
    'payload' => $payload,
]);

switch ($command) {
    case 'checklive':
        // Tidak ada antrean perintah server untuk saat ini.
        respond([
            'ret' => 'checklive',
            'cloudtime' => date('Y-m-d H:i:s')
        ]);

    case 'reg':
        // Konfirmasi penerimaan registrasi perangkat.
        respond([
            'ret' => 'reg',
            'sn' => $serialNumber,
            'result' => true
        ]);

    case 'sendlog':
        $records = $payload['record'] ?? [];
        if (!is_array($records)) {
            $records = [];
        }

        if (!$logWritten) {
            respond([
                'ret' => 'sendlog',
                'sn' => $serialNumber,
                'result' => false,
                'reason' => 3,
                'msg' => 'Server gagal menyimpan log ke disk.'
            ], 500);
        }

        // DENY secara default. Receiver ini belum memvalidasi anggota
        // maupun menerapkan aturan pemberian akses pintu.
        respond([
            'ret' => 'sendlog',
            'sn' => $serialNumber,
            'result' => true,
            'count' => count($records),
            'logindex' => (int) ($payload['logindex'] ?? 0),
            'mark' => true,
            'access' => 0,
            'message' => 'Log received. Access is not granted by this test receiver.',
            'fontsize' => 24,
            'text_color' => [220, 70, 70],
            'voice' => '',
            'voiceindex' => 0,
            'name' => (string) ($records[0]['name'] ?? ''),
            'questionnaire' => false
        ]);

    default:
        // Command yang belum didokumentasikan tetap tersimpan di incoming.jsonl.
        // Jangan menganggap verify atau command lain berhasil tanpa format resminya.
        writeLog('unknown-commands.jsonl', [
            'received_at' => $receivedAt,
            'cmd' => $command !== '' ? $command : null,
            'sn' => $serialNumber !== '' ? $serialNumber : null,
            'payload' => $payload,
        ]);

        respond([
            'ret' => $command !== '' ? $command : 'unknown',
            'sn' => $serialNumber,
            'result' => false,
            'reason' => 5,
            'msg' => 'Command logged but not implemented yet'
        ]);
}
