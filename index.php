<?php
/**
 * AiFace BS HTTP/HTTPS Receiver
 *
 * Endpoint tunggal untuk request JSON dari perangkat AiFace.
 * Jalankan lokal:
 *   php -S 0.0.0.0:8000
 *
 * Catatan:
 * - Semua request POST dicatat ke logs/incoming.jsonl.
 * - GET hanya untuk health check browser.
 * - Keputusan akses sendlog default DENY (access=0).
 */

date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$logDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'logs';

if (!is_dir($logDirectory)) {
    @mkdir($logDirectory, 0775, true);
}

function writeLog(string $file, array $data): void
{
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $file;
    $line = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($line !== false) {
        file_put_contents($path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function respond(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    http_response_code(200);
    header('Content-Type: text/plain; charset=utf-8');
    echo "AiFace BS endpoint is online. Send device commands using HTTP POST JSON.";
    exit;
}

if ($method !== 'POST') {
    respond([
        'result' => false,
        'reason' => 1,
        'msg' => 'Method not allowed. Use POST with JSON body.'
    ], 405);
}

$rawBody = file_get_contents('php://input');
$rawBody = $rawBody === false ? '' : $rawBody;

$payload = json_decode($rawBody, true);
$receivedAt = date('Y-m-d H:i:s');

$baseLog = [
    'received_at' => $receivedAt,
    'method' => $method,
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
    'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
    'content_length' => $_SERVER['CONTENT_LENGTH'] ?? null,
    'raw' => $rawBody,
    'json_valid' => is_array($payload),
    'payload' => is_array($payload) ? $payload : null,
];

writeLog('incoming.jsonl', $baseLog);

if (!is_array($payload)) {
    writeLog('errors.jsonl', [
        'received_at' => $receivedAt,
        'error' => 'Invalid JSON body',
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

        // DENY secara default. Jangan ubah ke 1 sebelum validasi anggota
        // dan aturan akses benar-benar diimplementasikan.
        respond([
            'ret' => 'sendlog',
            'sn' => $serialNumber,
            'result' => true,
            'count' => count($records),
            'logindex' => (int) ($payload['logindex'] ?? 0),
            'mark' => true,
            'access' => 0,
            'message' => 'Log received',
            'fontsize' => 24,
            'text_color' => [220, 70, 70],
            'voice' => '',
            'voiceindex' => 0,
            'name' => (string) ($records[0]['name'] ?? ''),
            'questionnaire' => false
        ]);

    default:
        // Request tetap tersimpan di incoming.jsonl meskipun command
        // belum dikenali. Jangan mengarang format balasan command lain.
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
            'msg' => 'Command not implemented yet'
        ]);
}
