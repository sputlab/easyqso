<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
$token   = trim($_GET['token'] ?? '');
$success = $token && verify_email($token);
?><!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verifica Email — EasyQSO</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Barlow+Condensed:wght@700&display=swap');
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#0a0f0a;color:#c8e8c8;font-family:'Barlow',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
  .card{background:#111711;border:1px solid #2a402a;border-radius:12px;padding:40px;max-width:420px;width:100%;text-align:center}
  .logo{font-family:'Barlow Condensed',sans-serif;font-size:32px;font-weight:700;color:#39ff6a;letter-spacing:2px;margin-bottom:24px}
  .icon{font-size:48px;margin-bottom:16px}
  h2{font-size:20px;margin-bottom:12px;color:<?= $success ? '#39ff6a' : '#ff4444' ?>}
  p{color:#5a8a5a;font-size:14px;line-height:1.6;margin-bottom:24px}
  .btn{display:inline-block;background:#39ff6a;color:#040a04;font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:700;letter-spacing:2px;padding:12px 32px;border-radius:6px;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="logo">EasyQSO</div>
  <div class="icon"><?= $success ? '✅' : '❌' ?></div>
  <h2><?= $success ? 'Email verificata!' : 'Link non valido' ?></h2>
  <p><?= $success ? 'Il tuo account è attivo. Puoi accedere a EasyQSO.' : 'Il link è scaduto o già utilizzato. Richiedi una nuova verifica.' ?></p>
  <a class="btn" href="<?= BASE_URL ?>">Vai al Login</a>
</div>
</body></html>
