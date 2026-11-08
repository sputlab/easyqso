<?php
// ============================================================
//  EasyQSO — Account management (tutti gli utenti)
// ============================================================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// ── Modifica nominativo ──────────────────────────────────────
function account_change_callsign(int $uid, string $callsign): array {
    $call = strtoupper(trim($callsign));
    if (!preg_match('/^[A-Z0-9\/]{2,12}$/', $call))
        return ['ok'=>false,'error'=>'Formato nominativo non valido (2-12 caratteri alfanumerici).'];
    db()->prepare('UPDATE eqso_users SET callsign=? WHERE id=?')->execute([$call,$uid]);
    return ['ok'=>true,'callsign'=>$call];
}

// ── Cambio password ──────────────────────────────────────────
function account_change_password(int $uid, string $old, string $new, bool $logout_others = false): array {
    if (strlen($new) < 8) return ['ok'=>false,'error'=>'Password minimo 8 caratteri.'];
    if (!preg_match('/[A-Z]/',$new)) return ['ok'=>false,'error'=>'La password deve contenere almeno una lettera maiuscola.'];
    if (!preg_match('/[0-9]/',$new)) return ['ok'=>false,'error'=>'La password deve contenere almeno un numero.'];
    $db = db();
    $st = $db->prepare('SELECT password_hash,is_active FROM eqso_users WHERE id=?');
    $st->execute([$uid]); $u=$st->fetch();
    if (!$u||!$u['is_active']) return ['ok'=>false,'error'=>'Utente non trovato.'];
    if (!password_verify($old,$u['password_hash'])) return ['ok'=>false,'error'=>'Password attuale errata.'];
    $hash = password_hash($new,PASSWORD_BCRYPT,['cost'=>12]);
    $db->prepare('UPDATE eqso_users SET password_hash=?,force_pw_change=0 WHERE id=?')->execute([$hash,$uid]);
    if ($logout_others) {
        // recupera token corrente dal cookie/header per NON invalidarlo
        $current = get_bearer_token();
        $st2 = $db->prepare('DELETE FROM eqso_sessions WHERE user_id=? AND token!=?');
        $st2->execute([$uid, $current ?? '']);
    }
    return ['ok'=>true];
}

// ── Richiesta eliminazione account ──────────────────────────
function account_request_delete(int $uid, string $qso_action): array {
    if (!in_array($qso_action,['delete','anonymize']))
        return ['ok'=>false,'error'=>'Azione QSO non valida.'];
    $db    = db();
    $token = generate_token(32);
    $exp   = date('Y-m-d H:i:s', time()+86400); // 24h
    // Invalida token precedenti dello stesso tipo
    $db->prepare('DELETE FROM eqso_account_tokens WHERE user_id=? AND action=?')->execute([$uid,'delete_account']);
    $db->prepare('INSERT INTO eqso_account_tokens(user_id,token,action,payload,expires_at)VALUES(?,?,?,?,?)')
       ->execute([$uid,$token,'delete_account',json_encode(['qso_action'=>$qso_action]),$exp]);
    // Recupera email
    $st = $db->prepare('SELECT email,callsign FROM eqso_users WHERE id=?');
    $st->execute([$uid]); $u=$st->fetch();
    if (!$u) return ['ok'=>false,'error'=>'Utente non trovato.'];
    send_delete_confirmation_email($u['email'],$u['callsign'],$token,$qso_action);
    return ['ok'=>true];
}

// ── Conferma eliminazione via token email ────────────────────
function account_confirm_delete(string $token): array {
    $db = db();
    $st = $db->prepare('SELECT * FROM eqso_account_tokens WHERE token=? AND action=? AND expires_at>NOW() AND used_at IS NULL');
    $st->execute([$token,'delete_account']);
    $row = $st->fetch();
    if (!$row) return ['ok'=>false,'error'=>'Link non valido o scaduto.'];
    $payload = json_decode($row['payload']??'{}',true);
    $uid     = (int)$row['user_id'];
    $qso_act = $payload['qso_action'] ?? 'anonymize';
    // Marca token come usato
    $db->prepare('UPDATE eqso_account_tokens SET used_at=NOW() WHERE id=?')->execute([$row['id']]);
    // Gestisci QSO
    if ($qso_act === 'delete') {
        $db->prepare('DELETE FROM eqso_qso_log WHERE user_id=?')->execute([$uid]);
    } else {
        $db->prepare("UPDATE eqso_qso_log SET callsign='DELETED', dxcc=NULL, note=NULL WHERE user_id=?")->execute([$uid]);
    }
    // Invalida sessioni
    $db->prepare('DELETE FROM eqso_sessions WHERE user_id=?')->execute([$uid]);
    // Elimina utente
    $db->prepare('DELETE FROM eqso_users WHERE id=?')->execute([$uid]);
    return ['ok'=>true];
}

// ── Lista sessioni attive ────────────────────────────────────
function account_get_sessions(int $uid): array {
    $current = get_bearer_token();
    $st = db()->prepare('SELECT id,token,created_at,expires_at FROM eqso_sessions WHERE user_id=? AND expires_at>NOW() ORDER BY created_at DESC');
    $st->execute([$uid]);
    return array_map(fn($s)=>[
        'id'         => $s['id'],
        'created_at' => $s['created_at'],
        'expires_at' => $s['expires_at'],
        'current'    => ($s['token']===$current),
    ], $st->fetchAll());
}

// ── Revoca sessione ──────────────────────────────────────────
function account_revoke_session(int $uid, int $sid): bool {
    $st = db()->prepare('DELETE FROM eqso_sessions WHERE id=? AND user_id=?');
    $st->execute([$sid,$uid]);
    return $st->rowCount()>0;
}

// ── Email: conferma eliminazione ─────────────────────────────
function send_delete_confirmation_email(string $to, string $call, string $token, string $qso_action): void {
    $url   = BASE_URL.'/confirm-delete.php?token='.$token;
    $label = $qso_action==='delete' ? 'eliminati' : 'anonimizzati';
    $subj  = 'Conferma eliminazione account — EasyQSO';
    $body  = "Ciao $call,\n\nHai richiesto l'eliminazione del tuo account EasyQSO.\n"
           . "I tuoi QSO verranno $label.\n\n"
           . "Clicca il link per confermare (valido 24 ore):\n$url\n\n"
           . "Se non hai richiesto nulla, ignora questa email.\n\nEasyQSO Team";
    $hdr = "From: ".MAIL_FROM_NAME." <".MAIL_FROM.">\r\nContent-Type: text/plain; charset=UTF-8";
    mail($to,$subj,$body,$hdr);
}
