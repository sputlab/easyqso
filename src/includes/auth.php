<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';

function generate_token(int $bytes = 32): string { return bin2hex(random_bytes($bytes)); }

function json_ok(array $data = [], int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, ...$data]); exit;
}
function json_error(string $msg, int $code = 400): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $msg]); exit;
}

// ── Legge l'header Authorization in modo robusto ─────────────
// getallheaders() fallisce spesso su Nginx+PHP-FPM perché
// Nginx non passa Authorization a PHP per default.
// Usiamo $_SERVER come fallback primario.
function get_bearer_token(): ?string {
    $a = '';

    // 1. $_SERVER — il più affidabile su Nginx+PHP-FPM
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $a = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $a = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    // 2. getallheaders() — funziona su Apache, a volte su Nginx
    if (!$a && function_exists('getallheaders')) {
        $h = getallheaders();
        $a = $h['Authorization'] ?? $h['authorization'] ?? '';
    }

    // 3. Header HTTP_* manuale (alcuni hosting lo espongono così)
    if (!$a && !empty($_SERVER['HTTP_X_AUTHORIZATION'])) {
        $a = $_SERVER['HTTP_X_AUTHORIZATION'];
    }

    if (!$a) return null;
    if (!preg_match('/Bearer\s+(\S+)/i', $a, $m)) return null;
    return $m[1];
}

function auth_user(): ?array {
    $token = get_bearer_token();
    if (!$token) return null;

    $st = db()->prepare('
        SELECT u.* FROM eqso_sessions s
        JOIN eqso_users u ON u.id = s.user_id
        WHERE s.token = ? AND s.expires_at > NOW() AND u.is_active = 1
    ');
    $st->execute([$token]);
    $u = $st->fetch();
    if (!$u) return null;
    unset($u['password_hash'], $u['verify_token'], $u['reset_token']);
    return $u;
}

function require_auth(): array {
    $u = auth_user();
    if (!$u) { json_error('Non autenticato.', 401); exit; }
    return $u;
}
function require_admin(): array {
    $u = require_auth();
    if (!$u['is_admin']) { json_error('Accesso negato.', 403); exit; }
    return $u;
}

function register_user(string $callsign, string $email, string $password, string $lang = 'it'): array {
    $lang = i18n_valid($lang) ?? 'it';
    $db = db();
    $st = $db->prepare('SELECT id FROM eqso_users WHERE email = ?');
    $st->execute([strtolower($email)]);
    if ($st->fetch()) return ['ok' => false, 'error' => 'Email già registrata.'];
    $hash  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $token = generate_token(32);
    $db->prepare('INSERT INTO eqso_users (callsign, email, password_hash, verify_token, lang) VALUES (?,?,?,?,?)')
       ->execute([strtoupper($callsign), strtolower($email), $hash, $token, $lang]);
    send_verification_email($email, $callsign, $token, $lang);
    return ['ok' => true];
}

function login_user(string $email, string $password): array {
    $db = db();
    $st = $db->prepare('SELECT * FROM eqso_users WHERE email = ? AND is_active = 1');
    $st->execute([strtolower($email)]);
    $user = $st->fetch();
    if (!$user || !password_verify($password, $user['password_hash']))
        return ['ok' => false, 'error' => 'Credenziali non valide.'];
    if (!$user['is_verified'])
        return ['ok' => false, 'error' => 'Account non verificato. Controlla la tua email.'];
    $token  = generate_token(32);
    $expiry = date('Y-m-d H:i:s', time() + SESSION_TTL);
    $db->prepare('INSERT INTO eqso_sessions (user_id, token, expires_at) VALUES (?,?,?)')
       ->execute([$user['id'], $token, $expiry]);
    $db->prepare('UPDATE eqso_users SET last_login = NOW() WHERE id = ?')
       ->execute([$user['id']]);
    unset($user['password_hash'], $user['verify_token'], $user['reset_token']);
    return ['ok' => true, 'token' => $token, 'user' => $user];
}

function logout_user(string $token): void {
    db()->prepare('DELETE FROM eqso_sessions WHERE token = ?')->execute([$token]);
}

function verify_email(string $token): bool {
    $db = db();
    $st = $db->prepare('SELECT id FROM eqso_users WHERE verify_token = ? AND is_verified = 0');
    $st->execute([$token]);
    $u = $st->fetch();
    if (!$u) return false;
    $db->prepare('UPDATE eqso_users SET is_verified = 1, verify_token = NULL WHERE id = ?')
       ->execute([$u['id']]);
    return true;
}

function request_password_reset(string $email): void {
    $db = db();
    $st = $db->prepare('SELECT * FROM eqso_users WHERE email = ? AND is_active = 1');
    $st->execute([strtolower($email)]);
    $u = $st->fetch();
    if (!$u) return;
    $token   = generate_token(32);
    $expires = date('Y-m-d H:i:s', time() + 3600);
    $db->prepare('UPDATE eqso_users SET reset_token = ?, reset_expires = ? WHERE id = ?')
       ->execute([$token, $expires, $u['id']]);
    send_reset_email($u['email'], $u['callsign'], $token, $u['lang'] ?? 'it');
}

function do_password_reset(string $token, string $pw): bool {
    $db = db();
    $st = $db->prepare('SELECT id FROM eqso_users WHERE reset_token = ? AND reset_expires > NOW()');
    $st->execute([$token]);
    $u = $st->fetch();
    if (!$u) return false;
    $hash = password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
    $db->prepare('UPDATE eqso_users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?')
       ->execute([$hash, $u['id']]);
    return true;
}

function send_verification_email(string $to, string $call, string $token, string $lang = 'it'): void {
    $url  = BASE_URL . '/verify.php?token=' . $token;
    if ($lang === 'en') {
        $subj = 'Confirm your email — EasyQSO';
        $body = "Hi $call,\n\nClick the link to verify your EasyQSO account:\n$url\n\nThe link is valid for 48 hours.\n\nEasyQSO Team";
    } else {
        $subj = 'Conferma la tua email — EasyQSO';
        $body = "Ciao $call,\n\nClicca il link per verificare il tuo account EasyQSO:\n$url\n\nIl link è valido per 48 ore.\n\nEasyQSO Team";
    }
    $hdr  = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\nContent-Type: text/plain; charset=UTF-8";
    mail($to, $subj, $body, $hdr);
}

function send_reset_email(string $to, string $call, string $token, string $lang = 'it'): void {
    $url  = BASE_URL . '/reset-password.php?token=' . $token . '&lang=' . ($lang === 'en' ? 'en' : 'it');
    if ($lang === 'en') {
        $subj = 'Reset password — EasyQSO';
        $body = "Hi $call,\n\nYou requested a password reset:\n$url\n\nThe link expires in 1 hour.\n\nEasyQSO Team";
    } else {
        $subj = 'Reset password — EasyQSO';
        $body = "Ciao $call,\n\nHai richiesto il reset della password:\n$url\n\nIl link scade tra 1 ora.\n\nEasyQSO Team";
    }
    $hdr  = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\nContent-Type: text/plain; charset=UTF-8";
    mail($to, $subj, $body, $hdr);
}
