<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/i18n.php';
$L = i18n_lang();
$token   = trim($_GET['token'] ?? '');
$done    = false;
$error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw  = $_POST['password']  ?? '';
    $pw2 = $_POST['password2'] ?? '';
    $tok = $_POST['token']     ?? '';
    if (strlen($pw) < 8)   $error = tr('Password minimo 8 caratteri.');
    elseif ($pw !== $pw2)  $error = tr('Le password non coincidono.');
    elseif (!do_password_reset($tok, $pw)) $error = tr('Token scaduto o non valido.');
    else $done = true;
}
?><!DOCTYPE html>
<html lang="<?= $L ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= tr('Reset Password — EasyQSO') ?></title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Barlow+Condensed:wght@700&family=Share+Tech+Mono&display=swap');
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#0a0f0a;color:#c8e8c8;font-family:'Barlow',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
  .card{background:#111711;border:1px solid #2a402a;border-radius:12px;padding:36px;max-width:400px;width:100%}
  .logo{font-family:'Barlow Condensed',sans-serif;font-size:28px;font-weight:700;color:#39ff6a;letter-spacing:2px;margin-bottom:6px}
  .sub{font-family:'Share Tech Mono',monospace;font-size:11px;color:#3a5a3a;letter-spacing:1px;margin-bottom:28px}
  label{font-family:'Share Tech Mono',monospace;font-size:10px;color:#3a5a3a;letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:4px}
  input{width:100%;background:#0a0f0a;border:1px solid #2a402a;color:#c8e8c8;font-family:'Share Tech Mono',monospace;font-size:14px;padding:10px 12px;border-radius:4px;outline:none;margin-bottom:14px}
  input:focus{border-color:#1a7a35}
  .btn{width:100%;background:#39ff6a;color:#040a04;border:none;font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:700;letter-spacing:2px;padding:12px;border-radius:4px;cursor:pointer;margin-top:4px}
  .error{background:rgba(255,68,68,.1);border:1px solid rgba(255,68,68,.4);color:#ff4444;font-size:13px;padding:10px 12px;border-radius:4px;margin-bottom:14px}
  .success{text-align:center;padding:16px 0}
  .success .icon{font-size:48px;margin-bottom:12px}
  .success h2{color:#39ff6a;margin-bottom:10px}
  .success p{color:#5a8a5a;font-size:14px;margin-bottom:20px}
  .link{display:inline-block;background:#39ff6a;color:#040a04;font-family:'Barlow Condensed',sans-serif;font-size:15px;font-weight:700;letter-spacing:2px;padding:10px 28px;border-radius:4px;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="logo">EasyQSO</div>
  <div class="sub"><?= tr('RESET PASSWORD') ?></div>
  <?php if ($done): ?>
  <div class="success">
    <div class="icon">✅</div>
    <h2><?= tr('Password aggiornata!') ?></h2>
    <p><?= tr('Puoi accedere con la tua nuova password.') ?></p>
    <a class="link" href="<?= BASE_URL ?>"><?= tr('Vai al Login') ?></a>
  </div>
  <?php elseif (!$token): ?>
  <div class="error"><?= tr('Link non valido. Richiedi un nuovo reset dalla pagina di login.') ?></div>
  <a class="link" href="<?= BASE_URL ?>"><?= tr('Torna al Login') ?></a>
  <?php else: ?>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif ?>
  <form method="post">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <label><?= tr('Nuova password') ?></label>
    <input type="password" name="password" placeholder="<?= tr('Minimo 8 caratteri') ?>" required>
    <label><?= tr('Conferma password') ?></label>
    <input type="password" name="password2" placeholder="<?= tr('Ripeti la password') ?>" required>
    <button class="btn" type="submit"><?= tr('AGGIORNA PASSWORD') ?></button>
  </form>
  <?php endif ?>
</div>
</body></html>
