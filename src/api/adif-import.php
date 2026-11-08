<?php
// ============================================================
//  EasyQSO — ADIF Import  (api/adif-import.php)
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){http_response_code(204);exit;}

require_once __DIR__.'/../includes/auth.php';
$user = require_auth();

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?? [];
$adif = $body['adif'] ?? '';
if (!$adif) json_error('Nessun contenuto ADIF ricevuto.');

// ── Parser ADIF ──────────────────────────────────────────────
function parse_adif(string $adif): array {
    // Rimuove tutto prima di <EOH> se presente
    $adif = preg_replace('/^.*<EOH>/si', '', $adif);
    $records = []; $current = [];
    // Estrae tutti i tag <FIELD:len>value o <FIELD:len:type>value
    preg_match_all('/<([A-Z_0-9]+)(?::[0-9]+)(?::[A-Z])?:([0-9]+)>([^<]*)/i', $adif, $m, PREG_SET_ORDER);
    // Alternativa più robusta: split per <EOR>
    $qsos = preg_split('/<EOR>/i', $adif);
    foreach ($qsos as $qso) {
        $qso = trim($qso);
        if (!$qso) continue;
        $rec = [];
        preg_match_all('/<([A-Z_0-9]+)(?::[^>]*)?>([^<]*)/i', $qso, $fields, PREG_SET_ORDER);
        foreach ($fields as $f) {
            $rec[strtoupper($f[1])] = trim($f[2]);
        }
        if (!empty($rec['CALL'])) $records[] = $rec;
    }
    return $records;
}

$records = parse_adif($adif);
if (!$records) json_error('Nessun QSO trovato nel file ADIF.');

// Bande valide
$valid_bands = ['160m','80m','40m','30m','20m','17m','15m','12m','10m','6m','2m','70cm'];
$valid_modes = ['SSB','CW','FT8','FT4','AM','FM','DIGI','RTTY','PSK31','JT65','JS8'];

$db = db();
$inserted = 0; $skipped = 0;
$stmt = $db->prepare('INSERT INTO eqso_qso_log
    (user_id,qso_date,qso_time,callsign,freq,band,mode,rst_sent,rst_rcvd,serial_sent,serial_rcvd,dxcc,note)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');

foreach ($records as $r) {
    // Data: ADIF usa YYYYMMDD
    $raw_date = $r['QSO_DATE'] ?? '';
    $date = '';
    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $raw_date, $dm)) {
        $date = "{$dm[1]}-{$dm[2]}-{$dm[3]}";
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw_date)) {
        $date = $raw_date;
    }
    if (!$date) { $skipped++; continue; }

    // Ora: HHMM o HHMMSS
    $raw_time = $r['TIME_ON'] ?? $r['TIME_OFF'] ?? '0000';
    $time = '00:00:00';
    if (preg_match('/^(\d{2})(\d{2})(\d{2})?$/', $raw_time, $tm)) {
        $time = $tm[1].':'.$tm[2].':'.($tm[3]??'00');
    }

    $callsign = strtoupper(trim($r['CALL'] ?? ''));
    if (!$callsign) { $skipped++; continue; }

    $band = strtolower($r['BAND'] ?? '');
    if (!in_array($band, $valid_bands)) $band = '20m';

    $mode = strtoupper($r['MODE'] ?? 'SSB');
    if (!in_array($mode, $valid_modes)) $mode = 'SSB';

    $freq = isset($r['FREQ']) ? (float)$r['FREQ'] : null;

    try {
        $stmt->execute([
            $user['id'], $date, $time, $callsign,
            $freq, $band, $mode,
            $r['RST_SENT'] ?? '59',
            $r['RST_RCVD'] ?? '59',
            $r['STX'] ?? $r['STX_STRING'] ?? null,
            $r['SRX'] ?? $r['SRX_STRING'] ?? null,
            strtoupper($r['DXCC'] ?? $r['COUNTRY'] ?? ''),
            mb_substr($r['NOTES'] ?? $r['COMMENT'] ?? '', 0, 255),
        ]);
        $inserted++;
    } catch (Exception $e) {
        $skipped++;
    }
}

json_ok([
    'inserted' => $inserted,
    'skipped'  => $skipped,
    'total'    => count($records),
    'message'  => "Importati $inserted QSO" . ($skipped ? ", $skipped ignorati." : '.'),
]);
