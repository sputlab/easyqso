<?php
// ============================================================
//  EasyQSO v2.1.1 — API REST
// ============================================================
$allowed = ['https://www.sputlab.com','https://sputlab.com'];
$origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
header('Access-Control-Allow-Origin: '.(in_array($origin,$allowed)?$origin:'*'));
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');
header('Vary: Origin');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){http_response_code(204);exit;}
header('Content-Type: application/json; charset=utf-8');

// ── Gestione errori: mai un 500 HTML anonimo, sempre JSON + log ──
function api_fail(string $msg): void {
    if (!headers_sent()) { http_response_code(500); header('Content-Type: application/json; charset=utf-8'); }
    echo json_encode(['ok'=>false,'error'=>$msg]);
}
set_exception_handler(function(Throwable $e){
    error_log('[EasyQSO] '.get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
    // 42S02 = tabella mancante, 42S22 = colonna mancante → DB non migrato
    $schema = $e instanceof PDOException && in_array((string)$e->getCode(),['42S02','42S22'],true);
    api_fail($schema ? 'Database non aggiornato: esegui le migrazioni in db/.' : 'Errore interno del server.');
});
register_shutdown_function(function(){
    $e = error_get_last();
    if ($e && in_array($e['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR],true)) {
        error_log('[EasyQSO] Fatal: '.$e['message'].' @ '.$e['file'].':'.$e['line']);
        api_fail('Errore interno del server.');
    }
});

require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/rate_limit.php';
require_once __DIR__.'/../includes/account.php';
require_once __DIR__.'/../includes/admin_mgmt.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = preg_replace('#^.*/api#','',parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
$parts  = array_values(array_filter(explode('/',$uri)));
$res    = $parts[0]??''; $id=$parts[1]??null; $sub=$parts[2]??null;
$body   = []; $raw=file_get_contents('php://input');
if($raw) $body=json_decode($raw,true)??[];

match($res){
    'auth'  => route_auth($method,$id,$body),
    'qso'   => route_qso($method,$id,$body),
    'user'  => route_user($method,$id,$sub,$body),
    'admin' => route_admin($method,$id,$sub,$body),
    default => json_error('Endpoint non trovato.',404),
};

// ════════════════════════════════════════════════════════════
//  AUTH
// ════════════════════════════════════════════════════════════
function route_auth(string $m,?string $a,array $b):void{
    match($a){
        'register'      => auth_do_register($b),
        'login'         => auth_do_login($b),
        'logout'        => auth_do_logout(),
        'verify'        => auth_do_verify($b),
        'reset-request' => auth_do_reset_request($b),
        'reset-do'      => auth_do_reset($b),
        'me'            => json_ok(['user'=>require_auth()]),
        default         => json_error('Azione non valida.',404),
    };
}
function auth_do_register(array $b):void{
    $ip=client_ip();
    if(!rate_check($ip,'register',3,3600)) json_error('Troppi tentativi. Riprova tra un\'ora.',429);
    $call=trim($b['callsign']??''); $email=trim($b['email']??''); $pass=$b['password']??''; $pass2=$b['password2']??'';
    if(!$call||!$email||!$pass) json_error('Compila tutti i campi.');
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) json_error('Email non valida.');
    if(strlen($pass)<8) json_error('Password minimo 8 caratteri.');
    if($pass!==$pass2) json_error('Le password non coincidono.');
    if(!preg_match('/^[A-Z0-9\/]{2,12}$/i',$call)) json_error('Nominativo non valido.');
    $r=register_user($call,$email,$pass);
    if(!$r['ok']) json_error($r['error']);
    json_ok(['message'=>'Registrazione completata! Controlla la tua email.'],201);
}
function auth_do_login(array $b):void{
    $ip=client_ip(); $email=trim($b['email']??''); $pass=$b['password']??'';
    if(!rate_check("$ip:login",'login',5,900)) json_error('Troppi tentativi. Attendi 15 minuti.',429);
    if(!$email||!$pass) json_error('Compila tutti i campi.');
    $r=login_user($email,$pass);
    if(!$r['ok']){json_error($r['error'],401);}
    rate_reset("$ip:login",'login');
    json_ok(['token'=>$r['token'],'user'=>$r['user']]);
}
function auth_do_logout():void{
    $t=get_bearer_token(); if($t)logout_user($t);
    json_ok(['message'=>'Logout effettuato.']);
}
function auth_do_verify(array $b):void{
    $token=trim($b['token']??$_GET['token']??'');
    if(!$token) json_error('Token mancante.');
    if(verify_email($token)) json_ok(['message'=>'Email verificata!']);
    else json_error('Token non valido o già usato.',400);
}
function auth_do_reset_request(array $b):void{
    $ip=client_ip();
    if(!rate_check($ip,'reset',3,3600)) json_error('Troppi tentativi.',429);
    $email=trim($b['email']??''); if(!$email)json_error('Email mancante.');
    request_password_reset($email);
    json_ok(['message'=>'Se l\'email esiste riceverai le istruzioni.']);
}
function auth_do_reset(array $b):void{
    $token=trim($b['token']??''); $pass=$b['password']??''; $pass2=$b['password2']??'';
    if(!$token||!$pass) json_error('Dati mancanti.');
    if(strlen($pass)<8) json_error('Password minimo 8 caratteri.');
    if($pass!==$pass2) json_error('Le password non coincidono.');
    if(do_password_reset($token,$pass)) json_ok(['message'=>'Password aggiornata!']);
    else json_error('Token non valido o scaduto.',400);
}

// ════════════════════════════════════════════════════════════
//  QSO
// ════════════════════════════════════════════════════════════
function route_qso(string $m,?string $id,array $b):void{
    $user=require_auth();
    match($m){
        'GET'    => $id?qso_get_one($user,(int)$id):qso_list($user),
        'POST'   => qso_create($user,$b),
        'PUT'    => $id?qso_update($user,(int)$id,$b):json_error('ID mancante.'),
        'DELETE' => $id?qso_delete($user,(int)$id):json_error('ID mancante.'),
        default  => json_error('Metodo non valido.',405),
    };
}
function qso_list(array $user):void{
    $band=$_GET['band']??'ALL'; $q=trim($_GET['q']??'');
    $sql='SELECT * FROM eqso_qso_log WHERE user_id=?'; $p=[$user['id']];
    if($band&&$band!=='ALL'){$sql.=' AND band=?';$p[]=$band;}
    if($q){$sql.=' AND (callsign LIKE ? OR dxcc LIKE ?)';$p[]="%$q%";$p[]="%$q%";}
    $sql.=' ORDER BY qso_date DESC,qso_time DESC';
    $st=db()->prepare($sql);$st->execute($p);
    json_ok(['qso'=>$st->fetchAll()]);
}
function qso_get_one(array $user,int $id):void{
    $st=db()->prepare('SELECT * FROM eqso_qso_log WHERE id=? AND user_id=?');
    $st->execute([$id,$user['id']]);$q=$st->fetch();
    if(!$q)json_error('QSO non trovato.',404);
    json_ok(['qso'=>$q]);
}
function qso_create(array $user,array $b):void{
    validate_qso($b);$db=db();
    $db->prepare('INSERT INTO eqso_qso_log(user_id,qso_date,qso_time,callsign,freq,band,mode,rst_sent,rst_rcvd,serial_sent,serial_rcvd,dxcc,note)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)')
    ->execute([$user['id'],$b['date'],$b['time'],strtoupper($b['callsign']),
        isset($b['freq'])&&$b['freq']!==''?(float)$b['freq']:null,
        $b['band'],$b['mode'],$b['rst_sent']??'59',$b['rst_rcvd']??'59',
        $b['serial_sent']??null,$b['serial_rcvd']??null,
        strtoupper($b['dxcc']??''),$b['note']??null]);
    $new=db()->prepare('SELECT * FROM eqso_qso_log WHERE id=?');
    $new->execute([$db->lastInsertId()]);
    json_ok(['qso'=>$new->fetch()],201);
}
function qso_update(array $user,int $id,array $b):void{
    $st=db()->prepare('SELECT id FROM eqso_qso_log WHERE id=? AND user_id=?');
    $st->execute([$id,$user['id']]);if(!$st->fetch())json_error('QSO non trovato.',404);
    validate_qso($b);
    db()->prepare('UPDATE eqso_qso_log SET qso_date=?,qso_time=?,callsign=?,freq=?,band=?,mode=?,rst_sent=?,rst_rcvd=?,serial_sent=?,serial_rcvd=?,dxcc=?,note=? WHERE id=? AND user_id=?')
    ->execute([$b['date'],$b['time'],strtoupper($b['callsign']),
        isset($b['freq'])&&$b['freq']!==''?(float)$b['freq']:null,
        $b['band'],$b['mode'],$b['rst_sent']??'59',$b['rst_rcvd']??'59',
        $b['serial_sent']??null,$b['serial_rcvd']??null,
        strtoupper($b['dxcc']??''),$b['note']??null,$id,$user['id']]);
    $st=db()->prepare('SELECT * FROM eqso_qso_log WHERE id=?');$st->execute([$id]);
    json_ok(['qso'=>$st->fetch()]);
}
function qso_delete(array $user,int $id):void{
    $st=db()->prepare('DELETE FROM eqso_qso_log WHERE id=? AND user_id=?');
    $st->execute([$id,$user['id']]);
    if($st->rowCount()===0)json_error('QSO non trovato.',404);
    json_ok(['message'=>'QSO eliminato.']);
}
function validate_qso(array $b):void{
    if(empty($b['callsign']))json_error('Nominativo obbligatorio.');
    if(empty($b['date']))json_error('Data obbligatoria.');
    if(empty($b['time']))json_error('Ora obbligatoria.');
    if(empty($b['band']))json_error('Banda obbligatoria.');
    if(empty($b['mode']))json_error('Modo obbligatorio.');
}

// ════════════════════════════════════════════════════════════
//  USER ACCOUNT
// ════════════════════════════════════════════════════════════
function route_user(string $m,?string $action,?string $sub,array $b):void{
    $user=require_auth();
    match($action){
        'settings'        => $m==='GET'?user_get_settings($user):user_save_settings($user,$b),
        'callsign'        => $m==='PUT'?user_do_change_callsign($user,$b):json_error('Metodo non valido.',405),
        'change-password' => user_do_change_password($user,$b),
        'sessions'        => $m==='GET'?user_do_sessions($user):json_error('Metodo non valido.',405),
        'session'         => $m==='DELETE'&&$sub?user_do_revoke_session($user,(int)$sub):json_error('Errore.',400),
        'request-delete'  => user_do_request_delete($user,$b),
        'confirm-delete'  => user_do_confirm_delete($b),
        'stats'           => user_stats($user),
        'export-adif'     => user_export_adif($user),
        'export-csv'      => user_export_csv($user),
        default           => json_error('Azione non valida.',404),
    };
}
function user_get_settings(array $user):void{
    json_ok(['settings'=>['callsign'=>$user['callsign'],'email'=>$user['email'],
        'theme'=>$user['theme'],'opt_autotime'=>(bool)$user['opt_autotime'],
        'opt_keepfreq'=>(bool)$user['opt_keepfreq'],'is_admin'=>(bool)$user['is_admin']]]);
}
function user_save_settings(array $user,array $b):void{
    $theme=in_array($b['theme']??'',['radio','dark','light'])?$b['theme']:$user['theme'];
    $at=isset($b['opt_autotime'])?(int)(bool)$b['opt_autotime']:(int)$user['opt_autotime'];
    $kf=isset($b['opt_keepfreq'])?(int)(bool)$b['opt_keepfreq']:(int)$user['opt_keepfreq'];
    db()->prepare('UPDATE eqso_users SET theme=?,opt_autotime=?,opt_keepfreq=? WHERE id=?')
        ->execute([$theme,$at,$kf,$user['id']]);
    json_ok(['message'=>'Impostazioni salvate.']);
}
function user_do_change_callsign(array $user,array $b):void{
    $r=account_change_callsign($user['id'],$b['callsign']??'');
    if(!$r['ok'])json_error($r['error']);
    json_ok(['callsign'=>$r['callsign'],'message'=>'Nominativo aggiornato.']);
}
function user_do_change_password(array $user,array $b):void{
    $ip=client_ip();
    if(!rate_check("user:{$user['id']}",'chpwd',5,900))json_error('Troppi tentativi. Attendi 15 minuti.',429);
    $r=account_change_password($user['id'],$b['old_password']??'',$b['new_password']??'',(bool)($b['logout_others']??false));
    if(!$r['ok'])json_error($r['error']);
    json_ok(['message'=>'Password aggiornata.']);
}
function user_do_sessions(array $user):void{
    json_ok(['sessions'=>account_get_sessions($user['id'])]);
}
function user_do_revoke_session(array $user,int $sid):void{
    if(account_revoke_session($user['id'],$sid)) json_ok(['message'=>'Sessione revocata.']);
    else json_error('Sessione non trovata.',404);
}
function user_do_request_delete(array $user,array $b):void{
    $r=account_request_delete($user['id'],$b['qso_action']??'anonymize');
    if(!$r['ok'])json_error($r['error']);
    json_ok(['message'=>'Email di conferma inviata. Controlla la tua casella.']);
}
function user_do_confirm_delete(array $b):void{
    $token=trim($b['token']??$_GET['token']??'');
    if(!$token)json_error('Token mancante.');
    $r=account_confirm_delete($token);
    if(!$r['ok'])json_error($r['error'],400);
    json_ok(['message'=>'Account eliminato.']);
}
function user_stats(array $user):void{
    $db=db();
    $st=$db->prepare('SELECT COUNT(*) AS total,COUNT(DISTINCT callsign) AS unique_calls,COUNT(DISTINCT dxcc) AS dxcc,COUNT(DISTINCT band) AS bands FROM eqso_qso_log WHERE user_id=?');
    $st->execute([$user['id']]);$stats=$st->fetch();
    $st=$db->prepare('SELECT band,COUNT(*) AS cnt FROM eqso_qso_log WHERE user_id=? GROUP BY band ORDER BY cnt DESC');
    $st->execute([$user['id']]);$by_band=$st->fetchAll();
    $st=$db->prepare('SELECT mode,COUNT(*) AS cnt FROM eqso_qso_log WHERE user_id=? GROUP BY mode ORDER BY cnt DESC');
    $st->execute([$user['id']]);$by_mode=$st->fetchAll();
    json_ok(['stats'=>$stats,'by_band'=>$by_band,'by_mode'=>$by_mode]);
}
function user_export_adif(array $user):void{
    $st=db()->prepare('SELECT * FROM eqso_qso_log WHERE user_id=? ORDER BY qso_date DESC,qso_time DESC');
    $st->execute([$user['id']]);
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="easyqso_export.adi"');
    echo "ADIF Export from EasyQSO v2.1.1\n<EOH>\n\n";
    foreach($st->fetchAll() as $q){
        $d=str_replace('-','',$q['qso_date']);$t=str_replace(':','',substr($q['qso_time'],0,5));
        $f=$q['freq']?number_format((float)$q['freq'],3,'.',''): '';
        foreach([['CALL',$q['callsign']],['BAND',$q['band']],['MODE',$q['mode']],['QSO_DATE',$d],['TIME_ON',$t],
            $f?['FREQ',$f]:null,$q['rst_sent']?['RST_SENT',$q['rst_sent']]:null,
            $q['rst_rcvd']?['RST_RCVD',$q['rst_rcvd']]:null,$q['dxcc']?['DXCC',$q['dxcc']]:null,
            $q['note']?['NOTES',$q['note']]:null] as $fld)
            if($fld&&$fld[1]!=='')echo "<{$fld[0]}:".strlen($fld[1]).">{$fld[1]} ";
        echo "<EOR>\n";
    }exit;
}
function user_export_csv(array $user):void{
    $st=db()->prepare('SELECT * FROM eqso_qso_log WHERE user_id=? ORDER BY qso_date DESC,qso_time DESC');
    $st->execute([$user['id']]);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="easyqso_export.csv"');
    $out=fopen('php://output','w');
    fputcsv($out,['ID','Nominativo','Data','Ora UTC','Freq MHz','Banda','Modo','RST TX','RST RX','Nr TX','Nr RX','DXCC','Note']);
    foreach($st->fetchAll() as $q)
        fputcsv($out,[$q['id'],$q['callsign'],$q['qso_date'],$q['qso_time'],$q['freq'],$q['band'],$q['mode'],
            $q['rst_sent'],$q['rst_rcvd'],$q['serial_sent'],$q['serial_rcvd'],$q['dxcc'],$q['note']]);
    fclose($out);exit;
}

// ════════════════════════════════════════════════════════════
//  ADMIN
// ════════════════════════════════════════════════════════════
function route_admin(string $m,?string $resource,?string $id,array $b):void{
    $admin=require_admin();
    match($resource){
        'users'     => $m==='GET'?do_admin_list_users($admin):($m==='POST'?do_admin_create_user($admin,$b):json_error('Metodo non valido.',405)),
        'user'      => match($m){
            'GET'    => do_admin_get_user($admin,(int)$id),
            'PUT'    => do_admin_update_user($admin,(int)$id,$b),
            'DELETE' => do_admin_delete_user($admin,(int)$id,$b),
            'POST'   => $id&&$b['action']??''?do_admin_action($admin,(int)$id,$b):json_error('Azione mancante.'),
            default  => json_error('Metodo non valido.',405),
        },
        'stats'     => do_admin_stats(),
        'audit'     => do_admin_audit(),
        default     => json_error('Risorsa non valida.',404),
    };
}
function do_admin_list_users(array $admin):void{
    $q=$_GET['q']??''; $filter=$_GET['filter']??'all'; $page=(int)($_GET['page']??1);
    json_ok(admin_list_users($q,$filter,max(1,$page)));
}
function do_admin_get_user(array $admin,int $id):void{
    $u=admin_get_user_detail($id);
    if(!$u)json_error('Utente non trovato.',404);
    json_ok(['user'=>$u]);
}
function do_admin_update_user(array $admin,int $id,array $b):void{
    $r=admin_update_user($admin['id'],$admin['callsign'],$id,$b);
    if(!$r['ok'])json_error($r['error']);
    json_ok(['message'=>'Utente aggiornato.']);
}
function do_admin_delete_user(array $admin,int $id,array $b):void{
    $r=admin_delete_user($admin['id'],$admin['callsign'],$id,$b['qso_action']??'anonymize');
    if(!$r['ok'])json_error($r['error']);
    json_ok(['message'=>'Utente eliminato.']);
}
function do_admin_action(array $admin,int $id,array $b):void{
    $action=$b['action']??'';
    match($action){
        'reset-password' => (function() use($admin,$id){$r=admin_send_reset($admin['id'],$admin['callsign'],$id);$r['ok']?json_ok(['message'=>'Email reset inviata.']):json_error($r['error']);}  )(),
        'toggle-active'  => do_admin_update_user($admin,$id,['is_active'=>!(bool)($b['current_active']??true),'suspended_reason'=>$b['reason']??null]),
        default          => json_error('Azione non riconosciuta.',400),
    };
}
function do_admin_create_user(array $admin,array $b):void{
    $r=admin_create_user($admin['id'],$admin['callsign'],$b);
    if(!$r['ok'])json_error($r['error']);
    json_ok(['message'=>'Utente creato.','id'=>$r['id']],201);
}
function do_admin_stats():void{
    $db=db();
    json_ok([
        'total_users'    =>(int)$db->query('SELECT COUNT(*) FROM eqso_users')->fetchColumn(),
        'verified_users' =>(int)$db->query('SELECT COUNT(*) FROM eqso_users WHERE is_verified=1')->fetchColumn(),
        'active_users'   =>(int)$db->query('SELECT COUNT(*) FROM eqso_users WHERE is_active=1')->fetchColumn(),
        'total_qso'      =>(int)$db->query('SELECT COUNT(*) FROM eqso_qso_log')->fetchColumn(),
        'active_sessions'=>(int)$db->query('SELECT COUNT(*) FROM eqso_sessions WHERE expires_at>NOW()')->fetchColumn(),
    ]);
}
function do_admin_audit():void{
    $page=(int)($_GET['page']??1);
    json_ok(admin_get_audit(max(1,$page)));
}
