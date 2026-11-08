<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
// Il pannello admin usa cookie di sessione separati dal token API
session_start();
// Autenticazione admin via sessione PHP classica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $r = login_user($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($r['ok'] && $r['user']['is_admin']) {
        $_SESSION['admin_token'] = $r['token'];
        $_SESSION['admin_user']  = $r['user'];
        header('Location: '.$_SERVER['PHP_SELF']); exit;
    } else {
        $login_error = $r['ok'] ? 'Accesso riservato agli amministratori.' : ($r['error'] ?? 'Errore.');
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    if (isset($_SESSION['admin_token'])) logout_user($_SESSION['admin_token']);
    session_destroy(); header('Location: '.$_SERVER['PHP_SELF']); exit;
}
$logged_in  = isset($_SESSION['admin_user']);
$admin_user = $_SESSION['admin_user'] ?? null;
?><!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — EasyQSO</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@700&family=Share+Tech+Mono&display=swap');
  *{box-sizing:border-box;margin:0;padding:0}
  :root{--g:#39ff6a;--gd:#1a7a35;--bg:#0a0f0a;--s:#111711;--s2:#162016;--b:#2a402a;--t:#c8e8c8;--td:#5a8a5a;--tm:#3a5a3a;--red:#ff4444;--amb:#ffaa00}
  body{background:var(--bg);color:var(--t);font-family:'Barlow',sans-serif;min-height:100vh}
  a{color:var(--g);text-decoration:none}

  /* LOGIN */
  .login-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
  .login-card{background:var(--s);border:1px solid var(--b);border-radius:12px;padding:40px;max-width:380px;width:100%}
  .logo{font-family:'Barlow Condensed',sans-serif;font-size:28px;font-weight:700;color:var(--g);letter-spacing:2px;margin-bottom:4px}
  .sub-logo{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--tm);letter-spacing:2px;margin-bottom:28px}
  .field-lbl{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--tm);letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:4px}
  .field-inp{width:100%;background:var(--bg);border:1px solid var(--b);color:var(--t);font-family:'Share Tech Mono',monospace;font-size:14px;padding:9px 11px;border-radius:4px;outline:none;margin-bottom:14px}
  .field-inp:focus{border-color:var(--gd)}
  .btn-login{width:100%;background:var(--g);color:#040a04;border:none;font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:700;letter-spacing:2px;padding:11px;border-radius:4px;cursor:pointer}
  .err{background:rgba(255,68,68,.1);border:1px solid rgba(255,68,68,.4);color:var(--red);font-size:13px;padding:9px 12px;border-radius:4px;margin-bottom:14px}

  /* ADMIN LAYOUT */
  .topbar{background:var(--s);border-bottom:1px solid var(--b);padding:10px 24px;display:flex;align-items:center;gap:16px}
  .topbar .logo{font-size:22px;margin-bottom:0}
  .topbar .badge{font-family:'Share Tech Mono',monospace;font-size:10px;background:rgba(57,255,106,.15);color:var(--g);border:1px solid rgba(57,255,106,.3);padding:2px 8px;border-radius:3px;letter-spacing:1px}
  .topbar .user-info{margin-left:auto;font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--td)}
  .btn-logout{background:transparent;border:1px solid var(--b);color:var(--td);font-family:'Share Tech Mono',monospace;font-size:11px;padding:5px 12px;border-radius:4px;cursor:pointer;margin-left:12px}
  .btn-logout:hover{border-color:var(--red);color:var(--red)}

  .main{padding:24px}
  .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:28px}
  .stat-card{background:var(--s);border:1px solid var(--b);border-radius:8px;padding:18px;text-align:center}
  .stat-card .num{font-family:'Barlow Condensed',sans-serif;font-size:36px;font-weight:700;color:var(--g)}
  .stat-card .lbl{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--tm);letter-spacing:1px;text-transform:uppercase;margin-top:2px}

  .section-title{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--tm);letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--b)}
  .card{background:var(--s);border:1px solid var(--b);border-radius:8px;overflow:hidden;margin-bottom:24px}
  table{width:100%;border-collapse:collapse;font-family:'Share Tech Mono',monospace;font-size:12px}
  thead tr{background:var(--s2);border-bottom:1px solid var(--b)}
  th{padding:8px 12px;text-align:left;font-size:10px;color:var(--tm);letter-spacing:1px;text-transform:uppercase;white-space:nowrap}
  tbody tr{border-bottom:1px solid rgba(42,64,42,.5)}
  tbody tr:hover{background:var(--s2)}
  td{padding:8px 12px;color:var(--td);white-space:nowrap}
  td.call{color:#00d4ff;font-weight:600}
  td.email{color:var(--t)}
  .badge{display:inline-block;font-size:10px;padding:1px 6px;border-radius:3px;font-weight:600}
  .badge-yes{background:rgba(57,255,106,.12);color:var(--g);border:1px solid rgba(57,255,106,.25)}
  .badge-no{background:rgba(255,68,68,.1);color:var(--red);border:1px solid rgba(255,68,68,.25)}
  .badge-admin{background:rgba(255,170,0,.12);color:var(--amb);border:1px solid rgba(255,170,0,.25)}
  .act-btn{background:transparent;border:1px solid var(--b);color:var(--td);font-family:'Share Tech Mono',monospace;font-size:10px;padding:3px 8px;border-radius:3px;cursor:pointer;transition:all .15s}
  .act-btn:hover{border-color:var(--g);color:var(--g)}
  .act-btn.del:hover{border-color:var(--red);color:var(--red)}
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:100;align-items:center;justify-content:center;padding:20px}
  .modal-overlay.open{display:flex}
  .modal{background:var(--s);border:1px solid var(--b);border-radius:10px;padding:28px;max-width:400px;width:100%;position:relative}
  .modal h3{font-family:'Barlow Condensed',sans-serif;font-size:20px;color:var(--g);margin-bottom:16px;letter-spacing:1px}
  .modal-row{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--b)}
  .modal-row:last-of-type{border:none}
  .modal-row label{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--tm)}
  .toggle-wrap{display:flex;gap:6px}
  .r-btn{font-family:'Share Tech Mono',monospace;font-size:10px;padding:4px 10px;border-radius:3px;border:1px solid var(--b);background:transparent;color:var(--td);cursor:pointer}
  .r-btn.on{background:rgba(57,255,106,.15);color:var(--g);border-color:var(--gd)}
  .r-btn.off{background:rgba(255,68,68,.1);color:var(--red);border-color:rgba(255,68,68,.3)}
  .modal-actions{display:flex;gap:8px;margin-top:20px}
  .btn-save{flex:1;background:var(--g);color:#040a04;border:none;font-family:'Barlow Condensed',sans-serif;font-size:15px;font-weight:700;letter-spacing:1px;padding:9px;border-radius:4px;cursor:pointer}
  .btn-cancel{background:transparent;border:1px solid var(--b);color:var(--td);font-family:'Share Tech Mono',monospace;font-size:12px;padding:9px 16px;border-radius:4px;cursor:pointer}
  .notice{background:rgba(255,170,0,.08);border:1px solid rgba(255,170,0,.3);color:var(--amb);font-family:'Share Tech Mono',monospace;font-size:11px;padding:10px 14px;border-radius:4px;margin-bottom:20px}
  @media(max-width:600px){.stats-row{grid-template-columns:1fr 1fr}.main{padding:12px}}
</style>
</head>
<body>

<?php if (!$logged_in): ?>
<div class="login-wrap">
  <div class="login-card">
    <div class="logo">EasyQSO</div>
    <div class="sub-logo">PANNELLO ADMIN</div>
    <?php if (!empty($login_error)): ?><div class="err"><?= htmlspecialchars($login_error) ?></div><?php endif ?>
    <form method="post">
      <input type="hidden" name="action" value="login">
      <label class="field-lbl">Email admin</label>
      <input class="field-inp" type="email" name="email" placeholder="admin@example.com" required autofocus>
      <label class="field-lbl">Password</label>
      <input class="field-inp" type="password" name="password" placeholder="••••••••" required>
      <button class="btn-login" type="submit">ACCEDI</button>
    </form>
  </div>
</div>

<?php else: ?>
<div class="topbar">
  <div class="logo">EasyQSO</div>
  <span class="badge">ADMIN</span>
  <span class="user-info">👤 <?= htmlspecialchars($admin_user['callsign']) ?></span>
  <form method="post" style="margin:0">
    <input type="hidden" name="action" value="logout">
    <button class="btn-logout" type="submit">Logout</button>
  </form>
</div>

<div class="main">
  <div class="notice">⚠️ Ricorda di cambiare la password dell'account ADMIN di default appena possibile.</div>

  <!-- Stats -->
  <div class="section-title">Statistiche globali</div>
  <div class="stats-row" id="stats-row">
    <div class="stat-card"><div class="num" id="s-users">—</div><div class="lbl">Utenti totali</div></div>
    <div class="stat-card"><div class="num" id="s-verified">—</div><div class="lbl">Verificati</div></div>
    <div class="stat-card"><div class="num" id="s-qso">—</div><div class="lbl">QSO totali</div></div>
    <div class="stat-card"><div class="num" id="s-sess">—</div><div class="lbl">Sessioni attive</div></div>
  </div>

  <!-- Users table -->
  <div class="section-title" style="margin-top:8px">Utenti registrati</div>
  <div class="card">
    <div style="overflow-x:auto">
    <table>
      <thead><tr>
        <th>ID</th><th>Nominativo</th><th>Email</th>
        <th>Verificato</th><th>Admin</th><th>Attivo</th>
        <th>QSO</th><th>Registrato</th><th>Ultimo accesso</th><th></th>
      </tr></thead>
      <tbody id="users-tbody"><tr><td colspan="10" style="text-align:center;padding:24px;color:var(--tm)">Caricamento…</td></tr></tbody>
    </table>
    </div>
  </div>
</div>

<!-- Modal modifica utente -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal">
    <h3>Modifica utente</h3>
    <input type="hidden" id="edit-id">
    <div class="modal-row">
      <label>Verificato</label>
      <div class="toggle-wrap">
        <button class="r-btn on"  id="v-yes" onclick="setFlag('verified',1)">✓ Sì</button>
        <button class="r-btn off" id="v-no"  onclick="setFlag('verified',0)">✕ No</button>
      </div>
    </div>
    <div class="modal-row">
      <label>Admin</label>
      <div class="toggle-wrap">
        <button class="r-btn" id="a-yes" onclick="setFlag('admin',1)">✓ Sì</button>
        <button class="r-btn" id="a-no"  onclick="setFlag('admin',0)">✕ No</button>
      </div>
    </div>
    <div class="modal-row">
      <label>Attivo</label>
      <div class="toggle-wrap">
        <button class="r-btn" id="ac-yes" onclick="setFlag('active',1)">✓ Sì</button>
        <button class="r-btn" id="ac-no"  onclick="setFlag('active',0)">✕ No</button>
      </div>
    </div>
    <div class="modal-actions">
      <button class="btn-save" onclick="saveUser()">SALVA</button>
      <button class="btn-cancel" onclick="closeModal()">Annulla</button>
    </div>
  </div>
</div>

<script>
const API = '<?= BASE_URL ?>/api';
const TOKEN = '<?= $_SESSION['admin_token'] ?>';
const H = { 'Content-Type':'application/json', 'Authorization':'Bearer '+TOKEN };

async function apiFetch(url, opts={}) {
  const r = await fetch(url, { headers: H, ...opts });
  return r.json();
}

let editState = { verified:1, admin:0, active:1 };

async function loadStats() {
  const d = await apiFetch(API+'/admin/stats');
  if (d.ok) {
    document.getElementById('s-users').textContent   = d.total_users;
    document.getElementById('s-verified').textContent= d.verified_users;
    document.getElementById('s-qso').textContent     = d.total_qso;
    document.getElementById('s-sess').textContent    = d.active_sessions;
  }
}

async function loadUsers() {
  const d = await apiFetch(API+'/admin/users');
  const tb = document.getElementById('users-tbody');
  if (!d.ok || !d.users.length) { tb.innerHTML='<tr><td colspan="10" style="text-align:center;padding:24px;color:var(--tm)">Nessun utente.</td></tr>'; return; }
  tb.innerHTML = d.users.map(u => `
    <tr>
      <td>${u.id}</td>
      <td class="call">${u.callsign}</td>
      <td class="email">${u.email}</td>
      <td><span class="badge ${u.is_verified?'badge-yes':'badge-no'}">${u.is_verified?'Sì':'No'}</span></td>
      <td>${u.is_admin?'<span class="badge badge-admin">Admin</span>':''}</td>
      <td><span class="badge ${u.is_active?'badge-yes':'badge-no'}">${u.is_active?'Sì':'No'}</span></td>
      <td style="color:var(--g)">${u.qso_count}</td>
      <td>${(u.created_at||'').slice(0,10)}</td>
      <td>${(u.last_login||'—').slice(0,10)}</td>
      <td style="display:flex;gap:4px">
        <button class="act-btn" onclick="openEdit(${u.id},${u.is_verified},${u.is_admin},${u.is_active})">✏️</button>
        ${!u.is_admin?`<button class="act-btn del" onclick="deleteUser(${u.id},'${u.callsign}')">✕</button>`:''}
      </td>
    </tr>`).join('');
}

function openEdit(id, verified, admin, active) {
  document.getElementById('edit-id').value = id;
  editState = { verified:+verified, admin:+admin, active:+active };
  refreshEditBtns();
  document.getElementById('edit-modal').classList.add('open');
}
function closeModal() { document.getElementById('edit-modal').classList.remove('open'); }
function setFlag(f, v) {
  if (f==='verified') editState.verified = v;
  if (f==='admin')    editState.admin    = v;
  if (f==='active')   editState.active   = v;
  refreshEditBtns();
}
function refreshEditBtns() {
  const on='r-btn on', off='r-btn off', n='r-btn';
  document.getElementById('v-yes').className  = editState.verified ? on  : n;
  document.getElementById('v-no').className   = editState.verified ? n   : off;
  document.getElementById('a-yes').className  = editState.admin    ? on  : n;
  document.getElementById('a-no').className   = editState.admin    ? n   : off;
  document.getElementById('ac-yes').className = editState.active   ? on  : n;
  document.getElementById('ac-no').className  = editState.active   ? n   : off;
}
async function saveUser() {
  const id = document.getElementById('edit-id').value;
  const d  = await apiFetch(API+'/admin/user/'+id, {
    method:'PUT', body: JSON.stringify({ is_verified:editState.verified, is_admin:editState.admin, is_active:editState.active })
  });
  closeModal();
  if (d.ok) { loadUsers(); loadStats(); }
  else alert('Errore: '+(d.error||'?'));
}
async function deleteUser(id, call) {
  if (!confirm('Eliminare l\'utente '+call+' e tutti i suoi QSO?')) return;
  const d = await apiFetch(API+'/admin/user/'+id, { method:'DELETE' });
  if (d.ok) { loadUsers(); loadStats(); }
  else alert('Errore: '+(d.error||'?'));
}

document.getElementById('edit-modal').addEventListener('click', e => { if (e.target===e.currentTarget) closeModal(); });
loadStats(); loadUsers();
setInterval(loadStats, 30000);
</script>
<?php endif ?>
</body></html>
