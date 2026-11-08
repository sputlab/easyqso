<?php
// ============================================================
//  EasyQSO — DX Cluster Proxy  (api/cluster.php)
//  Proxia le spot di DXheat.com evitando CORS dal browser
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

$band   = preg_replace('/[^0-9a-z]/i','', $_GET['band']??'');
$mode   = preg_replace('/[^A-Z]/','',   strtoupper($_GET['mode']??''));
$limit  = min(30, max(5, (int)($_GET['limit']??20)));

$url = "https://dxheat.com/dxc/?source=web&limit={$limit}&de=EU";
if ($band)  $url .= "&band={$band}";
if ($mode)  $url .= "&mode={$mode}";

$ctx = stream_context_create(['http'=>[
    'timeout'     => 5,
    'user_agent'  => 'EasyQSO/2.0 (+https://sputlab.com)',
    'ignore_errors' => true,
]]);

$raw = @file_get_contents($url, false, $ctx);
if ($raw === false) {
    http_response_code(503);
    echo json_encode(['ok'=>false,'error'=>'DX Cluster non raggiungibile.']);
    exit;
}

$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'Risposta non valida dal cluster.']);
    exit;
}

// Normalizza i campi di DXheat
$spots = array_map(fn($s) => [
    'dx'      => strtoupper($s['dx']??''),
    'de'      => strtoupper($s['de']??''),
    'freq'    => round((float)($s['f']??0), 1),
    'band'    => $s['band'] ?? '',
    'mode'    => strtoupper($s['mode']??''),
    'comment' => mb_substr($s['comment']??'', 0, 60),
    'time'    => $s['time']??'',
], $data['spots']??$data??[]);

echo json_encode(['ok'=>true,'spots'=>$spots,'ts'=>time()]);
