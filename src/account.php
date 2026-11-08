<?php require_once __DIR__.'/includes/config.php'; require_once __DIR__.'/includes/i18n.php'; ?>
<!DOCTYPE html>
<html lang="<?= i18n_lang() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Il mio account — EasyQSO</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@600;700&display=swap');
*{box-sizing:border-box;margin:0;padding:0}
:root,[data-theme="radio"]{--ac:#39ff6a;--acd:#1a7a35;--ac2:#ffaa00;--bg:#0a0f0a;--su:#111711;--br2:#2a402a;--tx:#c8e8c8;--txd:#5a8a5a;--txm:#3a5a3a;--red:#ff4444;--glow:rgba(57,255,106,.3)}
[data-theme="dark"]{--ac:#7c9dff;--acd:#3a5acc;--ac2:#f0a060;--bg:#0d0d0f;--su:#141418;--br2:#2e2e38;--tx:#d8d8e8;--txd:#7878a0;--txm:#4a4a60;--red:#ff5555;--glow:rgba(124,157,255,.25)}
[data-theme="light"]{--ac:#1a6fd4;--acd:#1055a8;--ac2:#c07010;--bg:#f0f2f8;--su:#fff;--br2:#c0c8e0;--tx:#1a1e2e;--txd:#4a5070;--txm:#8090b0;--red:#cc2222;--glow:rgba(26,111,212,.15)}
body{background:var(--bg);color:var(--tx);font-family:'Barlow',sans-serif;min-height:100vh;padding:20px}
.wrap{max-width:640px;margin:0 auto}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.logo{font-family:'Barlow Condensed',sans-serif;font-size:28px;font-weight:700;color:var(--ac);letter-spacing:2px;text-shadow:0 0 20px var(--glow)}
.back{font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--txd);text-decoration:none}
.back:hover{color:var(--ac)}
.card{background:var(--su);border:1px solid var(--br2);border-radius:12px;padding:24px;margin-bottom:18px}
h2{font-family:'Barlow Condensed',sans-serif;font-size:18px;letter-spacing:2px;color:var(--ac);margin-bottom:14px;text-transform:uppercase}
.row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:520px){.row{grid-template-columns:1fr}}
.fl{display:flex;flex-direction:column;gap:3px;margin-bottom:14px}
label{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);letter-spacing:1px;text-transform:uppercase}
select,input[type=text],input[type=password]{background:var(--bg);border:1px solid var(--br2);color:var(--tx);font-family:'Share Tech Mono',monospace;font-size:14px;padding:9px 11px;border-radius:4px;outline:none;width:100%}
input:focus,select:focus{border-color:var(--acd)}
input[readonly]{opacity:.65}
.hint{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm)}
.btn{background:var(--ac);color:var(--bg);border:none;font-family:'Barlow Condensed',sans-serif;font-size:15px;font-weight:700;letter-spacing:2px;padding:10px 20px;border-radius:4px;cursor:pointer}
.btn:hover{filter:brightness(1.08)}
.btn.danger{background:var(--red);color:#fff}
.btn.ghost{background:transparent;color:var(--ac);border:1px solid var(--acd)}
.msg{font-family:'Share Tech Mono',monospace;font-size:12px;padding:9px 12px;border-radius:4px;margin-bottom:14px;display:none}
.msg.show{display:block}
.msg.err{background:rgba(255,68,68,.1);border:1px solid rgba(255,68,68,.4);color:var(--red)}
.msg.ok{background:color-mix(in srgb,var(--ac) 10%,transparent);border:1px solid color-mix(in srgb,var(--ac) 30%,transparent);color:var(--ac)}
.sess{display:flex;gap:10px;align-items:center;font-family:'Share Tech Mono',monospace;font-size:12px;padding:8px 0;border-bottom:1px solid var(--br2)}
.sess .cur{background:var(--ac);color:var(--bg);padding:1px 6px;border-radius:3px;font-size:10px}
.sess .rev{margin-left:auto;color:var(--red);cursor:pointer}
.radios label{display:block;text-transform:none;font-size:12px;color:var(--txd);margin-bottom:6px;cursor:pointer}
.danger-zone{border-color:rgba(255,68,68,.4)}
.danger-zone h2{color:var(--red)}
.danger-zone p{font-size:13px;color:var(--txd);margin-bottom:12px}
#gate{text-align:center;padding:40px 0;display:none}
</style>
</head>
<body>
<div class="wrap">
 <div class="top"><div class="logo">EasyQSO</div><a class="back" href="<?= BASE_URL ?>/">← Torna al logbook</a></div>

 <div id="gate" class="card">Sessione non valida. <a class="back" href="<?= BASE_URL ?>/">Esegui il login</a></div>

 <div id="main" style="display:none">
  <div class="card">
   <h2>Profilo</h2>
   <div class="msg" id="m-prof"></div>
   <div class="fl"><label for="callsign">Nominativo</label>
    <input type="text" id="callsign" maxlength="12" autocapitalize="characters" autocomplete="off">
    <span class="hint">2–12 caratteri: lettere, numeri e "/"</span></div>
   <div class="row">
    <div class="fl"><label>Email</label><input type="text" id="email" readonly></div>
    <div class="fl"><label>QSO registrati</label><input type="text" id="qsos" readonly></div>
   </div>
   <div class="row">
    <div class="fl"><label>Membro dal</label><input type="text" id="created" readonly></div>
    <div class="fl"><label>Ultimo accesso</label><input type="text" id="lastlogin" readonly></div>
   </div>
   <button class="btn" id="btn-call">SALVA NOMINATIVO</button>
  </div>

  <div class="card">
   <h2>Lingua</h2>
   <div class="msg" id="m-lang"></div>
   <div class="fl"><label for="lang">Lingua dell'interfaccia</label>
    <select id="lang"><option value="it" data-notr>Italiano</option><option value="en" data-notr>English</option></select></div>
  </div>

  <div class="card">
   <h2>Sicurezza</h2>
   <div class="msg" id="m-pw"></div>
   <div class="fl"><label for="oldpw">Password attuale</label><input type="password" id="oldpw" autocomplete="current-password"></div>
   <div class="row">
    <div class="fl"><label for="newpw">Nuova password</label><input type="password" id="newpw" autocomplete="new-password" placeholder="Min 8, 1 maiusc., 1 numero"></div>
    <div class="fl"><label for="newpw2">Conferma</label><input type="password" id="newpw2" autocomplete="new-password"></div>
   </div>
   <div class="fl radios"><label><input type="checkbox" id="logout-others"> Disconnetti le altre sessioni attive</label></div>
   <button class="btn" id="btn-pw">AGGIORNA PASSWORD</button>
  </div>

  <div class="card">
   <h2>Sessioni attive</h2>
   <div class="msg" id="m-sess"></div>
   <div id="sess-list" class="hint">Caricamento…</div>
  </div>

  <div class="card danger-zone">
   <h2>Elimina account</h2>
   <div class="msg" id="m-del"></div>
   <p>Azione irreversibile. Riceverai un'email con un link di conferma valido 24 ore.</p>
   <div class="fl radios">
    <label><input type="radio" name="qa" value="anonymize" checked> Anonimizza i QSO (nominativo → "DELETED")</label>
    <label><input type="radio" name="qa" value="delete"> Elimina tutti i QSO</label>
   </div>
   <button class="btn danger" id="btn-del">RICHIEDI ELIMINAZIONE</button>
  </div>
 </div>
</div>

<?= i18n_client_script() ?>
<script>
const API='<?= BASE_URL ?>/api';
let token=null;
try{token=localStorage.getItem('easyqso_token')}catch(e){}
if(!token){const m=document.cookie.match(/(?:^|;\s*)easyqso_token=([a-f0-9]{64})/);if(m)token=m[1]}
try{const t=localStorage.getItem('easyqso_theme');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}

const $=id=>document.getElementById(id);
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
function say(id,text,ok=false){const el=$(id);el.textContent=text;el.className='msg show '+(ok?'ok':'err');setTimeout(()=>el.className='msg',4500)}
async function api(path,opts={}){
  const r=await fetch(API+path,{headers:{'Content-Type':'application/json','Authorization':'Bearer '+token},...opts});
  const d=await r.json().catch(()=>({}));
  if(r.status===401){$('main').style.display='none';$('gate').style.display='block';return{ok:false,error:'Sessione scaduta.'}}
  return d;
}

async function load(){
  const [s,st,me]=await Promise.all([api('/user/settings'),api('/user/stats'),api('/auth/me')]);
  if(!s.ok)return;
  if(s.settings.lang)I18N.set(s.settings.lang);
  $('lang').value=I18N.lang;
  $('callsign').value=s.settings.callsign;$('email').value=s.settings.email;
  if(st.ok)$('qsos').value=st.stats.total+' QSO';
  if(me.ok){$('created').value=(me.user.created_at||'').slice(0,10);$('lastlogin').value=(me.user.last_login||'Mai').slice(0,16)}
  $('main').style.display='block';
  loadSessions();
}
async function loadSessions(){
  const d=await api('/user/sessions');const el=$('sess-list');
  if(!d.ok||!d.sessions.length){el.textContent='Nessuna sessione attiva.';return}
  el.innerHTML=d.sessions.map(s=>`<div class="sess">${s.current?'<span class="cur">CORRENTE</span>':''}<span>${esc(s.created_at.slice(0,16))}</span><span class="hint" style="margin-left:auto">scade ${esc(s.expires_at.slice(0,10))}</span>${s.current?'':`<span class="rev" data-sid="${+s.id}">✕ Revoca</span>`}</div>`).join('');
  el.querySelectorAll('.rev').forEach(b=>b.onclick=async()=>{const r=await api('/user/session/'+b.dataset.sid,{method:'DELETE'});if(r.ok)loadSessions();else say('m-sess',r.error||'Errore.')});
}

$('lang').onchange=async()=>{
  const l=$('lang').value;
  const d=await api('/user/settings',{method:'POST',body:JSON.stringify({lang:l})});
  if(!d.ok)return say('m-lang',d.error||'Errore.');
  I18N.set(l);
  say('m-lang','Lingua aggiornata.',true);
};
$('btn-call').onclick=async()=>{
  const call=$('callsign').value.trim().toUpperCase();
  const d=await api('/user/callsign',{method:'PUT',body:JSON.stringify({callsign:call})});
  if(!d.ok)return say('m-prof',d.error||'Errore.');
  $('callsign').value=d.callsign;
  say('m-prof','Nominativo aggiornato: '+d.callsign,true);
};
$('btn-pw').onclick=async()=>{
  const old=$('oldpw').value,pw=$('newpw').value,pw2=$('newpw2').value;
  if(!old||!pw||!pw2)return say('m-pw','Compila tutti i campi.');
  if(pw!==pw2)return say('m-pw','Le password non coincidono.');
  const lo=$('logout-others').checked;
  const d=await api('/user/change-password',{method:'POST',body:JSON.stringify({old_password:old,new_password:pw,new_password2:pw2,logout_others:lo})});
  if(!d.ok)return say('m-pw',d.error||'Errore.');
  ['oldpw','newpw','newpw2'].forEach(i=>$(i).value='');
  say('m-pw','Password aggiornata!',true);if(lo)loadSessions();
};
$('btn-del').onclick=async()=>{
  const qa=document.querySelector('input[name="qa"]:checked').value;
  if(!confirm(`Sei sicuro? Riceverai un'email di conferma.\nI QSO saranno: ${qa==='delete'?'ELIMINATI':'ANONIMIZZATI'}`))return;
  const d=await api('/user/request-delete',{method:'POST',body:JSON.stringify({qso_action:qa})});
  if(!d.ok)return say('m-del',d.error||'Errore.');
  say('m-del','Email inviata! Controlla la casella entro 24 ore.',true);
};

if(token)load();else $('gate').style.display='block';
</script>
</body>
</html>
