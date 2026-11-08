<?php
require_once __DIR__ . '/db.php';

function rate_check(string $identifier, string $action, int $max = 5, int $window_sec = 900): bool {
    $db  = db();
    $now = date('Y-m-d H:i:s');
    $win = date('Y-m-d H:i:s', time() - $window_sec);
    if (rand(1,100)===1) $db->prepare('DELETE FROM eqso_rate_limits WHERE window_start < ?')->execute([$win]);
    $st = $db->prepare('SELECT id,attempts FROM eqso_rate_limits WHERE identifier=? AND action=? AND window_start>=?');
    $st->execute([$identifier,$action,$win]);
    $row = $st->fetch();
    if (!$row) { $db->prepare('INSERT INTO eqso_rate_limits(identifier,action,attempts,window_start)VALUES(?,?,1,?)')->execute([$identifier,$action,$now]); return true; }
    if ($row['attempts'] >= $max) return false;
    $db->prepare('UPDATE eqso_rate_limits SET attempts=attempts+1 WHERE id=?')->execute([$row['id']]);
    return true;
}
function rate_reset(string $identifier, string $action): void {
    db()->prepare('DELETE FROM eqso_rate_limits WHERE identifier=? AND action=?')->execute([$identifier,$action]);
}
function client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k)
        if (!empty($_SERVER[$k])) return trim(explode(',',$_SERVER[$k])[0]);
    return '0.0.0.0';
}
