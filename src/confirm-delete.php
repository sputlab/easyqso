<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/account.php';
require_once __DIR__.'/includes/i18n.php';
$L=i18n_lang();
$token=trim($_GET['token']??'');
$result=null;
if($token){ $result=account_confirm_delete($token); }
?><!DOCTYPE html><html lang="<?= $L ?>"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= tr('Eliminazione Account — EasyQSO') ?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Barlow+Condensed:wght@700&display=swap');
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0a0f0a;color:#c8e8c8;font-family:'Barlow',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
.card{background:#111711;border:1px solid #2a402a;border-radius:12px;padding:40px;max-width:440px;width:100%;text-align:center}
.logo{font-family:'Barlow Condensed',sans-serif;font-size:28px;font-weight:700;color:#39ff6a;letter-spacing:2px;margin-bottom:20px}
.icon{font-size:48px;margin-bottom:14px}
h2{font-size:20px;margin-bottom:10px}
p{color:#5a8a5a;font-size:14px;line-height:1.6;margin-bottom:22px}
.btn{display:inline-block;background:#39ff6a;color:#040a04;font-family:'Barlow Condensed',sans-serif;font-size:15px;font-weight:700;letter-spacing:1px;padding:11px 28px;border-radius:6px;text-decoration:none}
.err h2{color:#ff4444}
</style></head><body>
<div class="card">
  <div class="logo">EasyQSO</div>
  <?php if(!$token): ?>
    <div class="icon">❌</div><h2 style="color:#ff4444">   <?= tr('Link non valido') ?></h2>
    <p><?= tr('Nessun token fornito.') ?></p>
  <?php elseif(!$result||!$result['ok']): ?>
    <div class="icon">⚠️</div><h2 style="color:#ffaa00">   <?= tr('Link scaduto o già usato') ?></h2>
    <p><?= htmlspecialchars(tr($result['error']??'Il link non è più valido.')) ?></p>
  <?php else: ?>
    <div class="icon">✅</div><h2 style="color:#39ff6a">   <?= tr('Account eliminato') ?></h2>
    <p><?= tr('Il tuo account EasyQSO è stato eliminato con successo. I tuoi dati sono stati rimossi.') ?></p>
  <?php endif ?>
  <a class="btn" href="<?= BASE_URL ?>"><?= tr('Torna alla Home') ?></a>
</div></body></html>
