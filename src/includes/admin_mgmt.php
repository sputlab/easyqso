<?php
// ============================================================
//  EasyQSO — Admin: gestione utenti + audit
// ============================================================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// ── Audit log ────────────────────────────────────────────────
function audit(int $admin_id, string $admin_call, string $action, ?int $target_id=null, array $detail=[]): void {
    db()->prepare('INSERT INTO eqso_audit_log(admin_id,admin_callsign,target_user_id,action,detail,ip)VALUES(?,?,?,?,?,?)')
       ->execute([$admin_id,$admin_call,$target_id,$action,json_encode($detail),client_ip()]);
}

// ── Lista utenti con ricerca/filtro/paginazione ──────────────
function admin_list_users(string $q='', string $filter='all', int $page=1, int $per=20): array {
    $db     = db();
    $offset = ($page-1)*$per;
    $where  = ['1=1']; $p=[];
    if ($q) { $where[]='(u.callsign LIKE ? OR u.email LIKE ?)'; $p[]="%$q%"; $p[]="%$q%"; }
    match($filter) {
        'active'     => ($where[]='u.is_active=1 AND u.is_verified=1'),
        'suspended'  => ($where[]='u.is_active=0'),
        'unverified' => ($where[]='u.is_verified=0'),
        default      => null,
    };
    $w = implode(' AND ',$where);
    $cnt = $db->prepare("SELECT COUNT(*) FROM eqso_users u WHERE $w");
    $cnt->execute($p); $total=(int)$cnt->fetchColumn();
    $st = $db->prepare("SELECT u.id,u.callsign,u.email,u.is_verified,u.is_admin,u.is_active,
        u.suspended_reason,u.created_at,u.last_login,
        (SELECT COUNT(*) FROM eqso_qso_log WHERE user_id=u.id) AS qso_count
        FROM eqso_users u WHERE $w ORDER BY u.created_at DESC LIMIT $per OFFSET $offset");
    $st->execute($p);
    return ['users'=>$st->fetchAll(),'total'=>$total,'page'=>$page,'per'=>$per,'pages'=>(int)ceil($total/$per)];
}

// ── Dettaglio utente ─────────────────────────────────────────
function admin_get_user_detail(int $id): ?array {
    $st = db()->prepare('SELECT id,callsign,email,is_verified,is_admin,is_active,suspended_reason,
        force_pw_change,theme,created_at,last_login FROM eqso_users WHERE id=?');
    $st->execute([$id]); return $st->fetch()||null;
}

// ── Aggiorna ruolo/stato ─────────────────────────────────────
function admin_update_user(int $admin_id, string $admin_call, int $uid, array $b): array {
    $db = db();
    // Impedisci auto-modifica ruolo critico
    if ($uid===$admin_id && isset($b['is_admin']) && !(bool)$b['is_admin'])
        return ['ok'=>false,'error'=>'Non puoi rimuovere i tuoi privilegi admin.'];
    // Impedisci disattivare l'ultimo admin
    if (isset($b['is_admin']) && !(bool)$b['is_admin']) {
        $cnt=(int)$db->prepare('SELECT COUNT(*) FROM eqso_users WHERE is_admin=1 AND id!=?')->execute([$uid])??0;
        $c=$db->prepare('SELECT COUNT(*) FROM eqso_users WHERE is_admin=1 AND id!=?');$c->execute([$uid]);
        if ((int)$c->fetchColumn()===0) return ['ok'=>false,'error'=>'Deve esistere almeno un admin.'];
    }
    $fields=[]; $vals=[];
    if (isset($b['is_admin']))     { $fields[]='is_admin=?';     $vals[]=(int)(bool)$b['is_admin']; }
    if (isset($b['is_verified']))  { $fields[]='is_verified=?';  $vals[]=(int)(bool)$b['is_verified']; }
    if (isset($b['is_active']))    { $fields[]='is_active=?';    $vals[]=(int)(bool)$b['is_active']; }
    if (isset($b['suspended_reason'])) { $fields[]='suspended_reason=?'; $vals[]=$b['suspended_reason']?:null; }
    if (isset($b['force_pw_change']))  { $fields[]='force_pw_change=?';  $vals[]=(int)(bool)$b['force_pw_change']; }
    if (!$fields) return ['ok'=>false,'error'=>'Nessun campo da aggiornare.'];
    $vals[]=$uid;
    $db->prepare('UPDATE eqso_users SET '.implode(',',$fields).' WHERE id=?')->execute($vals);
    // Se disattivato, invalida sessioni
    if (isset($b['is_active']) && !(bool)$b['is_active'])
        $db->prepare('DELETE FROM eqso_sessions WHERE user_id=?')->execute([$uid]);
    audit($admin_id,$admin_call,'update_user',$uid,$b);
    return ['ok'=>true];
}

// ── Invia link reset password ────────────────────────────────
function admin_send_reset(int $admin_id, string $admin_call, int $uid): array {
    $db = db();
    $st = $db->prepare('SELECT email,callsign FROM eqso_users WHERE id=? AND is_active=1');
    $st->execute([$uid]); $u=$st->fetch();
    if (!$u) return ['ok'=>false,'error'=>'Utente non trovato o inattivo.'];
    $token   = generate_token(32);
    $expires = date('Y-m-d H:i:s',time()+3600);
    $db->prepare('UPDATE eqso_users SET reset_token=?,reset_expires=? WHERE id=?')->execute([$token,$expires,$uid]);
    send_reset_email($u['email'],$u['callsign'],$token);
    audit($admin_id,$admin_call,'admin_reset_password',$uid);
    return ['ok'=>true];
}

// ── Elimina utente ───────────────────────────────────────────
function admin_delete_user(int $admin_id, string $admin_call, int $uid, string $qso_action='anonymize'): array {
    if ($uid===$admin_id) return ['ok'=>false,'error'=>'Non puoi eliminare il tuo account da qui.'];
    $db = db();
    $st = $db->prepare('SELECT callsign,email,is_admin FROM eqso_users WHERE id=?');
    $st->execute([$uid]); $u=$st->fetch();
    if (!$u) return ['ok'=>false,'error'=>'Utente non trovato.'];
    if ($u['is_admin']) {
        $c=$db->prepare('SELECT COUNT(*) FROM eqso_users WHERE is_admin=1 AND id!=?');$c->execute([$uid]);
        if ((int)$c->fetchColumn()===0) return ['ok'=>false,'error'=>'Deve esistere almeno un admin.'];
    }
    if ($qso_action==='delete') $db->prepare('DELETE FROM eqso_qso_log WHERE user_id=?')->execute([$uid]);
    else $db->prepare("UPDATE eqso_qso_log SET callsign='DELETED',dxcc=NULL,note=NULL WHERE user_id=?")->execute([$uid]);
    $db->prepare('DELETE FROM eqso_sessions WHERE user_id=?')->execute([$uid]);
    $db->prepare('DELETE FROM eqso_users WHERE id=?')->execute([$uid]);
    audit($admin_id,$admin_call,'delete_user',$uid,['callsign'=>$u['callsign'],'email'=>$u['email'],'qso_action'=>$qso_action]);
    return ['ok'=>true];
}

// ── Crea nuovo utente ────────────────────────────────────────
function admin_create_user(int $admin_id, string $admin_call, array $b): array {
    $call  = strtoupper(trim($b['callsign']??''));
    $email = strtolower(trim($b['email']??''));
    $pass  = $b['password']??'';
    $is_admin = (bool)($b['is_admin']??false);
    if (!$call||!$email||!$pass) return ['ok'=>false,'error'=>'Compila tutti i campi.'];
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) return ['ok'=>false,'error'=>'Email non valida.'];
    if (!preg_match('/^[A-Z0-9\/]{2,12}$/',$call)) return ['ok'=>false,'error'=>'Nominativo non valido.'];
    if (strlen($pass)<8) return ['ok'=>false,'error'=>'Password minimo 8 caratteri.'];
    $db=$db=db();
    $st=$db->prepare('SELECT id FROM eqso_users WHERE email=?');$st->execute([$email]);
    if ($st->fetch()) return ['ok'=>false,'error'=>'Email già registrata.'];
    $hash=password_hash($pass,PASSWORD_BCRYPT,['cost'=>12]);
    $db->prepare('INSERT INTO eqso_users(callsign,email,password_hash,is_verified,is_admin)VALUES(?,?,?,1,?)')
       ->execute([$call,$email,$hash,(int)$is_admin]);
    $new_id=(int)$db->lastInsertId();
    audit($admin_id,$admin_call,'create_user',$new_id,['callsign'=>$call,'email'=>$email]);
    return ['ok'=>true,'id'=>$new_id];
}

// ── Audit log paginato ───────────────────────────────────────
function admin_get_audit(int $page=1, int $per=50): array {
    $offset=($page-1)*$per;
    $db=db();
    $total=(int)$db->query('SELECT COUNT(*) FROM eqso_audit_log')->fetchColumn();
    $st=$db->prepare('SELECT * FROM eqso_audit_log ORDER BY created_at DESC LIMIT '.$per.' OFFSET '.$offset);
    $st->execute();
    return ['logs'=>$st->fetchAll(),'total'=>$total,'page'=>$page,'pages'=>(int)ceil($total/$per)];
}
