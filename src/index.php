<?php require_once __DIR__.'/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover">
<title>EasyQSO v<?= APP_VERSION ?></title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js" defer></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@600;700&display=swap');
*{box-sizing:border-box;margin:0;padding:0}
:root,[data-theme="radio"]{
  --ac:#39ff6a;--acd:#1a7a35;--ac2:#ffaa00;--ac3:#00d4ff;--ac4:#b388ff;
  --bg:#0a0f0a;--su:#111711;--su2:#162016;--br:#1e2e1e;--br2:#2a402a;
  --tx:#c8e8c8;--txd:#5a8a5a;--txm:#3a5a3a;--red:#ff4444;--glow:rgba(57,255,106,.3);
  --tog:#2a402a;--thbtn:rgba(57,255,106,.15);--ov:rgba(0,0,0,.75);--mbg:#131a13;--mbr:#2a402a;}
[data-theme="dark"]{
  --ac:#7c9dff;--acd:#3a5acc;--ac2:#f0a060;--ac3:#60d0f0;--ac4:#c4a0f8;
  --bg:#0d0d0f;--su:#141418;--su2:#1a1a20;--br:#222228;--br2:#2e2e38;
  --tx:#d8d8e8;--txd:#7878a0;--txm:#4a4a60;--red:#ff5555;--glow:rgba(124,157,255,.25);
  --tog:#2e2e38;--thbtn:rgba(124,157,255,.15);--ov:rgba(0,0,0,.8);--mbg:#18181e;--mbr:#2e2e38;}
[data-theme="light"]{
  --ac:#1a6fd4;--acd:#1055a8;--ac2:#c07010;--ac3:#0090b8;--ac4:#7040c0;
  --bg:#f0f2f8;--su:#ffffff;--su2:#e8eaf4;--br:#d0d4e8;--br2:#c0c8e0;
  --tx:#1a1e2e;--txd:#4a5070;--txm:#8090b0;--red:#cc2222;--glow:rgba(26,111,212,.15);
  --tog:#c0c8e0;--thbtn:rgba(26,111,212,.12);--ov:rgba(0,0,0,.5);--mbg:#fff;--mbr:#c0c8e0;}
body{background:var(--bg);color:var(--tx);font-family:'Barlow',sans-serif;min-height:100vh;display:flex;flex-direction:column;transition:background .25s,color .25s}
a{color:var(--ac)}
/* AUTH */
.auth-wrap{display:flex;align-items:center;justify-content:center;flex:1;padding:24px}
.auth-card{background:var(--su);border:1px solid var(--br2);border-radius:12px;padding:36px;max-width:420px;width:100%}
.auth-logo{font-family:'Barlow Condensed',sans-serif;font-size:30px;font-weight:700;color:var(--ac);letter-spacing:2px;text-shadow:0 0 20px var(--glow);margin-bottom:2px}
.auth-sub{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);letter-spacing:2px;margin-bottom:28px}
.auth-tabs{display:flex;border-bottom:1px solid var(--br2);margin-bottom:24px}
.auth-tab{background:transparent;border:none;font-family:'Barlow Condensed',sans-serif;font-size:15px;font-weight:600;letter-spacing:1px;color:var(--txm);padding:8px 20px;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s}
.auth-tab.active{color:var(--ac);border-bottom-color:var(--ac)}
.auth-screen{display:none}.auth-screen.active{display:block}
.fl{display:flex;flex-direction:column;gap:3px;margin-bottom:14px}
.fl label{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);letter-spacing:1px;text-transform:uppercase}
.fl input{background:var(--bg);border:1px solid var(--br2);color:var(--tx);font-family:'Share Tech Mono',monospace;font-size:14px;padding:9px 11px;border-radius:4px;outline:none;transition:border-color .15s}
.fl input:focus{border-color:var(--acd)}.fl input::placeholder{color:var(--txm)}
.btn-primary{width:100%;background:var(--ac);color:var(--bg);border:none;font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:700;letter-spacing:2px;padding:11px;border-radius:4px;cursor:pointer;margin-top:4px;transition:filter .15s}
.btn-primary:hover{filter:brightness(1.08)}
.auth-msg{font-family:'Share Tech Mono',monospace;font-size:12px;padding:9px 12px;border-radius:4px;margin-bottom:14px;display:none}
.auth-msg.err{background:rgba(255,68,68,.1);border:1px solid rgba(255,68,68,.4);color:var(--red)}
.auth-msg.ok{background:color-mix(in srgb,var(--ac) 10%,transparent);border:1px solid color-mix(in srgb,var(--ac) 30%,transparent);color:var(--ac)}
.auth-msg.show{display:block}
.forgot-link{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txd);text-align:right;margin-top:-8px;margin-bottom:14px;cursor:pointer}
.forgot-link:hover{color:var(--ac)}
/* HEADER */
.header{background:var(--su);border-bottom:1px solid var(--br2);padding:10px 16px;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:8px;transition:background .25s;position:relative}
.header-left{display:flex;align-items:center;gap:12px}
.logo{font-family:'Barlow Condensed',sans-serif;font-size:24px;font-weight:700;color:var(--ac);text-shadow:0 0 20px var(--glow);letter-spacing:2px}
.logo span{color:var(--txm);font-size:10px;font-family:'Share Tech Mono',monospace;display:block;letter-spacing:1px;margin-top:-3px}
.stats-row{display:flex;gap:16px;flex-wrap:wrap;align-items:center}
.stat-pill{font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--txd);display:flex;flex-direction:column;align-items:center}
.stat-pill strong{color:var(--ac);font-size:17px;transition:color .25s}
.header-center{display:flex;flex-direction:column;align-items:center}
.utc-clock{font-family:'Share Tech Mono',monospace;font-size:22px;color:var(--ac2);letter-spacing:3px}
.utc-label{font-size:9px;color:var(--txm);letter-spacing:2px}
.header-right{display:flex;align-items:center;justify-content:flex-end;gap:8px}
.user-badge{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--ac3);padding:4px 10px;border:1px solid var(--br2);border-radius:4px}
/* BURGER */
.burger-btn{background:transparent;border:1px solid var(--br2);color:var(--txd);width:36px;height:36px;border-radius:6px;cursor:pointer;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;padding:8px;transition:all .15s}
.burger-btn:hover{border-color:var(--ac);color:var(--ac)}
.burger-btn span{display:block;width:16px;height:2px;background:currentColor;border-radius:2px;transition:all .2s}
.burger-btn.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
.burger-btn.open span:nth-child(2){opacity:0;transform:scaleX(0)}
.burger-btn.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
.burger-menu{position:absolute;top:calc(100% + 4px);right:12px;background:var(--su);border:1px solid var(--br2);border-radius:8px;padding:12px;width:230px;z-index:200;box-shadow:0 8px 32px rgba(0,0,0,.4);display:none;flex-direction:column;gap:5px}
.burger-menu.open{display:flex}
.menu-sep{font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--txm);letter-spacing:2px;text-transform:uppercase;padding:4px 0 2px;border-bottom:1px solid var(--br);margin-bottom:2px;margin-top:4px}
.theme-btn{font-family:'Share Tech Mono',monospace;font-size:11px;padding:7px 10px;border-radius:5px;border:1px solid transparent;background:transparent;color:var(--txd);cursor:pointer;display:flex;align-items:center;gap:8px;width:100%;transition:all .15s}
.theme-btn:hover{background:var(--su2);color:var(--tx);border-color:var(--br2)}
.theme-btn.active{background:var(--thbtn);color:var(--ac);border-color:var(--acd)}
.tdot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.tdot-r{background:#39ff6a}.tdot-d{background:#7c9dff}.tdot-l{background:#1a6fd4}
.menu-btn{font-family:'Barlow',sans-serif;font-size:13px;font-weight:500;padding:7px 10px;border-radius:5px;border:1px solid transparent;background:transparent;color:var(--txd);cursor:pointer;display:flex;align-items:center;gap:8px;width:100%;text-align:left;transition:all .15s}
.menu-btn:hover{background:var(--su2);color:var(--tx);border-color:var(--br2)}
/* CONTEST BAR */
.contest-bar{display:none;background:color-mix(in srgb,var(--ac2) 8%,transparent);border-bottom:1px solid color-mix(in srgb,var(--ac2) 30%,transparent);padding:6px 16px;align-items:center;gap:20px;flex-wrap:wrap}
.contest-bar.active{display:flex}
.contest-timer{font-family:'Share Tech Mono',monospace;font-size:22px;color:var(--ac2);letter-spacing:2px}
.clabel{font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--txm);text-transform:uppercase;letter-spacing:1px;display:block}
.contest-score{font-family:'Share Tech Mono',monospace;font-size:16px;color:var(--ac);font-weight:700}
.contest-toggle{background:transparent;border:1px solid var(--br2);color:var(--txd);font-family:'Share Tech Mono',monospace;font-size:10px;padding:4px 10px;border-radius:4px;cursor:pointer;transition:all .15s;white-space:nowrap}
.contest-toggle:hover{border-color:var(--ac2);color:var(--ac2)}
.contest-toggle.on{border-color:var(--ac2);color:var(--ac2);background:color-mix(in srgb,var(--ac2) 10%,transparent)}
/* FORM */
.form-panel{background:var(--su);border-bottom:1px solid var(--br2);padding:12px 16px;transition:background .25s}
.form-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1.4fr 1fr 1fr .8fr .8fr .8fr .8fr 1fr 1.4fr;gap:8px;align-items:end}
.field{display:flex;flex-direction:column;gap:3px}
.field label{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);letter-spacing:1px;text-transform:uppercase;white-space:nowrap}
.field input,.field select{background:var(--bg);border:1px solid var(--br2);color:var(--tx);font-family:'Share Tech Mono',monospace;font-size:14px;padding:7px 10px;border-radius:4px;width:100%;outline:none;transition:border-color .15s;-webkit-appearance:none;appearance:none}
.field input:focus,.field select:focus{border-color:var(--acd);box-shadow:0 0 0 2px color-mix(in srgb,var(--ac) 12%,transparent)}
.field input::placeholder{color:var(--txm)}.field select option{background:var(--su);color:var(--tx)}
.field.fc input{font-size:18px;font-weight:600;letter-spacing:2px;color:var(--ac3);text-transform:uppercase}
.field.ff input{color:var(--ac4)}.field.fb select{color:var(--ac);font-weight:600}
.field.fb select.auto{border-color:var(--acd);box-shadow:0 0 0 2px color-mix(in srgb,var(--ac) 15%,transparent)}
.field.fr input{color:var(--ac2)}
.qrz-btn{background:transparent;border:none;cursor:pointer;font-size:14px;padding:2px 4px;border-radius:3px;color:var(--txm);transition:all .15s}
.qrz-btn:hover{color:var(--ac3);background:color-mix(in srgb,var(--ac3) 10%,transparent)}
.dxcc-badge{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--ac);background:color-mix(in srgb,var(--ac) 10%,transparent);border:1px solid color-mix(in srgb,var(--ac) 25%,transparent);padding:1px 6px;border-radius:3px;margin-top:2px;display:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dxcc-badge.show{display:block}
.edit-input{background:var(--bg);border:1px solid var(--acd);color:var(--tx);font-family:'Share Tech Mono',monospace;font-size:12px;padding:3px 6px;border-radius:3px;outline:none;width:100%;min-width:55px}
.edit-sel{background:var(--bg);border:1px solid var(--acd);color:var(--tx);font-family:'Share Tech Mono',monospace;font-size:12px;padding:3px 4px;border-radius:3px;outline:none;-webkit-appearance:none;width:100%}
.btn-row{display:flex;gap:8px;margin-top:10px;align-items:center;flex-wrap:wrap}
.btn-log{background:var(--ac);color:var(--bg);border:none;font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:700;letter-spacing:2px;padding:10px 28px;border-radius:4px;cursor:pointer;transition:all .1s;text-transform:uppercase;white-space:nowrap}
.btn-log:hover{filter:brightness(1.1);transform:translateY(-1px)}.btn-log:active{transform:scale(.98)}
.btn-sec{background:transparent;border:1px solid var(--br2);color:var(--txd);font-family:'Share Tech Mono',monospace;font-size:12px;padding:10px 16px;border-radius:4px;cursor:pointer;transition:all .15s;white-space:nowrap}
.btn-sec:hover{border-color:var(--txd);color:var(--tx)}
.tog-opts{display:flex;gap:16px;align-items:center;margin-left:8px;flex-wrap:wrap}
.tog-item{display:flex;align-items:center;gap:7px;cursor:pointer;user-select:none}
.tog-sw{position:relative;width:36px;height:20px;flex-shrink:0}
.tog-sw input{opacity:0;width:0;height:0;position:absolute}
.tog-track{position:absolute;inset:0;background:var(--tog);border-radius:10px;border:1px solid var(--br2);transition:background .2s;cursor:pointer}
.tog-thumb{position:absolute;top:3px;left:3px;width:12px;height:12px;border-radius:50%;background:var(--txm);transition:transform .2s,background .2s}
.tog-sw input:checked~.tog-track{background:var(--acd);border-color:var(--acd)}
.tog-sw input:checked~.tog-track .tog-thumb{transform:translateX(16px);background:var(--ac)}
.tog-lbl{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txd);line-height:1.3}
.tog-lbl strong{display:block;font-size:10px;color:var(--txm);font-weight:400}
.band-hint{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--ac);margin-left:auto;opacity:0;transition:opacity .2s;white-space:nowrap}
.band-hint.v{opacity:1}
.dupe-alert{display:none;background:color-mix(in srgb,var(--red) 10%,transparent);border:1px solid color-mix(in srgb,var(--red) 40%,transparent);color:var(--red);font-family:'Share Tech Mono',monospace;font-size:12px;padding:6px 12px;border-radius:4px;margin-top:8px}
/* TOOLBAR */
.toolbar{background:var(--su2);border-bottom:1px solid var(--br);padding:8px 16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;transition:background .25s}
.tlbl{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm)}
.fi{background:var(--bg);border:1px solid var(--br2);color:var(--tx);font-family:'Share Tech Mono',monospace;font-size:12px;padding:5px 10px;border-radius:4px;outline:none;width:130px}
.fi:focus{border-color:var(--acd)}.fi::placeholder{color:var(--txm)}
.bpills{display:flex;gap:6px;flex-wrap:wrap}
.bp{font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:.5px;padding:3px 8px;border-radius:3px;border:1px solid var(--br2);color:var(--txm);background:transparent;cursor:pointer;transition:all .15s}
.bp:hover{border-color:var(--txd);color:var(--txd)}
.bp.active{border-color:var(--acd);color:var(--ac);background:color-mix(in srgb,var(--ac) 8%,transparent)}
.btn-exp{margin-left:auto;background:transparent;border:1px solid var(--br2);color:var(--txd);font-family:'Share Tech Mono',monospace;font-size:11px;padding:5px 12px;border-radius:4px;cursor:pointer;transition:all .15s}
.btn-exp:hover{border-color:var(--ac2);color:var(--ac2)}
/* TABLE */
.log-wrap{overflow-x:auto;flex:1;min-height:150px}
table{width:100%;border-collapse:collapse;font-family:'Share Tech Mono',monospace;font-size:13px;min-width:640px}
thead{position:sticky;top:0;z-index:10}
thead tr{background:var(--su2);border-bottom:1px solid var(--br2);transition:background .25s}
th{padding:8px 10px;text-align:left;font-size:10px;color:var(--txm);letter-spacing:1.5px;text-transform:uppercase;white-space:nowrap;cursor:pointer;user-select:none}
th:hover{color:var(--ac)}
th.sa::after{content:' ↑';color:var(--ac)}th.sd::after{content:' ↓';color:var(--ac)}
tbody tr{border-bottom:1px solid var(--br);transition:background .1s;animation:rIn .2s ease}
@keyframes rIn{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:none}}
tbody tr:hover{background:var(--su)}
tbody tr.editing{background:color-mix(in srgb,var(--ac) 4%,transparent)}
td{padding:6px 10px;white-space:nowrap;color:var(--txd);vertical-align:middle}
td.call{color:var(--ac3);font-size:14px;font-weight:600;letter-spacing:1px}
td.fq{color:var(--ac4)}td.rs{color:var(--ac2)}td.sn{color:var(--txm);font-size:11px}td.bc{color:var(--ac);font-size:12px;font-weight:600}
.bm{font-size:10px;padding:2px 6px;border-radius:3px;letter-spacing:.5px;font-weight:600}
.m-ssb{background:rgba(57,255,106,.12);color:#39ff6a;border:1px solid rgba(57,255,106,.2)}
.m-cw{background:rgba(255,170,0,.12);color:#ffaa00;border:1px solid rgba(255,170,0,.2)}
.m-ft8{background:rgba(0,212,255,.1);color:#00d4ff;border:1px solid rgba(0,212,255,.2)}
.m-ft4{background:rgba(0,212,255,.08);color:#70e8ff;border:1px solid rgba(0,212,255,.15)}
.m-am{background:rgba(179,136,255,.1);color:#b388ff;border:1px solid rgba(179,136,255,.2)}
.m-digi{background:rgba(255,68,68,.1);color:#ff8888;border:1px solid rgba(255,68,68,.2)}
.btn-del{background:transparent;border:none;color:var(--txm);cursor:pointer;font-size:13px;padding:3px 5px;border-radius:3px;transition:all .15s}
.btn-del:hover{color:var(--red);background:color-mix(in srgb,var(--red) 10%,transparent)}
.btn-edt{background:transparent;border:none;color:var(--txm);cursor:pointer;font-size:13px;padding:3px 5px;border-radius:3px;transition:all .15s}
.btn-edt:hover{color:var(--ac);background:color-mix(in srgb,var(--ac) 10%,transparent)}
.btn-sav{background:var(--ac);color:var(--bg);border:none;font-family:'Share Tech Mono',monospace;font-size:10px;font-weight:700;padding:3px 8px;border-radius:3px;cursor:pointer}
.btn-cnc{background:transparent;color:var(--txm);border:1px solid var(--br2);font-family:'Share Tech Mono',monospace;font-size:10px;padding:3px 8px;border-radius:3px;cursor:pointer;margin-left:3px}
.empty{text-align:center;padding:50px 20px;color:var(--txm);font-family:'Share Tech Mono',monospace;font-size:13px;letter-spacing:1px}
.empty div{font-size:32px;margin-bottom:12px;opacity:.3}
/* DX CLUSTER */
.cluster-panel{position:fixed;right:0;top:50%;transform:translateY(-50%);width:300px;background:var(--su);border:1px solid var(--br2);border-right:none;border-radius:8px 0 0 8px;z-index:300;display:none;flex-direction:column;max-height:70vh;box-shadow:-4px 0 20px rgba(0,0,0,.3)}
.cluster-panel.open{display:flex}
.cluster-head{padding:10px 12px;border-bottom:1px solid var(--br2);display:flex;align-items:center;gap:8px;flex-shrink:0}
.cluster-title{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--ac);letter-spacing:1px;text-transform:uppercase;flex:1}
.cluster-dot{width:7px;height:7px;border-radius:50%;background:var(--red);flex-shrink:0}
.cluster-dot.live{background:var(--ac);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.cluster-list{overflow-y:auto;flex:1}
.cluster-spot{padding:7px 10px;border-bottom:1px solid var(--br);cursor:pointer;transition:background .1s;display:flex;flex-direction:column;gap:2px}
.cluster-spot:hover{background:var(--su2)}
.spot-dx{font-family:'Share Tech Mono',monospace;font-size:13px;color:var(--ac3);font-weight:600}
.spot-freq{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--ac4)}
.spot-info{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cluster-footer{padding:6px 10px;border-top:1px solid var(--br2);font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);display:flex;justify-content:space-between;align-items:center;flex-shrink:0}
.cluster-btn-open{position:fixed;right:0;top:50%;transform:translateY(-50%);background:var(--su);border:1px solid var(--br2);border-right:none;border-radius:6px 0 0 6px;padding:10px 6px;cursor:pointer;z-index:299;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--txd);transition:all .15s;writing-mode:vertical-rl;letter-spacing:1px}
.cluster-btn-open:hover{color:var(--ac);border-color:var(--ac)}
.cluster-filters{padding:6px 10px;border-bottom:1px solid var(--br);display:flex;gap:6px;flex-shrink:0}
.cluster-sel{background:var(--bg);border:1px solid var(--br2);color:var(--txd);font-family:'Share Tech Mono',monospace;font-size:10px;padding:3px 6px;border-radius:3px;outline:none;-webkit-appearance:none;flex:1}
/* MODAL */
.ov{display:none;position:fixed;inset:0;background:var(--ov);z-index:500;align-items:center;justify-content:center;padding:20px}
.ov.open{display:flex}
.modal{background:var(--mbg);border:1px solid var(--mbr);border-radius:12px;max-width:480px;width:100%;max-height:80vh;overflow-y:auto;padding:28px;position:relative;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.mc{position:absolute;top:14px;right:14px;background:transparent;border:1px solid var(--br2);color:var(--txm);width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;transition:all .15s}
.mc:hover{color:var(--tx);border-color:var(--txd)}
.mt{font-family:'Barlow Condensed',sans-serif;font-size:26px;font-weight:700;color:var(--ac);letter-spacing:2px;margin-bottom:2px}
.mv{font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--txm);letter-spacing:1px;margin-bottom:10px}
.mm{font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--txd);margin-bottom:16px}
.md{font-size:14px;color:var(--txd);line-height:1.6;margin-bottom:12px}
.mf{font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--txd);padding-left:16px;margin-bottom:20px}
.mf li{margin-bottom:4px}
.ma{display:flex;flex-direction:column;gap:8px}
.mb{display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;border-radius:6px;font-family:'Barlow',sans-serif;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;border:none}
.mb.p{background:var(--ac);color:var(--bg)}.mb.p:hover{filter:brightness(1.1)}
.mb.s{background:transparent;border:1px solid var(--br2);color:var(--txd)}.mb.s:hover{border-color:var(--ac);color:var(--ac)}
.mdiv{border:none;border-top:1px solid var(--br2);margin:14px 0}
.cle{margin-bottom:16px}.clv{font-family:'Share Tech Mono',monospace;font-size:13px;color:var(--ac);font-weight:600;letter-spacing:1px;margin-bottom:3px}
.cld{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);margin-bottom:5px}
.cll{font-size:13px;color:var(--txd);padding-left:14px}.cll li{margin-bottom:3px}
/* PANEL (account & usermgmt) */
.panel-overlay{display:none;position:fixed;inset:0;background:var(--ov);z-index:400;flex-direction:column;overflow-y:auto}
.panel-overlay.open{display:flex}
.panel-wrap{background:var(--mbg);border-left:1px solid var(--mbr);width:100%;max-width:860px;margin-left:auto;min-height:100%;display:flex;flex-direction:column}
.panel-head{background:var(--su);border-bottom:1px solid var(--br2);padding:14px 20px;display:flex;align-items:center;gap:12px;flex-shrink:0}
.panel-title{font-family:'Barlow Condensed',sans-serif;font-size:22px;font-weight:700;color:var(--ac);letter-spacing:1px}
.panel-close{margin-left:auto;background:transparent;border:1px solid var(--br2);color:var(--txm);width:32px;height:32px;border-radius:6px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;transition:all .15s}
.panel-close:hover{color:var(--red);border-color:var(--red)}
.panel-body{padding:20px;flex:1}
.panel-tabs{display:flex;border-bottom:1px solid var(--br2);margin-bottom:20px}
.ptab{background:transparent;border:none;font-family:'Barlow Condensed',sans-serif;font-size:15px;font-weight:600;letter-spacing:1px;color:var(--txm);padding:9px 20px;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s}
.ptab.active{color:var(--ac);border-bottom-color:var(--ac)}
.ptab-pane{display:none}.ptab-pane.active{display:block}
.prow{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.prow.full{grid-template-columns:1fr}
.pfield{display:flex;flex-direction:column;gap:4px}
.pfield label{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);letter-spacing:1px;text-transform:uppercase}
.pfield input,.pfield select{background:var(--bg);border:1px solid var(--br2);color:var(--tx);font-family:'Share Tech Mono',monospace;font-size:13px;padding:8px 10px;border-radius:4px;outline:none;transition:border-color .15s;-webkit-appearance:none}
.pfield input:focus{border-color:var(--acd)}.pfield input::placeholder{color:var(--txm)}.pfield input[readonly]{color:var(--txd);cursor:default}
.psec-title{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);letter-spacing:2px;text-transform:uppercase;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--br)}
.panel-msg{font-family:'Share Tech Mono',monospace;font-size:12px;padding:8px 12px;border-radius:4px;margin-bottom:12px;display:none}
.panel-msg.ok{background:color-mix(in srgb,var(--ac) 10%,transparent);border:1px solid color-mix(in srgb,var(--ac) 30%,transparent);color:var(--ac)}
.panel-msg.err{background:rgba(255,68,68,.1);border:1px solid rgba(255,68,68,.4);color:var(--red)}
.panel-msg.show{display:block}
.pbtn{background:var(--ac);color:var(--bg);border:none;font-family:'Barlow Condensed',sans-serif;font-size:15px;font-weight:700;letter-spacing:1px;padding:9px 22px;border-radius:4px;cursor:pointer;transition:all .15s}
.pbtn:hover{filter:brightness(1.08)}.pbtn.danger{background:var(--red);color:#fff}
.pbtn.ghost{background:transparent;border:1px solid var(--br2);color:var(--txd);font-family:'Share Tech Mono',monospace;font-size:12px}
.pbtn.ghost:hover{border-color:var(--txd);color:var(--tx)}
.pbtn.sm{font-size:11px;padding:4px 10px;font-family:'Share Tech Mono',monospace;font-weight:400;letter-spacing:.5px}
.sess-list{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.sess-item{background:var(--su2);border:1px solid var(--br);border-radius:4px;padding:8px 12px;display:flex;align-items:center;gap:10px;font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txd)}
.sess-item .badge-cur{background:color-mix(in srgb,var(--ac) 12%,transparent);color:var(--ac);border:1px solid color-mix(in srgb,var(--ac) 25%,transparent);font-size:9px;padding:2px 6px;border-radius:3px}
.sess-item .revoke{margin-left:auto;cursor:pointer;color:var(--red);font-size:10px;opacity:.7}
.sess-item .revoke:hover{opacity:1}
.danger-zone{background:rgba(255,68,68,.04);border:1px solid rgba(255,68,68,.2);border-radius:6px;padding:16px;margin-top:4px}
.danger-zone h4{color:var(--red);font-family:'Barlow Condensed',sans-serif;font-size:16px;letter-spacing:1px;margin-bottom:6px}
.danger-zone p{font-size:13px;color:var(--txd);line-height:1.5;margin-bottom:14px}
.radio-group{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.radio-opt{display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:13px;color:var(--txd);line-height:1.4}
.radio-opt input[type=radio]{margin-top:3px;accent-color:var(--ac);flex-shrink:0}
.radio-opt span strong{display:block;font-size:12px;color:var(--tx)}
/* UserMgmt table */
.um-toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;align-items:center}
.um-search{flex:1;min-width:180px;background:var(--bg);border:1px solid var(--br2);color:var(--tx);font-family:'Share Tech Mono',monospace;font-size:12px;padding:7px 10px;border-radius:4px;outline:none}
.um-search:focus{border-color:var(--acd)}
.um-filter{background:var(--bg);border:1px solid var(--br2);color:var(--txd);font-family:'Share Tech Mono',monospace;font-size:11px;padding:7px 10px;border-radius:4px;outline:none;-webkit-appearance:none;cursor:pointer}
.um-table-wrap{overflow-x:auto;margin-bottom:14px}
.um-table{width:100%;border-collapse:collapse;font-family:'Share Tech Mono',monospace;font-size:12px;min-width:600px}
.um-table thead tr{background:var(--su2);border-bottom:1px solid var(--br2)}
.um-table th{padding:8px 10px;text-align:left;font-size:10px;color:var(--txm);letter-spacing:1.5px;text-transform:uppercase;white-space:nowrap}
.um-table tbody tr{border-bottom:1px solid var(--br);transition:background .1s}
.um-table tbody tr:hover{background:var(--su)}.um-table td{padding:7px 10px;color:var(--txd);vertical-align:middle;white-space:nowrap}
.um-table td.call{color:var(--ac3);font-weight:600}.um-table td.email{color:var(--tx);font-size:11px}
.bpill{font-size:9px;padding:2px 6px;border-radius:3px;font-weight:600;letter-spacing:.5px}
.bp-ok{background:color-mix(in srgb,var(--ac) 12%,transparent);color:var(--ac);border:1px solid color-mix(in srgb,var(--ac) 25%,transparent)}
.bp-no{background:rgba(255,68,68,.1);color:var(--red);border:1px solid rgba(255,68,68,.25)}
.bp-warn{background:rgba(255,170,0,.1);color:var(--ac2);border:1px solid rgba(255,170,0,.25)}
.pag-btn{background:transparent;border:1px solid var(--br2);color:var(--txd);font-family:'Share Tech Mono',monospace;font-size:11px;padding:4px 10px;border-radius:3px;cursor:pointer;transition:all .15s}
.pag-btn:hover{border-color:var(--ac);color:var(--ac)}.pag-btn:disabled{opacity:.4;cursor:default}
.um-pagination{display:flex;align-items:center;gap:8px;font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm)}
.create-drawer{display:none;background:var(--su2);border:1px solid var(--br2);border-radius:6px;padding:16px;margin-bottom:14px}
.create-drawer.open{display:block}
/* Tools modal */
.tools-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.tool-card{background:var(--su2);border:1px solid var(--br2);border-radius:6px;padding:14px;cursor:pointer;transition:all .15s;text-align:center}
.tool-card:hover{border-color:var(--ac);background:color-mix(in srgb,var(--ac) 5%,var(--su2))}
.tool-card .tool-icon{font-size:24px;margin-bottom:6px}
.tool-card .tool-name{font-family:'Barlow Condensed',sans-serif;font-size:14px;font-weight:600;color:var(--tx);letter-spacing:.5px}
.tool-card .tool-desc{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);margin-top:3px}
.adif-dropzone{border:2px dashed var(--br2);border-radius:8px;padding:24px;text-align:center;cursor:pointer;transition:all .2s;font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--txm)}
.adif-dropzone:hover,.adif-dropzone.drag-over{border-color:var(--ac);color:var(--ac);background:color-mix(in srgb,var(--ac) 5%,transparent)}
.adif-dropzone .drop-icon{font-size:32px;display:block;margin-bottom:8px;opacity:.5}
.adif-result{font-family:'Share Tech Mono',monospace;font-size:12px;padding:10px 12px;border-radius:4px;margin-top:10px;display:none}
.adif-result.ok{background:color-mix(in srgb,var(--ac) 10%,transparent);border:1px solid color-mix(in srgb,var(--ac) 30%,transparent);color:var(--ac)}
.adif-result.err{background:rgba(255,68,68,.1);border:1px solid rgba(255,68,68,.4);color:var(--red)}
.cabrillo-form{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px}
.cab-sel{background:var(--bg);border:1px solid var(--br2);color:var(--tx);font-family:'Share Tech Mono',monospace;font-size:12px;padding:7px 10px;border-radius:4px;outline:none;-webkit-appearance:none;width:100%}
.dxcc-map-wrap{position:relative;background:color-mix(in srgb,var(--ac3) 5%,var(--bg));border:1px solid var(--br2);border-radius:6px;overflow:hidden;margin-bottom:12px}
.dxcc-map-wrap canvas{display:block;width:100%;height:auto}
.map-legend{display:flex;gap:16px;flex-wrap:wrap;font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm);margin-bottom:8px}
.map-legend span{display:flex;align-items:center;gap:5px}
.map-dot{width:8px;height:8px;border-radius:50%;display:inline-block}
.stats-chart-wrap{margin-top:16px;position:relative;height:180px}
.stats-tabs{display:flex;border-bottom:1px solid var(--br2);margin-bottom:14px}
.stab{background:transparent;border:none;font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:1px;color:var(--txm);padding:7px 14px;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s;text-transform:uppercase}
.stab.active{color:var(--ac);border-bottom-color:var(--ac)}
.notif-status{font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm);display:flex;align-items:center;gap:6px;margin-bottom:10px}
.notif-badge{font-size:9px;padding:2px 6px;border-radius:3px;font-weight:600;letter-spacing:.5px}
.notif-on{background:color-mix(in srgb,var(--ac) 12%,transparent);color:var(--ac);border:1px solid color-mix(in srgb,var(--ac) 25%,transparent)}
.notif-off{background:rgba(255,68,68,.1);color:var(--red);border:1px solid rgba(255,68,68,.25)}
.backup-opts{display:flex;flex-direction:column;gap:6px;margin-bottom:12px}
.backup-opt{display:flex;align-items:center;gap:8px;font-family:'Share Tech Mono',monospace;font-size:12px;color:var(--txd);cursor:pointer}
.backup-opt input{accent-color:var(--ac)}
/* FOOTER */
.footer{background:var(--su);border-top:1px solid var(--br2);padding:10px 16px;text-align:center;font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm);flex-shrink:0}
.footer a{color:var(--txd)}.footer a:hover{color:var(--ac)}
/* TOAST */
.toast{position:fixed;bottom:20px;right:20px;background:var(--ac);color:var(--bg);font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:14px;letter-spacing:1px;padding:10px 20px;border-radius:4px;opacity:0;transform:translateY(10px);transition:all .2s;pointer-events:none;z-index:999}
.toast.show{opacity:1;transform:translateY(0)}.toast.err{background:var(--red);color:#fff}
@media(max-width:900px){.form-grid{grid-template-columns:repeat(6,1fr)}.field.fc{grid-column:span 3}.field.fd{grid-column:span 2}.field.ft{grid-column:span 2}.header{grid-template-columns:auto 1fr auto}.header-center{justify-self:center}.cluster-panel{width:90vw}.tools-grid{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.form-grid{grid-template-columns:1fr 1fr}.field.fc{grid-column:1/-1}.header{grid-template-columns:1fr auto}.header-center{display:none}.tog-opts{margin-left:0}.panel-wrap{max-width:100%}.tools-grid{grid-template-columns:1fr}.cabrillo-form{grid-template-columns:1fr}.prow{grid-template-columns:1fr}}
</style>
</head>
<body data-theme="radio">
<!-- ══ AUTH ══ -->
<div id="auth-wrap" class="auth-wrap">
 <div class="auth-card">
  <div class="auth-logo">EasyQSO</div>
  <div class="auth-sub">DX CONTEST LOGGER v<?= APP_VERSION ?></div>
  <div class="auth-tabs">
   <button class="auth-tab active" onclick="authTab('login')">Accedi</button>
   <button class="auth-tab" onclick="authTab('register')">Registrati</button>
   <button class="auth-tab" onclick="authTab('reset')">Password</button>
  </div>
  <div class="auth-screen active" id="s-login">
   <div class="auth-msg" id="msg-login"></div>
   <div class="fl"><label>Email</label><input type="email" id="l-email" placeholder="tu@email.com"></div>
   <div class="fl"><label>Password</label><input type="password" id="l-pass" placeholder="••••••••" onkeydown="if(event.key==='Enter')doLogin()"></div>
   <div class="forgot-link" onclick="authTab('reset')">Password dimenticata?</div>
   <button class="btn-primary" onclick="doLogin()">ACCEDI</button>
  </div>
  <div class="auth-screen" id="s-register">
   <div class="auth-msg" id="msg-reg"></div>
   <div class="fl"><label>Nominativo</label><input type="text" id="r-call" placeholder="IK1ABC" style="text-transform:uppercase"></div>
   <div class="fl"><label>Email</label><input type="email" id="r-email" placeholder="tu@email.com"></div>
   <div class="fl"><label>Password</label><input type="password" id="r-pass" placeholder="Minimo 8 caratteri"></div>
   <div class="fl"><label>Conferma password</label><input type="password" id="r-pass2" placeholder="Ripeti la password" onkeydown="if(event.key==='Enter')doRegister()"></div>
   <button class="btn-primary" onclick="doRegister()">REGISTRATI</button>
  </div>
  <div class="auth-screen" id="s-reset">
   <div class="auth-msg" id="msg-reset"></div>
   <div class="fl"><label>La tua email</label><input type="email" id="rs-email" placeholder="tu@email.com" onkeydown="if(event.key==='Enter')doReset()"></div>
   <button class="btn-primary" onclick="doReset()">INVIA LINK RESET</button>
  </div>
 </div>
</div>

<!-- ══ APP ══ -->
<div id="app-wrap" style="display:none;flex-direction:column;flex:1">

<!-- HEADER -->
<div class="header" style="position:relative">
 <div class="header-left">
  <div><div class="logo">EasyQSO <span>DX CONTEST LOGGER v<?= APP_VERSION ?></span></div></div>
  <div class="stats-row">
   <div class="stat-pill"><strong id="tot-qso">0</strong>QSO</div>
   <div class="stat-pill"><strong id="tot-dxcc">0</strong>DXCC</div>
   <div class="stat-pill"><strong id="tot-pts">0</strong>PUNTI</div>
  </div>
 </div>
 <div class="header-center"><div class="utc-label">UTC</div><div class="utc-clock" id="clock">--:--:--</div></div>
 <div class="header-right">
  <span class="user-badge" id="user-badge">—</span>
  <button class="burger-btn" id="burger-btn" onclick="toggleBurger()"><span></span><span></span><span></span></button>
  <div class="burger-menu" id="burger-menu">
   <div class="menu-sep">Tema</div>
   <button class="theme-btn active" data-theme="radio" onclick="setTheme('radio',this)"><span class="tdot tdot-r"></span>📻 RADIO</button>
   <button class="theme-btn" data-theme="dark" onclick="setTheme('dark',this)"><span class="tdot tdot-d"></span>🌑 DARK</button>
   <button class="theme-btn" data-theme="light" onclick="setTheme('light',this)"><span class="tdot tdot-l"></span>☀️ LIGHT</button>
   <div class="menu-sep">Account</div>
   <a class="menu-btn" href="account.php" style="text-decoration:none"><span>👤</span> Il mio account</a>
   <div id="menu-users-item" style="display:none"><button class="menu-btn" onclick="openUserMgmt()"><span>👥</span> Gestione Utenti</button></div>
   <div class="menu-sep">Strumenti</div>
   <button class="menu-btn" onclick="openTools('stats')"><span>📊</span> Statistiche</button>
   <button class="menu-btn" onclick="openTools('map')"><span>🗺️</span> Mappa DXCC</button>
   <button class="menu-btn" onclick="openTools('import')"><span>📥</span> Importa ADIF</button>
   <button class="menu-btn" onclick="openTools()"><span>🛠️</span> Tutti gli strumenti</button>
   <div class="menu-sep">Info</div>
   <button class="menu-btn" onclick="openOv('ov-about');closeBurger()"><span>ℹ️</span> About</button>
   <button class="menu-btn" onclick="openOv('ov-changelog');closeBurger()"><span>📋</span> Changelog</button>
   <button class="menu-btn" onclick="reportBug();closeBurger()"><span>🐛</span> Segnala bug</button>
   <button class="menu-btn" onclick="doLogout();closeBurger()" style="color:var(--red)"><span>🚪</span> Esci</button>
  </div>
 </div>
</div>

<!-- CONTEST BAR -->
<div class="contest-bar" id="contest-bar">
 <div><span class="clabel">Elapsed</span><div class="contest-timer" id="contest-timer">00:00:00</div></div>
 <div><span class="clabel">Punti</span><div class="contest-score" id="contest-pts">0</div></div>
 <div><span class="clabel">Molt. DXCC</span><div class="contest-score" id="contest-mults" style="color:var(--ac4)">0</div></div>
 <div><span class="clabel">Score totale</span><div class="contest-score" id="contest-total" style="color:var(--ac2)">0</div></div>
 <div style="margin-left:auto;display:flex;gap:8px">
  <button class="pbtn ghost sm" onclick="resetContest()" style="color:var(--red)">↺ Reset</button>
  <button class="pbtn ghost sm" onclick="toggleContest()">✕</button>
 </div>
</div>

<!-- FORM -->
<div class="form-panel">
 <div class="form-grid">
  <div class="field fc">
   <label>Nominativo</label>
   <div style="display:flex;align-items:center;gap:4px">
    <input type="text" id="f-call" placeholder="IK1ABC" autocomplete="off" maxlength="12" style="flex:1">
    <button class="qrz-btn" onclick="qrzLookup()" title="Cerca su QRZ.com">🔍</button>
   </div>
   <span class="dxcc-badge" id="dxcc-badge"></span>
  </div>
  <div class="field fd"><label>Data UTC</label><input type="date" id="f-date"></div>
  <div class="field ft"><label>Ora UTC</label><input type="time" id="f-time"></div>
  <div class="field ff"><label>Freq (MHz)</label><input type="text" id="f-freq" placeholder="14.200" inputmode="decimal" maxlength="10"></div>
  <div class="field fb"><label>Banda</label>
   <select id="f-band">
    <option value="160m">160m</option><option value="80m">80m</option><option value="40m">40m</option>
    <option value="30m">30m</option><option value="20m" selected>20m</option><option value="17m">17m</option>
    <option value="15m">15m</option><option value="12m">12m</option><option value="10m">10m</option>
    <option value="6m">6m</option><option value="2m">2m</option><option value="70cm">70cm</option>
   </select>
  </div>
  <div class="field"><label>Modo</label>
   <select id="f-mode"><option>SSB</option><option>CW</option><option>FT8</option><option>FT4</option><option>AM</option><option>FM</option><option>DIGI</option><option>RTTY</option></select>
  </div>
  <div class="field fr"><label>RST TX</label><input type="text" id="f-rst-s" maxlength="3" value="59"></div>
  <div class="field fr"><label>RST RX</label><input type="text" id="f-rst-r" maxlength="3" value="59"></div>
  <div class="field"><label>Nr. TX</label><input type="text" id="f-ser-s" placeholder="001" maxlength="6"></div>
  <div class="field"><label>Nr. RX</label><input type="text" id="f-ser-r" placeholder="001" maxlength="6"></div>
  <div class="field"><label>DXCC / Pfx</label><input type="text" id="f-dxcc" placeholder="I, DL…" maxlength="10"></div>
  <div class="field"><label>Note</label><input type="text" id="f-note" placeholder="Op, QTH…" maxlength="40"></div>
 </div>
 <div class="btn-row">
  <button class="btn-log" onclick="logQSO()">+ LOG QSO</button>
  <button class="btn-sec" onclick="clearForm()">RESET</button>
  <button class="contest-toggle" id="contest-toggle-btn" onclick="toggleContest()">🏆 CONTEST</button>
  <div class="tog-opts">
   <label class="tog-item">
    <div class="tog-sw"><input type="checkbox" id="opt-at" checked><div class="tog-track"><div class="tog-thumb"></div></div></div>
    <span class="tog-lbl">Ora auto<strong>aggiorna UTC</strong></span>
   </label>
   <label class="tog-item">
    <div class="tog-sw"><input type="checkbox" id="opt-kf"><div class="tog-track"><div class="tog-thumb"></div></div></div>
    <span class="tog-lbl">Freq fissa<strong>mantieni ultima</strong></span>
   </label>
  </div>
  <span class="band-hint" id="band-hint"></span>
 </div>
 <div class="dupe-alert" id="dupe-alert">⚠ DUPE! Nominativo già presente nel log.</div>
</div>

<!-- TOOLBAR -->
<div class="toolbar">
 <span class="tlbl">FILTRO:</span>
 <input class="fi" id="filter-call" placeholder="Nominativo…" oninput="renderLog()">
 <div class="bpills">
  <button class="bp active" data-band="ALL" onclick="setBand(this)">ALL</button>
  <button class="bp" data-band="160m" onclick="setBand(this)">160m</button>
  <button class="bp" data-band="80m" onclick="setBand(this)">80m</button>
  <button class="bp" data-band="40m" onclick="setBand(this)">40m</button>
  <button class="bp" data-band="30m" onclick="setBand(this)">30m</button>
  <button class="bp" data-band="20m" onclick="setBand(this)">20m</button>
  <button class="bp" data-band="17m" onclick="setBand(this)">17m</button>
  <button class="bp" data-band="15m" onclick="setBand(this)">15m</button>
  <button class="bp" data-band="12m" onclick="setBand(this)">12m</button>
  <button class="bp" data-band="10m" onclick="setBand(this)">10m</button>
  <button class="bp" data-band="6m" onclick="setBand(this)">6m</button>
 </div>
 <button class="btn-exp" onclick="exportADIF()">⬇ ADIF</button>
 <button class="btn-exp" onclick="exportCSV()">⬇ CSV</button>
</div>

<!-- LOG TABLE -->
<div class="log-wrap">
 <table>
  <thead><tr>
   <th onclick="sortBy('id')">#</th><th onclick="sortBy('callsign')">Nominativo</th>
   <th onclick="sortBy('qso_date')">Data</th><th onclick="sortBy('qso_time')">Ora UTC</th>
   <th onclick="sortBy('freq')">Freq</th><th onclick="sortBy('band')">Banda</th>
   <th onclick="sortBy('mode')">Modo</th><th onclick="sortBy('rst_sent')">RST TX</th>
   <th onclick="sortBy('rst_rcvd')">RST RX</th><th>Nr TX/RX</th>
   <th onclick="sortBy('dxcc')">DXCC</th><th>Note</th><th style="width:80px"></th>
  </tr></thead>
  <tbody id="log-body"></tbody>
 </table>
 <div class="empty" id="empty-state"><div>📻</div>Nessun QSO registrato.<br>Inserisci il primo contatto sopra.</div>
</div>

<!-- DX CLUSTER TAB -->
<button class="cluster-btn-open" id="cluster-open-btn" onclick="toggleCluster()">📡 DX CLUSTER</button>
<div class="cluster-panel" id="cluster-panel">
 <div class="cluster-head">
  <span class="cluster-dot" id="cluster-dot"></span>
  <span class="cluster-title">DX Cluster</span>
  <button onclick="toggleCluster()" style="background:transparent;border:none;color:var(--txm);cursor:pointer;font-size:16px">✕</button>
 </div>
 <div class="cluster-filters">
  <select class="cluster-sel" id="cl-band" onchange="fetchCluster()">
   <option value="">Tutte le bande</option>
   <option value="160m">160m</option><option value="80m">80m</option><option value="40m">40m</option>
   <option value="30m">30m</option><option value="20m">20m</option><option value="17m">17m</option><option value="15m">15m</option>
   <option value="12m">12m</option><option value="10m">10m</option><option value="6m">6m</option>
  </select>
  <select class="cluster-sel" id="cl-mode" onchange="fetchCluster()">
   <option value="">Tutti i modi</option>
   <option value="SSB">SSB</option><option value="CW">CW</option><option value="FT8">FT8</option><option value="FT4">FT4</option>
  </select>
 </div>
 <div class="cluster-list" id="cluster-list"><div style="padding:20px;text-align:center;font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm)">Caricamento…</div></div>
 <div class="cluster-footer"><span id="cluster-ts">—</span><button onclick="fetchCluster()" style="background:transparent;border:none;color:var(--txd);cursor:pointer;font-family:'Share Tech Mono',monospace;font-size:10px">↻</button></div>
</div>

<!-- ══ PANEL: ACCOUNT ══ -->
<div class="panel-overlay" id="panel-account">
 <div class="panel-wrap">
  <div class="panel-head"><span style="font-size:20px">👤</span><div class="panel-title">Account</div><button class="panel-close" onclick="closePanel('panel-account')">✕</button></div>
  <div class="panel-body">
   <div class="panel-tabs">
    <button class="ptab active" onclick="switchPTab('acc',0,this)">Profilo</button>
    <button class="ptab" onclick="switchPTab('acc',1,this)">Sicurezza</button>
    <button class="ptab" onclick="switchPTab('acc',2,this)">Elimina Account</button>
   </div>
   <div class="ptab-pane active" id="acc-pane-0">
    <div class="panel-msg" id="acc-msg-0"></div>
    <div class="prow"><div class="pfield"><label>Nominativo</label><input type="text" id="acc-callsign" maxlength="12"><div style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--txm);margin-top:2px">2–12 caratteri alfanumerici</div></div><div class="pfield"><label>Email</label><input type="text" id="acc-email" readonly></div></div>
    <div class="prow"><div class="pfield"><label>Membro dal</label><input type="text" id="acc-created" readonly></div><div class="pfield"><label>Ultimo accesso</label><input type="text" id="acc-lastlogin" readonly></div></div>
    <div class="prow"><div class="pfield"><label>QSO registrati</label><input type="text" id="acc-qso-count" readonly></div></div>
    <button class="pbtn" onclick="accSaveCallsign()">SALVA NOMINATIVO</button>
   </div>
   <div class="ptab-pane" id="acc-pane-1">
    <div class="panel-msg" id="acc-msg-1"></div>
    <div class="psec-title">Cambia password</div>
    <div class="prow full"><div class="pfield"><label>Password attuale</label><input type="password" id="acc-old-pw" placeholder="Password corrente"></div></div>
    <div class="prow"><div class="pfield"><label>Nuova password</label><input type="password" id="acc-new-pw" placeholder="Min 8 car., 1 maiusc., 1 numero"></div><div class="pfield"><label>Conferma</label><input type="password" id="acc-new-pw2" placeholder="Ripeti"></div></div>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px"><input type="checkbox" id="acc-logout-others" style="accent-color:var(--ac)"><label for="acc-logout-others" style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txd);cursor:pointer">Disconnetti le altre sessioni attive</label></div>
    <button class="pbtn" onclick="accChangePassword()" style="margin-bottom:24px">AGGIORNA PASSWORD</button>
    <div class="psec-title">Sessioni attive</div>
    <div class="sess-list" id="sess-list"><div style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm)">Caricamento…</div></div>
    <button class="pbtn ghost sm" onclick="loadSessions()">↻ Aggiorna</button>
   </div>
   <div class="ptab-pane" id="acc-pane-2">
    <div class="panel-msg" id="acc-msg-2"></div>
    <div class="danger-zone">
     <h4>⚠️ Zona Pericolo</h4>
     <p>L'eliminazione è <strong>irreversibile</strong>. Riceverai un'email con link valido 24 ore.</p>
     <div class="psec-title" style="margin-top:4px">Cosa fare con i tuoi QSO?</div>
     <div class="radio-group">
      <label class="radio-opt"><input type="radio" name="qso-action" value="anonymize" checked><span><strong>Anonimizza i QSO</strong>Nominativo → "DELETED"</span></label>
      <label class="radio-opt"><input type="radio" name="qso-action" value="delete"><span><strong>Elimina tutti i QSO</strong>Cancellazione definitiva</span></label>
     </div>
     <button class="pbtn danger" onclick="accRequestDelete()">RICHIEDI ELIMINAZIONE ACCOUNT</button>
    </div>
   </div>
  </div>
 </div>
</div>

<!-- ══ PANEL: GESTIONE UTENTI ══ -->
<div class="panel-overlay" id="panel-users">
 <div class="panel-wrap" style="max-width:100%">
  <div class="panel-head"><span style="font-size:20px">👥</span><div class="panel-title">Gestione Utenti</div><button class="panel-close" onclick="closePanel('panel-users')">✕</button></div>
  <div class="panel-body">
   <div class="panel-tabs">
    <button class="ptab active" onclick="switchPTab('um',0,this)">Utenti</button>
    <button class="ptab" onclick="switchPTab('um',1,this);loadAudit()">Audit Log</button>
   </div>
   <div class="ptab-pane active" id="um-pane-0">
    <div class="panel-msg" id="um-msg"></div>
    <div class="um-toolbar">
     <input class="um-search" id="um-search" placeholder="Cerca nominativo o email…" oninput="debounceLoadUsers()">
     <select class="um-filter" id="um-filter" onchange="loadUsers()"><option value="all">Tutti</option><option value="active">Attivi</option><option value="suspended">Sospesi</option><option value="unverified">Non verificati</option></select>
     <button class="pbtn sm" onclick="toggleCreateDrawer()" style="white-space:nowrap">+ Nuovo utente</button>
    </div>
    <div class="create-drawer" id="create-drawer">
     <div class="psec-title">Crea nuovo utente</div>
     <div class="prow"><div class="pfield"><label>Nominativo</label><input type="text" id="cu-call" maxlength="12" placeholder="IK1XYZ"></div><div class="pfield"><label>Email</label><input type="email" id="cu-email" placeholder="user@email.com"></div></div>
     <div class="prow"><div class="pfield"><label>Password</label><input type="password" id="cu-pass" placeholder="Min 8 caratteri"></div><div class="pfield" style="justify-content:flex-end;padding-bottom:4px"><label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txd);margin-top:auto"><input type="checkbox" id="cu-admin" style="accent-color:var(--ac)"> Ruolo Admin</label></div></div>
     <button class="pbtn sm" onclick="createUser()" style="margin-right:6px">CREA</button>
     <button class="pbtn ghost sm" onclick="toggleCreateDrawer()">Annulla</button>
    </div>
    <div class="um-table-wrap"><table class="um-table"><thead><tr><th>ID</th><th>Nominativo</th><th>Email</th><th>Verif.</th><th>Admin</th><th>Stato</th><th>QSO</th><th>Registrato</th><th>Ultimo acc.</th><th></th></tr></thead><tbody id="um-tbody"><tr><td colspan="10" style="text-align:center;padding:24px;color:var(--txm)">Caricamento…</td></tr></tbody></table></div>
    <div class="um-pagination"><button class="pag-btn" id="um-prev" onclick="umPage(-1)">← Prec</button><span id="um-page-info">—</span><button class="pag-btn" id="um-next" onclick="umPage(+1)">Succ →</button></div>
   </div>
   <div class="ptab-pane" id="um-pane-1">
    <div class="um-table-wrap"><table class="um-table"><thead><tr><th>ID</th><th>Admin</th><th>Azione</th><th>Target</th><th>IP</th><th>Data/Ora</th></tr></thead><tbody id="audit-tbody"><tr><td colspan="6" style="text-align:center;padding:24px;color:var(--txm)">Caricamento…</td></tr></tbody></table></div>
    <div class="um-pagination"><button class="pag-btn" id="aud-prev" onclick="audPage(-1)">← Prec</button><span id="aud-page-info">—</span><button class="pag-btn" id="aud-next" onclick="audPage(+1)">Succ →</button></div>
   </div>
  </div>
 </div>
</div>

<!-- ══ MODAL: TOOLS ══ -->
<div class="ov" id="ov-tools" onclick="ovClick(event,'ov-tools')">
 <div class="modal" style="max-width:520px">
  <button class="mc" onclick="closeOv('ov-tools')">✕</button>
  <div class="mt" style="font-size:22px;margin-bottom:16px">🛠️ Strumenti</div>
  <div class="tools-grid">
   <div class="tool-card" onclick="showTool('import')"><div class="tool-icon">📥</div><div class="tool-name">Importa ADIF</div><div class="tool-desc">Carica un log esistente</div></div>
   <div class="tool-card" onclick="showTool('cabrillo')"><div class="tool-icon">🏆</div><div class="tool-name">Export Cabrillo</div><div class="tool-desc">Formato per contest</div></div>
   <div class="tool-card" onclick="showTool('map')"><div class="tool-icon">🗺️</div><div class="tool-name">Mappa DXCC</div><div class="tool-desc">Entità lavorate</div></div>
   <div class="tool-card" onclick="showTool('stats')"><div class="tool-icon">📊</div><div class="tool-name">Statistiche</div><div class="tool-desc">Grafici e analisi</div></div>
   <div class="tool-card" onclick="showTool('backup')"><div class="tool-icon">💾</div><div class="tool-name">Backup automatico</div><div class="tool-desc">Salvataggio periodico</div></div>
   <div class="tool-card" onclick="showTool('notif')"><div class="tool-icon">🔔</div><div class="tool-name">Notifiche</div><div class="tool-desc">Alert QSO loggato</div></div>
  </div>
  <div id="tool-import" style="display:none">
   <div class="psec-title">📥 Importa ADIF</div>
   <div class="adif-dropzone" id="adif-zone" onclick="document.getElementById('adif-file').click()">
    <span class="drop-icon">📁</span>Trascina il file .adi/.adif o clicca per selezionarlo
    <input type="file" id="adif-file" accept=".adi,.adif,.log" style="display:none" onchange="handleAdifFile(this)">
   </div>
   <div class="adif-result" id="adif-result"></div>
  </div>
  <div id="tool-cabrillo" style="display:none">
   <div class="psec-title">🏆 Export Cabrillo</div>
   <div class="cabrillo-form">
    <div class="pfield"><label>Contest</label><select class="cab-sel" id="cab-contest"><option value="CQ-WW-SSB">CQ WW SSB</option><option value="CQ-WW-CW">CQ WW CW</option><option value="CQ-WPX-SSB">CQ WPX SSB</option><option value="CQ-WPX-CW">CQ WPX CW</option><option value="ARRL-DX-SSB">ARRL DX SSB</option><option value="ARRL-DX-CW">ARRL DX CW</option><option value="ARI-DX">ARI DX</option><option value="IOTA">IOTA</option></select></div>
    <div class="pfield"><label>Categoria</label><select class="cab-sel" id="cab-cat"><option value="SINGLE-OP">Single Op</option><option value="MULTI-OP">Multi Op</option><option value="CHECKLOG">Checklog</option></select></div>
    <div class="pfield"><label>Potenza</label><select class="cab-sel" id="cab-pwr"><option value="HIGH">High Power</option><option value="LOW">Low Power</option><option value="QRP">QRP</option></select></div>
    <div class="pfield"><label>Banda</label><select class="cab-sel" id="cab-band"><option value="ALL">All Band</option><option value="160M">160m</option><option value="80M">80m</option><option value="40M">40m</option><option value="30M">30m</option><option value="20M">20m</option><option value="17M">17m</option><option value="15M">15m</option><option value="12M">12m</option><option value="10M">10m</option></select></div>
   </div>
   <button class="pbtn" onclick="exportCabrillo()">⬇ SCARICA .CBR</button>
  </div>
  <div id="tool-map" style="display:none">
   <div class="psec-title">🗺️ Mappa DXCC lavorate</div>
   <div class="map-legend"><span><span class="map-dot" style="background:#39ff6a"></span>Lavorata</span><span><span class="map-dot" style="background:#2a4a2a"></span>Non lavorata</span></div>
   <div class="dxcc-map-wrap"><canvas id="dxcc-canvas" width="800" height="400"></canvas></div>
   <div id="map-tooltip" style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--ac);min-height:16px;text-align:center"></div>
  </div>
  <div id="tool-stats" style="display:none">
   <div class="psec-title">📊 Analisi QSO</div>
   <div class="stats-tabs">
    <button class="stab active" onclick="switchStatTab('hour',this)">Per ora</button>
    <button class="stab" onclick="switchStatTab('band',this)">Per banda</button>
    <button class="stab" onclick="switchStatTab('mode',this)">Per modo</button>
    <button class="stab" onclick="switchStatTab('day',this)">Per giorno</button>
   </div>
   <div class="stats-chart-wrap"><canvas id="stats-chart"></canvas></div>
   <div id="stats-summary" style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm);margin-top:10px;text-align:center"></div>
  </div>
  <div id="tool-backup" style="display:none">
   <div class="psec-title">💾 Backup automatico ADIF</div>
   <div class="backup-opts">
    <label class="backup-opt"><input type="radio" name="backup-when" value="qso" checked> Ogni <strong style="color:var(--ac)">10 QSO</strong> loggati</label>
    <label class="backup-opt"><input type="radio" name="backup-when" value="hour"> Ogni <strong style="color:var(--ac)">ora</strong></label>
    <label class="backup-opt"><input type="radio" name="backup-when" value="manual"> Solo manuale</label>
   </div>
   <button class="pbtn" onclick="backupNow()" style="margin-right:8px">⬇ Backup ora</button>
   <button class="pbtn ghost" onclick="toggleAutoBackup()" id="backup-toggle-btn">Attiva backup auto</button>
   <div id="backup-status" style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm);margin-top:10px"></div>
  </div>
  <div id="tool-notif" style="display:none">
   <div class="psec-title">🔔 Notifiche desktop</div>
   <div class="notif-status" id="notif-status"><span>Stato:</span><span class="notif-badge notif-off" id="notif-badge">Non attive</span></div>
   <p style="font-size:13px;color:var(--txd);line-height:1.5;margin-bottom:14px">Ricevi una notifica ogni volta che viene loggato un QSO.</p>
   <button class="pbtn" onclick="requestNotifPermission()" id="notif-btn">Attiva notifiche</button>
   <div id="notif-test-wrap" style="display:none;margin-top:10px"><button class="pbtn ghost" onclick="testNotif()">🔔 Notifica di test</button></div>
  </div>
 </div>
</div>

<!-- ══ MODAL: EDIT USER (admin) ══ -->
<div class="ov" id="ov-edit-user" onclick="ovClick(event,'ov-edit-user')">
 <div class="modal" style="max-width:440px">
  <button class="mc" onclick="closeOv('ov-edit-user')">✕</button>
  <div class="mt" style="font-size:20px;margin-bottom:4px" id="eu-title">Modifica utente</div>
  <div class="mv" id="eu-sub"></div>
  <div class="panel-msg" id="eu-msg" style="margin-top:8px"></div>
  <input type="hidden" id="eu-id">
  <div style="display:flex;flex-direction:column;gap:10px;margin-top:14px">
   <div style="display:flex;align-items:center;justify-content:space-between"><label style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm)">Verificato</label><div style="display:flex;gap:6px"><button class="pbtn sm" id="eu-v1" onclick="euSet('v',1)">✓ Sì</button><button class="pbtn ghost sm" id="eu-v0" onclick="euSet('v',0)">✕ No</button></div></div>
   <div style="display:flex;align-items:center;justify-content:space-between"><label style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm)">Ruolo Admin</label><div style="display:flex;gap:6px"><button class="pbtn sm" id="eu-a1" onclick="euSet('a',1)">✓ Sì</button><button class="pbtn ghost sm" id="eu-a0" onclick="euSet('a',0)">✕ No</button></div></div>
   <div style="display:flex;align-items:center;justify-content:space-between"><label style="font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--txm)">Stato</label><div style="display:flex;gap:6px"><button class="pbtn sm" id="eu-ac1" onclick="euSet('ac',1)">✓ Attivo</button><button class="pbtn ghost sm" id="eu-ac0" onclick="euSet('ac',0)" style="color:var(--red)">✕ Sospendi</button></div></div>
   <div id="eu-reason-wrap" style="display:none"><div class="pfield"><label>Motivo sospensione</label><input type="text" id="eu-reason" placeholder="Opzionale" maxlength="255"></div></div>
  </div>
  <div style="display:flex;gap:8px;margin-top:18px;flex-wrap:wrap">
   <button class="pbtn" onclick="euSave()">SALVA</button>
   <button class="pbtn ghost" onclick="euSendReset()" style="font-size:12px">📧 Reset Password</button>
   <button class="pbtn danger" onclick="euDelete()" style="font-size:12px;margin-left:auto">🗑 Elimina</button>
  </div>
 </div>
</div>

<!-- ══ MODAL: DELETE USER CONFIRM ══ -->
<div class="ov" id="ov-del-user" onclick="ovClick(event,'ov-del-user')">
 <div class="modal" style="max-width:400px">
  <button class="mc" onclick="closeOv('ov-del-user')">✕</button>
  <div class="mt" style="font-size:20px;color:var(--red)">Elimina utente</div>
  <div class="mv" id="du-sub" style="margin-top:4px"></div>
  <div class="panel-msg" id="du-msg" style="margin-top:8px"></div>
  <p style="font-size:13px;color:var(--txd);line-height:1.5;margin:12px 0">Azione irreversibile. Scegli cosa fare con i QSO:</p>
  <div class="radio-group">
   <label class="radio-opt"><input type="radio" name="du-qso" value="anonymize" checked><span><strong>Anonimizza QSO</strong>Nominativo → "DELETED"</span></label>
   <label class="radio-opt"><input type="radio" name="du-qso" value="delete"><span><strong>Elimina QSO</strong>Rimozione definitiva</span></label>
  </div>
  <div style="display:flex;gap:8px;margin-top:16px">
   <button class="pbtn danger" onclick="euConfirmDelete()">CONFERMA ELIMINAZIONE</button>
   <button class="pbtn ghost" onclick="closeOv('ov-del-user')">Annulla</button>
  </div>
 </div>
</div>

<!-- ══ MODAL: ABOUT ══ -->
<div class="ov" id="ov-about" onclick="ovClick(event,'ov-about')">
 <div class="modal">
  <button class="mc" onclick="closeOv('ov-about')">✕</button>
  <div class="mt">EasyQSO</div><div class="mv">v<?= APP_VERSION ?> — DX Contest Logger</div>
  <div class="mm">Made with ❤️ by <a href="https://sputlab.com" target="_blank">sputlab.com</a></div>
  <hr class="mdiv">
  <div class="md">Logbook web per radioamatori con account personale su DB, ottimizzato per i DX contest.</div>
  <ul class="mf">
   <li>📡 Inserimento rapido QSO con Enter</li><li>🎯 DXCC auto-fill dal nominativo</li>
   <li>⏰ Orologio UTC live e aggiornamento ora auto</li><li>🔁 Memoria ultima frequenza</li>
   <li>⚠️ Dupe alert</li><li>✏️ Modifica inline QSO</li><li>👤 Account personale su MySQL</li>
   <li>📊 Statistiche e grafici per ora/banda/modo</li><li>🗺️ Mappa DXCC interattiva</li>
   <li>📡 DX Cluster live con prefill form</li><li>🏆 Modalità contest con score live</li>
   <li>💾 Backup ADIF automatico</li><li>📥 Import ADIF</li><li>⬇ Export ADIF, CSV, Cabrillo</li>
   <li>🔔 Notifiche desktop</li><li>🔍 Lookup QRZ.com one-click</li><li>🎨 3 temi grafici</li>
  </ul>
  <hr class="mdiv">
  <div class="ma">
   <a class="mb p" href="https://paypal.me/sputnikov" target="_blank">❤️ Supporta con PayPal</a>
   <a class="mb s" href="https://www.sputlab.com/tags/software/" target="_blank">🔗 Altre WebApp</a>
   <button class="mb s" onclick="closeOv('ov-about');openOv('ov-changelog')">📋 Changelog</button>
   <button class="mb s" onclick="reportBug();closeOv('ov-about')">🐛 Segnala bug</button>
  </div>
 </div>
</div>

<!-- ══ MODAL: CHANGELOG ══ -->
<div class="ov" id="ov-changelog" onclick="ovClick(event,'ov-changelog')">
 <div class="modal">
  <button class="mc" onclick="closeOv('ov-changelog')">✕</button>
  <div class="mt" style="font-size:22px;margin-bottom:16px">Changelog</div>
  <div class="cle"><div class="clv">v2.1.2 — corrente</div><div class="cld">Novembre 2026</div>
   <ul class="cll"><li>🐛 Corretti i file .htaccess di api/ e includes/ che causavano errore 500 al login</li></ul></div>
  <hr class="mdiv">
  <div class="cle"><div class="clv">v2.1.1</div><div class="cld">Novembre 2026</div>
   <ul class="cll"><li>🛠️ L'API risponde sempre in JSON anche in caso di errore interno</li><li>🗄️ Avviso esplicito se il database non è migrato</li></ul></div>
  <hr class="mdiv">
  <div class="cle"><div class="clv">v2.1.0</div><div class="cld">Novembre 2026</div>
   <ul class="cll"><li>👤 Pagina di autogestione account (nominativo, password, sessioni, eliminazione)</li><li>📻 Banda 30m (e 17m/12m) nei filtri, cluster e Cabrillo</li></ul></div>
  <hr class="mdiv">
  <div class="cle"><div class="clv">v2.0.2</div><div class="cld">Settembre 2026</div>
   <ul class="cll"><li>🎯 DXCC auto-fill dal nominativo (260 prefissi)</li><li>📥 Import ADIF</li><li>🏆 Export Cabrillo per contest</li><li>📊 Grafici QSO per ora/banda/modo/giorno</li><li>📡 DX Cluster live con prefill form</li><li>🗺️ Mappa DXCC interattiva su canvas</li><li>🏆 Modalità Contest con timer e score live</li><li>🔔 Notifiche desktop</li><li>💾 Backup ADIF automatico</li><li>🔍 Lookup QRZ.com one-click</li></ul></div>
  <hr class="mdiv">
  <div class="cle"><div class="clv">v2.0.1</div><div class="cld">Settembre 2026</div>
   <ul class="cll"><li>👤 Autenticazione utenti (email + verifica + reset pw)</li><li>🗄️ Salvataggio QSO su MySQL</li><li>✏️ Modifica inline QSO</li><li>👥 Gestione Utenti per admin con audit log</li><li>🛡️ Rate limiting su login/register/reset</li><li>🔒 Cookie fallback per hosting Nginx</li><li>🎨 3 temi: Radio, Dark, Light</li></ul></div>
  <hr class="mdiv">
  <div class="cle"><div class="clv">v1.01–1.06</div><div class="cld">Aprile 2026</div>
   <ul class="cll"><li>🚀 Prima release — log QSO, DUPE alert, ADIF/CSV, statistiche, filtri, ordinamento</li><li>📡 Rilevamento banda automatico (ITU R1)</li><li>⏰ Toggle ora auto UTC e freq fissa</li></ul></div>
 </div>
</div>

</div><!-- /app-wrap -->

<div class="footer">Made with ❤️ by <a href="https://sputlab.com" target="_blank">sputlab.com</a> &nbsp;·&nbsp; EasyQSO v<?= APP_VERSION ?></div>
<div class="toast" id="toast"></div>
<script>
const API='<?= BASE_URL ?>/api';
const BANDS=[[1.81,2,'160m'],[3.5,3.8,'80m'],[7,7.2,'40m'],[10.1,10.15,'30m'],[14,14.35,'20m'],[18.068,18.168,'17m'],[21,21.45,'15m'],[24.89,24.99,'12m'],[28,29.7,'10m'],[50,54,'6m'],[144,148,'2m'],[430,440,'70cm']];
const MODES=['SSB','CW','FT8','FT4','AM','FM','DIGI','RTTY'];
const BANDS_LIST=['160m','80m','40m','30m','20m','17m','15m','12m','10m','6m','2m','70cm'];
let token=null,currentUser=null,log=[],serial=1,sortCol='id',sortDir=-1,bandFilter='ALL',lastFreq='',editingId=null;
let DXCC={};

// ── Utilities ────────────────────────────────────────────────
function pad(n,l=2){return String(n).padStart(l,'0')}
function nowUTC(){const d=new Date();return{date:d.getUTCFullYear()+'-'+pad(d.getUTCMonth()+1)+'-'+pad(d.getUTCDate()),time:pad(d.getUTCHours())+':'+pad(d.getUTCMinutes())}}
function toast(msg,err=false){const t=document.getElementById('toast');t.textContent=msg;t.className='toast show'+(err?' err':'');setTimeout(()=>t.className='toast',2800)}
function openOv(id){document.getElementById(id).classList.add('open')}
function closeOv(id){document.getElementById(id).classList.remove('open')}
function ovClick(e,id){if(e.target===document.getElementById(id))closeOv(id)}
document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('.ov.open').forEach(o=>o.classList.remove('open'));closeBurger();closePanel('panel-account');closePanel('panel-users')}});
function reportBug(){window.location.href='mailto:bug@sputlab.com?subject=Segnalazione%20bug%20WebApp%20EasyQSO'}

// ── Theme ────────────────────────────────────────────────────
function setTheme(name,btn){
  document.body.setAttribute('data-theme',name);
  document.querySelectorAll('.theme-btn').forEach(b=>b.classList.toggle('active',b.dataset.theme===name));
  try{localStorage.setItem('easyqso_theme',name);}catch(e){}
  if(token)apiFetch('/user/settings',{method:'POST',body:JSON.stringify({theme:name})});
}
(function(){const t=localStorage.getItem('easyqso_theme');if(t){document.body.setAttribute('data-theme',t);document.querySelectorAll('.theme-btn').forEach(b=>b.classList.toggle('active',b.dataset.theme===t));}})();

// ── Burger ───────────────────────────────────────────────────
function toggleBurger(){document.getElementById('burger-btn').classList.toggle('open');document.getElementById('burger-menu').classList.toggle('open')}
function closeBurger(){document.getElementById('burger-btn').classList.remove('open');document.getElementById('burger-menu').classList.remove('open')}
document.addEventListener('click',e=>{const m=document.getElementById('burger-menu');if(m.classList.contains('open')&&!m.contains(e.target)&&!document.getElementById('burger-btn').contains(e.target))closeBurger()});

// ── Auth tabs ────────────────────────────────────────────────
function authTab(t){
  document.querySelectorAll('.auth-tab').forEach((b,i)=>b.classList.toggle('active',['login','register','reset'][i]===t));
  document.querySelectorAll('.auth-screen').forEach(s=>s.classList.remove('active'));
  document.getElementById('s-'+t).classList.add('active');
}
function showMsg(id,msg,ok=false){const el=document.getElementById(id);el.textContent=msg;el.className='auth-msg show '+(ok?'ok':'err')}

// ── API ──────────────────────────────────────────────────────
async function apiFetch(path,opts={}){
  const h={'Content-Type':'application/json'};
  if(token)h['Authorization']='Bearer '+token;
  const r=await fetch(API+path,{headers:h,...opts});
  const d=await r.json().catch(()=>({}));
  if(r.status===401&&path!=='/auth/login'){doLogout();return{ok:false}}
  return d;
}

// ── Auth actions ─────────────────────────────────────────────
async function doLogin(){
  const email=document.getElementById('l-email').value.trim();
  const pass=document.getElementById('l-pass').value;
  if(!email||!pass){showMsg('msg-login','Compila tutti i campi.');return}
  const d=await apiFetch('/auth/login',{method:'POST',body:JSON.stringify({email,password:pass})});
  if(!d.ok){showMsg('msg-login',d.error||'Errore.');return}
  token=d.token;currentUser=d.user;
  localStorage.setItem('easyqso_token',token);
  const exp=new Date(Date.now()+30*24*60*60*1000).toUTCString();
  document.cookie='easyqso_token='+token+'; expires='+exp+'; path=/; SameSite=Lax; Secure';
  bootApp();
}
async function doRegister(){
  const call=document.getElementById('r-call').value.trim().toUpperCase();
  const email=document.getElementById('r-email').value.trim();
  const pass=document.getElementById('r-pass').value;
  const pass2=document.getElementById('r-pass2').value;
  if(!call||!email||!pass){showMsg('msg-reg','Compila tutti i campi.');return}
  const d=await apiFetch('/auth/register',{method:'POST',body:JSON.stringify({callsign:call,email,password:pass,password2:pass2})});
  if(!d.ok){showMsg('msg-reg',d.error||'Errore.');return}
  showMsg('msg-reg',d.message||'Registrazione completata!',true);
}
async function doReset(){
  const email=document.getElementById('rs-email').value.trim();
  if(!email){showMsg('msg-reset','Inserisci la tua email.');return}
  const d=await apiFetch('/auth/reset-request',{method:'POST',body:JSON.stringify({email})});
  showMsg('msg-reset',d.message||'Operazione completata.',true);
}
async function doLogout(){
  closePanel('panel-account');closePanel('panel-users');
  if(token)await apiFetch('/auth/logout',{method:'POST'});
  token=null;currentUser=null;log=[];
  localStorage.removeItem('easyqso_token');
  document.cookie='easyqso_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Lax; Secure';
  document.getElementById('app-wrap').style.display='none';
  document.getElementById('auth-wrap').style.display='flex';
}

// ── Boot ─────────────────────────────────────────────────────
async function bootApp(){
  document.getElementById('auth-wrap').style.display='none';
  document.getElementById('app-wrap').style.display='flex';
  document.getElementById('user-badge').textContent='👤 '+currentUser.callsign;
  document.getElementById('menu-users-item').style.display=currentUser.is_admin?'block':'none';
  if(currentUser.theme)setTheme(currentUser.theme);
  document.getElementById('opt-at').checked=!!+currentUser.opt_autotime;
  document.getElementById('opt-kf').checked=!!+currentUser.opt_keepfreq;
  await loadDXCC();
  initDXCCAutofill();
  initAdifDrop();
  await loadLog();
  initForm();
}
async function loadLog(){
  const d=await apiFetch('/qso');
  if(d.ok){log=d.qso||[];serial=log.length?Math.max(...log.map(q=>+q.id))+1:1;}
  renderLog();updateStats();
}

// ── Freq / Band ───────────────────────────────────────────────
function freqToBand(raw){const f=parseFloat(String(raw).replace(',','.'));if(isNaN(f)||f<=0)return null;for(const[min,max,name]of BANDS)if(f>=min&&f<=max)return name;return null}
function onFreqInput(){
  const val=document.getElementById('f-freq').value;
  const band=freqToBand(val);const sel=document.getElementById('f-band');const hint=document.getElementById('band-hint');
  if(band){sel.value=band;sel.classList.add('auto');hint.textContent='↑ banda rilevata';hint.classList.add('v')}
  else{sel.classList.remove('auto');hint.textContent=val.length>0?'— fuori banda':'';hint.classList.toggle('v',val.length>0)}
}
document.getElementById('f-freq').addEventListener('input',onFreqInput);
document.getElementById('f-freq').addEventListener('paste',()=>setTimeout(onFreqInput,0));

// ── Form ─────────────────────────────────────────────────────
function initForm(){
  const n=nowUTC();
  if(document.getElementById('opt-at').checked){document.getElementById('f-date').value=n.date;document.getElementById('f-time').value=n.time}
  ['f-call','f-dxcc','f-note','f-ser-r'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('f-ser-s').value=pad(serial,3);
  document.getElementById('f-rst-s').value='59';document.getElementById('f-rst-r').value='59';
  document.getElementById('dupe-alert').style.display='none';document.getElementById('dxcc-badge').className='dxcc-badge';
  if(document.getElementById('opt-kf').checked&&lastFreq){document.getElementById('f-freq').value=lastFreq;onFreqInput()}
  else{document.getElementById('f-freq').value='';document.getElementById('f-band').classList.remove('auto');document.getElementById('band-hint').className='band-hint'}
  document.getElementById('f-call').focus();
}
function clearForm(){
  lastFreq='';const n=nowUTC();
  document.getElementById('f-date').value=n.date;document.getElementById('f-time').value=n.time;
  ['f-call','f-freq','f-dxcc','f-note','f-ser-r'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('f-ser-s').value=pad(serial,3);document.getElementById('f-rst-s').value='59';document.getElementById('f-rst-r').value='59';
  document.getElementById('dupe-alert').style.display='none';document.getElementById('f-band').classList.remove('auto');
  document.getElementById('band-hint').className='band-hint';document.getElementById('dxcc-badge').className='dxcc-badge';
  document.getElementById('f-call').focus();
}
let timeEditing=false;
['f-time','f-date'].forEach(id=>{document.getElementById(id).addEventListener('focus',()=>timeEditing=true);document.getElementById(id).addEventListener('blur',()=>timeEditing=false)});
setInterval(()=>{if(document.getElementById('opt-at').checked&&!timeEditing){const n=nowUTC();document.getElementById('f-date').value=n.date;document.getElementById('f-time').value=n.time}},10000);
document.getElementById('f-call').addEventListener('input',function(){
  this.value=this.value.toUpperCase();
  document.getElementById('dupe-alert').style.display=log.find(q=>q.callsign===this.value.trim())?'block':'none';
  // DXCC autofill
  if(this.value.length>=2&&Object.keys(DXCC).length){
    const r=extractPrefix(this.value);
    if(r){if(!document.getElementById('f-dxcc').value)document.getElementById('f-dxcc').value=r.pfx;const b=document.getElementById('dxcc-badge');b.textContent=r.entity.name;b.className='dxcc-badge show'}
    else document.getElementById('dxcc-badge').className='dxcc-badge';
  }
});
document.getElementById('f-dxcc').addEventListener('input',function(){this.value=this.value.toUpperCase()});

// ── Log QSO ──────────────────────────────────────────────────
async function logQSO(){
  const call=document.getElementById('f-call').value.trim().toUpperCase();
  if(!call){toast('Inserisci il nominativo!',true);document.getElementById('f-call').focus();return}
  const freqRaw=document.getElementById('f-freq').value.replace(',','.');
  const freqNum=parseFloat(freqRaw);const freq=isNaN(freqNum)?'':freqNum.toString();
  if(freq)lastFreq=document.getElementById('f-freq').value;
  const band=document.getElementById('f-band').value;
  const mode=document.getElementById('f-mode').value;
  const payload={callsign:call,date:document.getElementById('f-date').value,time:document.getElementById('f-time').value,
    freq,band,mode,rst_sent:document.getElementById('f-rst-s').value||'59',rst_rcvd:document.getElementById('f-rst-r').value||'59',
    serial_sent:document.getElementById('f-ser-s').value,serial_rcvd:document.getElementById('f-ser-r').value,
    dxcc:document.getElementById('f-dxcc').value.trim().toUpperCase(),note:document.getElementById('f-note').value.trim()};
  const d=await apiFetch('/qso',{method:'POST',body:JSON.stringify(payload)});
  if(!d.ok){toast(d.error||'Errore salvataggio.',true);return}
  log.unshift(d.qso);serial=+d.qso.id+1;
  renderLog();updateStats();
  toast('QSO #'+d.qso.id+' — '+d.qso.callsign+' loggato!');
  sendQsoNotif(call,band,mode);
  checkAutoBackup();
  if(contestActive)updateContestScore();
  initForm();
}

// ── Edit inline ───────────────────────────────────────────────
async function deleteQSO(id){
  if(!confirm('Eliminare questo QSO?'))return;
  const d=await apiFetch('/qso/'+id,{method:'DELETE'});
  if(!d.ok){toast(d.error||'Errore.',true);return}
  if(editingId===id)editingId=null;
  log=log.filter(q=>+q.id!==id);renderLog();updateStats();
}
function startEdit(id){editingId=id;renderLog();setTimeout(()=>{const el=document.querySelector('.edit-input');if(el)el.focus()},50)}
function cancelEdit(){editingId=null;renderLog()}
async function saveEdit(id){
  const g=f=>{const el=document.getElementById('e-'+id+'-'+f);return el?el.value.trim():''};
  const payload={callsign:g('call')||'',date:g('date'),time:g('time'),freq:g('freq').replace(',','.'),
    band:g('band'),mode:g('mode'),rst_sent:g('rst_s')||'59',rst_rcvd:g('rst_r')||'59',
    serial_sent:g('ser_s'),serial_rcvd:g('ser_r'),dxcc:g('dxcc'),note:g('note')};
  const d=await apiFetch('/qso/'+id,{method:'PUT',body:JSON.stringify(payload)});
  if(!d.ok){toast(d.error||'Errore.',true);return}
  const idx=log.findIndex(q=>+q.id===id);if(idx>=0)log[idx]=d.qso;
  editingId=null;renderLog();updateStats();toast('QSO #'+id+' aggiornato!');
}
function ei(id,field,val,extra=''){return`<input class="edit-input" id="e-${id}-${field}" value="${(val||'').replace(/"/g,'&quot;')}" ${extra} onkeydown="if(event.key==='Enter'){event.preventDefault();saveEdit(${id})}if(event.key==='Escape')cancelEdit()">`}
function esel(id,field,opts,val){return`<select class="edit-sel" id="e-${id}-${field}" onkeydown="if(event.key==='Escape')cancelEdit()">${opts.map(o=>`<option${o===val?' selected':''}>${o}</option>`).join('')}</select>`}
function modeClass(m){return({SSB:'m-ssb',CW:'m-cw',FT8:'m-ft8',FT4:'m-ft4',AM:'m-am',FM:'m-am',DIGI:'m-digi',RTTY:'m-digi'})[m]||'m-digi'}

// ── Render log ────────────────────────────────────────────────
function renderLog(){
  const filter=document.getElementById('filter-call').value.trim().toUpperCase();
  let data=[...log];
  if(filter)data=data.filter(q=>q.callsign.includes(filter)||(q.dxcc&&q.dxcc.includes(filter)));
  if(bandFilter!=='ALL')data=data.filter(q=>q.band===bandFilter);
  data.sort((a,b)=>{let av=a[sortCol],bv=b[sortCol];if(sortCol==='id'||sortCol==='freq'){av=parseFloat(av)||0;bv=parseFloat(bv)||0}return av<bv?sortDir:av>bv?-sortDir:0});
  const tb=document.getElementById('log-body');
  document.getElementById('empty-state').style.display=data.length?'none':'block';
  tb.innerHTML=data.map((q,i)=>{
    const isEdit=+q.id===editingId;
    if(isEdit)return`<tr class="editing"><td class="sn">${q.id}</td><td>${ei(q.id,'call',q.callsign,'maxlength="12" style="min-width:90px;color:var(--ac3);font-size:14px;font-weight:600;text-transform:uppercase"')}</td><td>${ei(q.id,'date',q.qso_date,'type="date" style="min-width:110px"')}</td><td>${ei(q.id,'time',(q.qso_time||'').slice(0,5),'type="time" style="min-width:80px"')}</td><td>${ei(q.id,'freq',q.freq,'style="min-width:70px;color:var(--ac4)"')}</td><td>${esel(q.id,'band',BANDS_LIST,q.band)}</td><td>${esel(q.id,'mode',MODES,q.mode)}</td><td>${ei(q.id,'rst_s',q.rst_sent,'maxlength="3" style="min-width:44px;color:var(--ac2)"')}</td><td>${ei(q.id,'rst_r',q.rst_rcvd,'maxlength="3" style="min-width:44px;color:var(--ac2)"')}</td><td style="display:flex;gap:3px">${ei(q.id,'ser_s',q.serial_sent,'maxlength="6" style="min-width:44px"')}${ei(q.id,'ser_r',q.serial_rcvd,'maxlength="6" style="min-width:44px"')}</td><td>${ei(q.id,'dxcc',q.dxcc,'maxlength="10" style="min-width:55px;text-transform:uppercase"')}</td><td>${ei(q.id,'note',q.note,'maxlength="40" style="min-width:100px"')}</td><td><button class="btn-sav" onclick="saveEdit(${q.id})">✓ OK</button><button class="btn-cnc" onclick="cancelEdit()">✕</button></td></tr>`;
    return`<tr class="${i===0&&log[0]?.id===q.id?'new-row':''}"><td class="sn">${q.id}</td><td class="call">${q.callsign}</td><td>${q.qso_date||''}</td><td>${(q.qso_time||'').slice(0,5)}</td><td class="fq">${q.freq?parseFloat(q.freq).toFixed(3):''}</td><td class="bc">${q.band}</td><td><span class="bm ${modeClass(q.mode)}">${q.mode}</span></td><td class="rs">${q.rst_sent}</td><td class="rs">${q.rst_rcvd}</td><td style="color:var(--txm);font-size:12px">${q.serial_sent||'-'}/${q.serial_rcvd||'-'}</td><td style="color:var(--ac4)">${q.dxcc||''}</td><td style="font-size:12px;color:var(--txm);max-width:110px;overflow:hidden;text-overflow:ellipsis">${q.note||''}</td><td><button class="btn-edt" onclick="startEdit(${q.id})" title="Modifica">✏️</button><button class="btn-del" onclick="deleteQSO(${q.id})" title="Elimina">✕</button></td></tr>`;
  }).join('');
}
function updateStats(){
  document.getElementById('tot-qso').textContent=log.length;
  document.getElementById('tot-dxcc').textContent=new Set(log.filter(q=>q.dxcc).map(q=>q.dxcc)).size;
  let pts=0;log.forEach(q=>{pts+=(['15m','10m','6m'].includes(q.band)?3:['20m','40m','80m'].includes(q.band)?2:1)});
  document.getElementById('tot-pts').textContent=pts;
}
function sortBy(col){if(sortCol===col)sortDir*=-1;else{sortCol=col;sortDir=-1}document.querySelectorAll('th').forEach(th=>th.className='');const cols=['id','callsign','qso_date','qso_time','freq','band','mode','rst_sent','rst_rcvd','','dxcc'];const ths=document.querySelectorAll('th');const idx=cols.indexOf(col);if(idx>=0&&ths[idx])ths[idx].className=sortDir===-1?'sd':'sa';renderLog()}
function setBand(btn){bandFilter=btn.dataset.band;document.querySelectorAll('.bp').forEach(p=>p.classList.remove('active'));btn.classList.add('active');renderLog()}

// ── Export ────────────────────────────────────────────────────
async function exportADIF(){if(!log.length){toast('Nessun QSO!',true);return}window.open(API+'/user/export-adif','_blank')}
async function exportCSV(){if(!log.length){toast('Nessun QSO!',true);return}window.open(API+'/user/export-csv','_blank')}

// ── Clock ─────────────────────────────────────────────────────
function tickClock(){const d=new Date();document.getElementById('clock').textContent=pad(d.getUTCHours())+':'+pad(d.getUTCMinutes())+':'+pad(d.getUTCSeconds())}
tickClock();setInterval(tickClock,1000);
document.addEventListener('keydown',e=>{if(e.key==='Enter'&&document.activeElement.tagName==='INPUT'&&editingId===null&&!document.activeElement.closest('.ov')&&!document.activeElement.closest('.panel-overlay')&&!document.activeElement.closest('.auth-wrap'))logQSO()});

// ════════════════════════════════════════════════════════════
//  ACCOUNT PANEL
// ════════════════════════════════════════════════════════════
function openPanel(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden'}
function closePanel(id){document.getElementById(id).classList.remove('open');document.body.style.overflow=''}
function switchPTab(prefix,idx,btn){
  const base=prefix==='acc'?'panel-account':'panel-users';
  document.querySelectorAll('#'+base+' .ptab-pane').forEach((p,i)=>p.classList.toggle('active',i===idx));
  document.querySelectorAll('#'+base+' .ptab').forEach(t=>t.classList.remove('active'));
  btn.classList.add('active');
}
function showPanelMsg(id,msg,ok=false){const el=document.getElementById(id);if(!el)return;el.textContent=msg;el.className='panel-msg show '+(ok?'ok':'err');setTimeout(()=>el.className='panel-msg',4000)}

async function openAccount(){
  closeBurger();
  const[s,stats,me]=await Promise.all([apiFetch('/user/settings'),apiFetch('/user/stats'),apiFetch('/auth/me')]);
  if(s.ok){document.getElementById('acc-callsign').value=s.settings.callsign;document.getElementById('acc-email').value=s.settings.email}
  if(stats.ok)document.getElementById('acc-qso-count').value=stats.stats.total+' QSO';
  if(me.ok){document.getElementById('acc-created').value=(me.user.created_at||'').slice(0,10);document.getElementById('acc-lastlogin').value=(me.user.last_login||'Mai').slice(0,16)}
  openPanel('panel-account');loadSessions();
}
async function accSaveCallsign(){
  const call=document.getElementById('acc-callsign').value.trim().toUpperCase();
  const d=await apiFetch('/user/callsign',{method:'PUT',body:JSON.stringify({callsign:call})});
  if(!d.ok){showPanelMsg('acc-msg-0',d.error||'Errore.');return}
  currentUser.callsign=d.callsign;document.getElementById('user-badge').textContent='👤 '+d.callsign;
  showPanelMsg('acc-msg-0','Nominativo aggiornato: '+d.callsign,true);
}
async function accChangePassword(){
  const old=document.getElementById('acc-old-pw').value;
  const pw=document.getElementById('acc-new-pw').value;
  const pw2=document.getElementById('acc-new-pw2').value;
  const lo=document.getElementById('acc-logout-others').checked;
  if(!old||!pw||!pw2){showPanelMsg('acc-msg-1','Compila tutti i campi.');return}
  if(pw!==pw2){showPanelMsg('acc-msg-1','Le password non coincidono.');return}
  const d=await apiFetch('/user/change-password',{method:'POST',body:JSON.stringify({old_password:old,new_password:pw,new_password2:pw2,logout_others:lo})});
  if(!d.ok){showPanelMsg('acc-msg-1',d.error||'Errore.');return}
  document.getElementById('acc-old-pw').value='';document.getElementById('acc-new-pw').value='';document.getElementById('acc-new-pw2').value='';
  showPanelMsg('acc-msg-1','Password aggiornata!',true);if(lo)loadSessions();
}
async function loadSessions(){
  const d=await apiFetch('/user/sessions');const el=document.getElementById('sess-list');
  if(!d.ok||!d.sessions.length){el.innerHTML='<div style="font-family:\'Share Tech Mono\',monospace;font-size:11px;color:var(--txm)">Nessuna sessione attiva.</div>';return}
  el.innerHTML=d.sessions.map(s=>`<div class="sess-item">${s.current?'<span class="badge-cur">CORRENTE</span>':''}<span>${s.created_at.slice(0,16)}</span><span style="margin-left:auto;color:var(--txm)">scade ${s.expires_at.slice(0,10)}</span>${!s.current?`<span class="revoke" onclick="revokeSession(${s.id})">✕ Revoca</span>`:''}</div>`).join('');
}
async function revokeSession(sid){const d=await apiFetch('/user/session/'+sid,{method:'DELETE'});if(d.ok)loadSessions();else showPanelMsg('acc-msg-1',d.error||'Errore.')}
async function accRequestDelete(){
  const qa=document.querySelector('input[name="qso-action"]:checked')?.value||'anonymize';
  if(!confirm(`Sei sicuro? Riceverai un'email di conferma.\nI QSO saranno: ${qa==='delete'?'ELIMINATI':'ANONIMIZZATI'}`))return;
  const d=await apiFetch('/user/request-delete',{method:'POST',body:JSON.stringify({qso_action:qa})});
  if(!d.ok){showPanelMsg('acc-msg-2',d.error||'Errore.');return}
  showPanelMsg('acc-msg-2','Email inviata! Controlla la casella entro 24 ore.',true);
}

// ════════════════════════════════════════════════════════════
//  USER MANAGEMENT (admin)
// ════════════════════════════════════════════════════════════
let umCurrentPage=1,umTotalPages=1,audCurrentPage=1,audTotalPages=1,umDebounce=null;
function debounceLoadUsers(){clearTimeout(umDebounce);umDebounce=setTimeout(loadUsers,400)}
async function openUserMgmt(){closeBurger();openPanel('panel-users');loadUsers()}
async function loadUsers(page=1){
  umCurrentPage=page;
  const q=document.getElementById('um-search').value.trim();
  const filter=document.getElementById('um-filter').value;
  const d=await apiFetch(`/admin/users?q=${encodeURIComponent(q)}&filter=${filter}&page=${page}`);
  const tb=document.getElementById('um-tbody');
  if(!d.ok){tb.innerHTML=`<tr><td colspan="10" style="color:var(--red);padding:20px;text-align:center">${d.error}</td></tr>`;return}
  umTotalPages=d.pages||1;
  document.getElementById('um-page-info').textContent=`Pag. ${d.page}/${d.pages} — ${d.total} utenti`;
  document.getElementById('um-prev').disabled=d.page<=1;document.getElementById('um-next').disabled=d.page>=d.pages;
  if(!d.users.length){tb.innerHTML='<tr><td colspan="10" style="text-align:center;padding:24px;color:var(--txm)">Nessun utente trovato.</td></tr>';return}
  tb.innerHTML=d.users.map(u=>`<tr><td style="color:var(--txm)">${u.id}</td><td class="call">${u.callsign}${u.is_admin?` <span class="bpill bp-warn">ADMIN</span>`:''}</td><td class="email">${u.email}</td><td>${u.is_verified?'<span class="bpill bp-ok">✓</span>':'<span class="bpill bp-no">✗</span>'}</td><td>${u.is_admin?'<span class="bpill bp-warn">✓</span>':''}</td><td>${u.is_active?'<span class="bpill bp-ok">Attivo</span>':'<span class="bpill bp-no">Sospeso</span>'}</td><td style="color:var(--ac)">${u.qso_count}</td><td>${(u.created_at||'').slice(0,10)}</td><td>${(u.last_login||'—').slice(0,10)}</td><td><button class="pbtn ghost sm" onclick="openEditUser(${u.id},'${u.callsign}',${u.is_verified},${u.is_admin},${u.is_active},${u.force_pw_change||0})">✏️</button></td></tr>`).join('');
}
function umPage(dir){loadUsers(Math.max(1,Math.min(umTotalPages,umCurrentPage+dir)))}
function toggleCreateDrawer(){document.getElementById('create-drawer').classList.toggle('open')}
async function createUser(){
  const d=await apiFetch('/admin/users',{method:'POST',body:JSON.stringify({callsign:document.getElementById('cu-call').value.trim(),email:document.getElementById('cu-email').value.trim(),password:document.getElementById('cu-pass').value,is_admin:document.getElementById('cu-admin').checked})});
  if(!d.ok){showPanelMsg('um-msg',d.error||'Errore.');return}
  showPanelMsg('um-msg','Utente creato!',true);
  ['cu-call','cu-email','cu-pass'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('cu-admin').checked=false;toggleCreateDrawer();loadUsers(umCurrentPage);
}
let euState={v:1,a:0,ac:1,fp:0},euUserId=null;
function openEditUser(id,call,verified,admin,active,force_pw){
  euUserId=id;euState={v:+verified,a:+admin,ac:+active,fp:+force_pw};
  document.getElementById('eu-id').value=id;document.getElementById('eu-title').textContent='Modifica utente';
  document.getElementById('eu-sub').textContent=`#${id} — ${call}`;document.getElementById('eu-reason').value='';
  document.getElementById('eu-reason-wrap').style.display='none';euRefresh();openOv('ov-edit-user');
}
function euSet(f,v){euState[f]=v;if(f==='ac')document.getElementById('eu-reason-wrap').style.display=v?'none':'block';euRefresh()}
function euRefresh(){
  const on='pbtn sm',off='pbtn ghost sm';
  document.getElementById('eu-v1').className=euState.v?on:off;document.getElementById('eu-v0').className=euState.v?off:on;
  document.getElementById('eu-a1').className=euState.a?on:off;document.getElementById('eu-a0').className=euState.a?off:on;
  document.getElementById('eu-ac1').className=euState.ac?on:off;document.getElementById('eu-ac0').className=euState.ac?off:on;
}
async function euSave(){
  const d=await apiFetch('/admin/user/'+euUserId,{method:'PUT',body:JSON.stringify({is_verified:euState.v,is_admin:euState.a,is_active:euState.ac,suspended_reason:euState.ac?null:document.getElementById('eu-reason').value||null})});
  if(!d.ok){showPanelMsg('eu-msg',d.error||'Errore.');return}
  showPanelMsg('eu-msg','Utente aggiornato.',true);setTimeout(()=>{closeOv('ov-edit-user');loadUsers(umCurrentPage)},800);
}
async function euSendReset(){const d=await apiFetch('/admin/user/'+euUserId,{method:'POST',body:JSON.stringify({action:'reset-password'})});d.ok?showPanelMsg('eu-msg','Email reset inviata!',true):showPanelMsg('eu-msg',d.error||'Errore.')}
function euDelete(){document.getElementById('du-sub').textContent=document.getElementById('eu-sub').textContent;closeOv('ov-edit-user');openOv('ov-del-user')}
async function euConfirmDelete(){
  const qa=document.querySelector('input[name="du-qso"]:checked')?.value||'anonymize';
  const d=await apiFetch('/admin/user/'+euUserId,{method:'DELETE',body:JSON.stringify({qso_action:qa})});
  if(!d.ok){showPanelMsg('du-msg',d.error||'Errore.');return}
  closeOv('ov-del-user');toast('Utente eliminato.');loadUsers(umCurrentPage);
}
async function loadAudit(page=1){
  audCurrentPage=page;const d=await apiFetch(`/admin/audit?page=${page}`);const tb=document.getElementById('audit-tbody');
  if(!d.ok||!d.logs.length){tb.innerHTML='<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--txm)">Nessuna voce.</td></tr>';return}
  audTotalPages=d.pages||1;document.getElementById('aud-page-info').textContent=`Pag. ${d.page}/${d.pages} — ${d.total} voci`;
  document.getElementById('aud-prev').disabled=d.page<=1;document.getElementById('aud-next').disabled=d.page>=d.pages;
  tb.innerHTML=d.logs.map(l=>`<tr><td style="color:var(--txm)">${l.id}</td><td style="color:var(--ac3)">${l.admin_callsign}</td><td style="color:var(--tx)">${l.action}</td><td style="color:var(--txd)">${l.target_user_id||'—'}</td><td style="color:var(--txm);font-size:10px">${l.ip||'—'}</td><td style="color:var(--txm)">${(l.created_at||'').slice(0,16)}</td></tr>`).join('');
}
function audPage(dir){loadAudit(Math.max(1,Math.min(audTotalPages,audCurrentPage+dir)))}

// ════════════════════════════════════════════════════════════
//  FEATURE 1 — DXCC Auto-fill
// ════════════════════════════════════════════════════════════
async function loadDXCC(){try{const r=await fetch(API+'/dxcc');DXCC=await r.json()}catch(e){console.warn('DXCC not loaded')}}
function extractPrefix(callsign){
  const clean=callsign.replace(/\/[A-Z0-9]+$/,'').toUpperCase();
  const local=clean.includes('/')?clean.split('/').pop():clean;
  for(let len=Math.min(local.length,5);len>=1;len--){
    const pfx=local.substring(0,len);if(DXCC[pfx])return{pfx,entity:DXCC[pfx]};
    const nonum=pfx.replace(/[0-9]+$/,'');if(nonum!==pfx&&DXCC[nonum])return{pfx:nonum,entity:DXCC[nonum]};
  }return null;
}
function initDXCCAutofill(){}// già integrato nell'event listener di f-call

// ════════════════════════════════════════════════════════════
//  FEATURE 10 — QRZ lookup
// ════════════════════════════════════════════════════════════
function qrzLookup(){const call=document.getElementById('f-call').value.trim().toUpperCase();if(!call){toast('Inserisci il nominativo!',true);return}window.open('https://www.qrz.com/db/'+call,'_blank','noopener')}

// ════════════════════════════════════════════════════════════
//  FEATURE 8 — Notifiche desktop
// ════════════════════════════════════════════════════════════
let notifEnabled=localStorage.getItem('easyqso_notif')==='1';
function updateNotifUI(){const badge=document.getElementById('notif-badge');const btn=document.getElementById('notif-btn');const wrap=document.getElementById('notif-test-wrap');if(!badge)return;const perm=Notification.permission;if(perm==='granted'&&notifEnabled){badge.textContent='Attive';badge.className='notif-badge notif-on';if(btn)btn.textContent='Disattiva';if(wrap)wrap.style.display='block'}else{badge.textContent=perm==='denied'?'Bloccate dal browser':'Non attive';badge.className='notif-badge notif-off';if(btn)btn.textContent=perm==='denied'?'Permesso negato':'Attiva notifiche';if(wrap)wrap.style.display='none'}}
async function requestNotifPermission(){if(!('Notification'in window)){alert('Notifiche non supportate.');return}if(Notification.permission==='granted'&&notifEnabled){notifEnabled=false;localStorage.setItem('easyqso_notif','0');updateNotifUI();return}if(Notification.permission!=='denied'){const p=await Notification.requestPermission();if(p==='granted'){notifEnabled=true;localStorage.setItem('easyqso_notif','1')}}updateNotifUI()}
function sendQsoNotif(call,band,mode){if(!notifEnabled||Notification.permission!=='granted')return;try{new Notification('QSO loggato — EasyQSO',{body:`${call} • ${band} • ${mode}`,icon:'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">📻</text></svg>',tag:'easyqso-qso'})}catch(e){}}
function testNotif(){sendQsoNotif('IK1TEST','20m','SSB')}

// ════════════════════════════════════════════════════════════
//  FEATURE 9 — Backup automatico
// ════════════════════════════════════════════════════════════
let autoBackupEnabled=false,autoBackupTimer=null,qsoSinceLastBackup=0;
function toggleAutoBackup(){autoBackupEnabled=!autoBackupEnabled;const btn=document.getElementById('backup-toggle-btn');const sts=document.getElementById('backup-status');if(autoBackupEnabled){btn.textContent='✕ Disattiva backup auto';if(sts)sts.textContent='✅ Backup automatico attivo.';const when=document.querySelector('input[name="backup-when"]:checked')?.value||'hour';if(when==='hour'){autoBackupTimer=setInterval(backupNow,3600000)}}else{btn.textContent='Attiva backup auto';if(sts)sts.textContent='';clearInterval(autoBackupTimer)}}
function backupNow(){if(!log.length){toast('Nessun QSO!',true);return}let adif='ADIF Export from EasyQSO v<?= APP_VERSION ?> — Backup\n<EOH>\n\n';log.forEach(q=>{const d=(q.qso_date||'').replace(/-/g,'');const t=(q.qso_time||'').replace(':','').slice(0,4);const f=q.freq?parseFloat(q.freq).toFixed(3):'';[['CALL',q.callsign],['BAND',q.band],['MODE',q.mode],['QSO_DATE',d],['TIME_ON',t],f?['FREQ',f]:null,q.rst_sent?['RST_SENT',q.rst_sent]:null,q.rst_rcvd?['RST_RCVD',q.rst_rcvd]:null,q.dxcc?['DXCC',q.dxcc]:null,q.note?['NOTES',q.note]:null].forEach(fld=>{if(fld&&fld[1])adif+=`<${fld[0]}:${fld[1].length}>${fld[1]} `});adif+='<EOR>\n'});const ts=new Date().toISOString().slice(0,16).replace(/[:T]/g,'-');const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([adif],{type:'text/plain'}));a.download=`easyqso_backup_${ts}.adi`;a.click();qsoSinceLastBackup=0;const sts=document.getElementById('backup-status');if(sts)sts.textContent=`✅ Backup scaricato ${ts}.`}
function checkAutoBackup(){if(!autoBackupEnabled)return;const when=document.querySelector('input[name="backup-when"]:checked')?.value||'hour';if(when==='qso'){qsoSinceLastBackup++;if(qsoSinceLastBackup>=10)backupNow()}}

// ════════════════════════════════════════════════════════════
//  FEATURE 7 — Modalità Contest
// ════════════════════════════════════════════════════════════
let contestActive=false,contestStart=null,contestTimer=null;
function toggleContest(){contestActive=!contestActive;const bar=document.getElementById('contest-bar');const btn=document.getElementById('contest-toggle-btn');if(contestActive){if(!contestStart)contestStart=Date.now();bar.classList.add('active');if(btn)btn.classList.add('on');contestTimer=setInterval(updateContestTimer,1000);updateContestScore()}else{bar.classList.remove('active');if(btn)btn.classList.remove('on');clearInterval(contestTimer)}}
function resetContest(){contestStart=Date.now();updateContestTimer();updateContestScore()}
function updateContestTimer(){if(!contestStart)return;const el=document.getElementById('contest-timer');if(!el)return;const diff=Math.floor((Date.now()-contestStart)/1000);el.textContent=pad(Math.floor(diff/3600))+':'+pad(Math.floor((diff%3600)/60))+':'+pad(diff%60)}
function updateContestScore(){if(!contestActive)return;let pts=0;const mults=new Set();log.forEach(q=>{pts+=(['15m','10m','6m'].includes(q.band)?3:['20m','40m','80m'].includes(q.band)?2:1);if(q.dxcc)mults.add(q.dxcc)});const total=pts*(mults.size||1);['contest-pts','contest-mults','contest-total'].forEach((id,i)=>{const el=document.getElementById(id);if(el)el.textContent=[pts,mults.size,total.toLocaleString('it-IT')][i]})}

// ════════════════════════════════════════════════════════════
//  FEATURE 5 — DX Cluster live
// ════════════════════════════════════════════════════════════
let clusterOpen=false,clusterInterval=null;
function toggleCluster(){clusterOpen=!clusterOpen;document.getElementById('cluster-panel').classList.toggle('open',clusterOpen);if(clusterOpen){fetchCluster();clusterInterval=setInterval(fetchCluster,60000)}else clearInterval(clusterInterval)}
async function fetchCluster(){const band=document.getElementById('cl-band')?.value||'';const mode=document.getElementById('cl-mode')?.value||'';const dot=document.getElementById('cluster-dot');if(dot)dot.className='cluster-dot';try{const url=`<?= BASE_URL ?>/api/cluster.php?limit=25${band?'&band='+band:''}${mode?'&mode='+mode:''}`;const d=await fetch(url).then(r=>r.json());if(!d.ok){renderClusterError(d.error||'Errore.');return}renderClusterSpots(d.spots);if(dot)dot.className='cluster-dot live';const ts=document.getElementById('cluster-ts');if(ts)ts.textContent='Aggiornato '+new Date().toLocaleTimeString('it-IT',{hour:'2-digit',minute:'2-digit'})}catch(e){renderClusterError('Errore di rete.')}}
function renderClusterError(msg){const el=document.getElementById('cluster-list');if(el)el.innerHTML=`<div style="padding:20px;text-align:center;color:var(--red);font-family:'Share Tech Mono',monospace;font-size:11px">${msg}</div>`}
function renderClusterSpots(spots){const el=document.getElementById('cluster-list');if(!el)return;if(!spots.length){el.innerHTML='<div style="padding:20px;text-align:center;font-family:\'Share Tech Mono\',monospace;font-size:11px;color:var(--txm)">Nessuno spot.</div>';return}el.innerHTML=spots.map(s=>`<div class="cluster-spot" onclick="spotToForm('${s.dx}','${s.freq}','${s.band}','${s.mode}')" title="${(s.comment||'').replace(/'/g,'')}"><div style="display:flex;justify-content:space-between;align-items:baseline"><span class="spot-dx">${s.dx}</span><span class="spot-freq">${s.freq>0?s.freq.toFixed(1)+' MHz':s.band}</span></div><div class="spot-info">${s.mode?'<b>'+s.mode+'</b> · ':''} ${s.comment||'—'} <span style="color:var(--txm);margin-left:4px">${s.time||''}</span></div></div>`).join('')}
function spotToForm(dx,freq,band,mode){const callEl=document.getElementById('f-call');const freqEl=document.getElementById('f-freq');const bandEl=document.getElementById('f-band');const modeEl=document.getElementById('f-mode');if(callEl)callEl.value=dx;if(freq>0&&freqEl){freqEl.value=freq;onFreqInput()}else if(band&&bandEl)bandEl.value=band.toLowerCase();if(mode&&modeEl){const m=MODES.find(x=>x===mode.toUpperCase());if(m)modeEl.value=m}callEl?.dispatchEvent(new Event('input'));callEl?.focus();toggleCluster()}

// ════════════════════════════════════════════════════════════
//  FEATURE 2 — Import ADIF
// ════════════════════════════════════════════════════════════
function showTool(name){['import','cabrillo','map','stats','backup','notif'].forEach(t=>{const el=document.getElementById('tool-'+t);if(el)el.style.display=t===name?'block':'none'});if(name==='map')renderDXCCMap();if(name==='stats')renderStatsChart('hour');if(name==='notif')updateNotifUI()}
function openTools(toolName){closeBurger();openOv('ov-tools');if(toolName)setTimeout(()=>showTool(toolName),50)}
function handleAdifFile(input){
  const file=input.files[0];if(!file)return;
  const zone=document.getElementById('adif-zone');
  const reader=new FileReader();
  reader.onload=async e=>{
    const adif=e.target.result;const res=document.getElementById('adif-result');
    try{
      const h={'Content-Type':'application/json'};if(token)h['Authorization']='Bearer '+token;
      const r=await fetch('<?= BASE_URL ?>/api/adif-import.php',{method:'POST',headers:h,body:JSON.stringify({adif})});
      const d=await r.json();
      res.style.display='block';res.className='adif-result '+(d.ok?'ok':'err');res.textContent=d.ok?d.message:(d.error||'Errore import.');
      if(d.ok){await loadLog();updateStats()}
    }catch(err){res.style.display='block';res.className='adif-result err';res.textContent='Errore di rete.'}
    input.value='';
  };
  reader.readAsText(file,'UTF-8');
}
function initAdifDrop(){const zone=document.getElementById('adif-zone');if(!zone)return;zone.addEventListener('dragover',e=>{e.preventDefault();zone.classList.add('drag-over')});zone.addEventListener('dragleave',()=>zone.classList.remove('drag-over'));zone.addEventListener('drop',e=>{e.preventDefault();zone.classList.remove('drag-over');const file=e.dataTransfer.files[0];if(file){const fi=document.getElementById('adif-file');const dt=new DataTransfer();dt.items.add(file);fi.files=dt.files;handleAdifFile(fi)}})}

// ════════════════════════════════════════════════════════════
//  FEATURE 3 — Export Cabrillo
// ════════════════════════════════════════════════════════════
function exportCabrillo(){if(!log.length){toast('Nessun QSO!',true);return}const contest=document.getElementById('cab-contest')?.value||'CQ-WW-SSB';const category=document.getElementById('cab-cat')?.value||'SINGLE-OP';const power=document.getElementById('cab-pwr')?.value||'HIGH';const band=document.getElementById('cab-band')?.value||'ALL';const h={};if(token)h['Authorization']='Bearer '+token;fetch(`<?= BASE_URL ?>/api/cabrillo.php?contest=${contest}&category=${category}&power=${power}&band=${band}`,{headers:h}).then(r=>r.blob()).then(blob=>{const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=contest.toLowerCase()+'_'+(currentUser?.callsign||'export').toLowerCase()+'.cbr';a.click();toast('File Cabrillo esportato!')}).catch(()=>toast('Errore export.',true))}

// ════════════════════════════════════════════════════════════
//  FEATURE 6 — Mappa DXCC
// ════════════════════════════════════════════════════════════
function renderDXCCMap(){
  const canvas=document.getElementById('dxcc-canvas');if(!canvas)return;
  const ctx=canvas.getContext('2d');const W=canvas.width,H=canvas.height;
  ctx.fillStyle='#0d1a2a';ctx.fillRect(0,0,W,H);
  ctx.strokeStyle='rgba(255,255,255,0.05)';ctx.lineWidth=0.5;
  for(let lon=-180;lon<=180;lon+=30){const x=((lon+180)/360)*W;ctx.beginPath();ctx.moveTo(x,0);ctx.lineTo(x,H);ctx.stroke()}
  for(let lat=-90;lat<=90;lat+=30){const y=((90-lat)/180)*H;ctx.beginPath();ctx.moveTo(0,y);ctx.lineTo(W,y);ctx.stroke()}
  ctx.strokeStyle='rgba(255,255,255,0.12)';ctx.lineWidth=1;ctx.beginPath();ctx.moveTo(0,H/2);ctx.lineTo(W,H/2);ctx.stroke();
  const worked=new Set(log.filter(q=>q.dxcc).map(q=>q.dxcc.toUpperCase()));
  const tooltip=document.getElementById('map-tooltip');const dots=[];
  Object.entries(DXCC).forEach(([pfx,entity])=>{
    const x=((entity.lon+180)/360)*W;const y=((90-entity.lat)/180)*H;
    const isWorked=worked.has(pfx.toUpperCase());const r=isWorked?4:2.5;
    ctx.beginPath();ctx.arc(x,y,r,0,Math.PI*2);ctx.fillStyle=isWorked?'#39ff6a':'#2a4a2a';ctx.fill();
    if(isWorked){ctx.strokeStyle='rgba(57,255,106,0.4)';ctx.lineWidth=1;ctx.stroke()}
    dots.push({x,y,r:r+4,pfx,entity,isWorked});
  });
  ctx.fillStyle='rgba(57,255,106,0.7)';ctx.font='bold 11px Share Tech Mono, monospace';
  ctx.fillText(`${worked.size} / ${Object.keys(DXCC).length} DXCC lavorate`,8,16);
  canvas.onmousemove=e=>{const rect=canvas.getBoundingClientRect();const sx=W/rect.width;const sy=H/rect.height;const mx=(e.clientX-rect.left)*sx;const my=(e.clientY-rect.top)*sy;const hit=dots.find(d=>Math.hypot(d.x-mx,d.y-my)<d.r);canvas.style.cursor=hit?'pointer':'';if(tooltip)tooltip.textContent=hit?`${hit.pfx} — ${hit.entity.name} (${hit.entity.cont})${hit.isWorked?' ✓':''}`:''}
  canvas.onclick=e=>{const rect=canvas.getBoundingClientRect();const sx=W/rect.width;const sy=H/rect.height;const mx=(e.clientX-rect.left)*sx;const my=(e.clientY-rect.top)*sy;const hit=dots.find(d=>Math.hypot(d.x-mx,d.y-my)<d.r);if(hit){const el=document.getElementById('f-dxcc');if(el)el.value=hit.pfx;toast(hit.entity.name+(hit.isWorked?' — già lavorata':' — non ancora lavorata'),!hit.isWorked)}}
}

// ════════════════════════════════════════════════════════════
//  FEATURE 4 — Grafici statistiche (Chart.js)
// ════════════════════════════════════════════════════════════
let statsChartInstance=null;
function switchStatTab(type,btn){document.querySelectorAll('.stab').forEach(b=>b.classList.remove('active'));btn.classList.add('active');renderStatsChart(type)}
function renderStatsChart(type){
  if(typeof Chart==='undefined'){const s=document.getElementById('stats-summary');if(s)s.textContent='Caricamento Chart.js…';const sc=document.createElement('script');sc.src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js';sc.onload=()=>renderStatsChart(type);document.head.appendChild(sc);return}
  const canvas=document.getElementById('stats-chart');if(!canvas)return;
  if(statsChartInstance){statsChartInstance.destroy();statsChartInstance=null}
  if(!log.length){const s=document.getElementById('stats-summary');if(s)s.textContent='Nessun QSO nel log.';return}
  let labels=[],data=[],label='';
  const accent=getComputedStyle(document.body).getPropertyValue('--ac').trim()||'#39ff6a';
  if(type==='hour'){const counts=Array(24).fill(0);log.forEach(q=>{const h=parseInt((q.qso_time||'00:00').split(':')[0]);if(!isNaN(h))counts[h]++});labels=Array.from({length:24},(_,i)=>pad(i)+':00');data=counts;label='QSO per ora UTC';const ph=counts.indexOf(Math.max(...counts));const s=document.getElementById('stats-summary');if(s)s.textContent=`Ora più attiva: ${labels[ph]} UTC (${counts[ph]} QSO)`}
  else if(type==='band'){const bands=['160m','80m','40m','30m','20m','17m','15m','12m','10m','6m','2m','70cm'];const counts={};bands.forEach(b=>counts[b]=0);log.forEach(q=>{if(counts[q.band]!==undefined)counts[q.band]++});const active=bands.filter(b=>counts[b]>0);labels=active;data=active.map(b=>counts[b]);label='QSO per banda';const top=active[data.indexOf(Math.max(...data))];const s=document.getElementById('stats-summary');if(s&&top)s.textContent=`Banda più attiva: ${top} (${counts[top]} QSO)`}
  else if(type==='mode'){const mc={};log.forEach(q=>{mc[q.mode]=(mc[q.mode]||0)+1});const sorted=Object.entries(mc).sort((a,b)=>b[1]-a[1]);labels=sorted.map(e=>e[0]);data=sorted.map(e=>e[1]);label='QSO per modo';const s=document.getElementById('stats-summary');if(s&&sorted[0])s.textContent=`Modo più usato: ${sorted[0][0]} (${sorted[0][1]} QSO)`}
  else if(type==='day'){const dc={};log.forEach(q=>{if(q.qso_date)dc[q.qso_date]=(dc[q.qso_date]||0)+1});const sorted=Object.entries(dc).sort((a,b)=>a[0].localeCompare(b[0])).slice(-14);labels=sorted.map(e=>e[0].slice(5));data=sorted.map(e=>e[1]);const s=document.getElementById('stats-summary');if(s)s.textContent=`${data.reduce((a,b)=>a+b,0)} QSO negli ultimi ${sorted.length} giorni`}
  statsChartInstance=new Chart(canvas,{type:'bar',data:{labels,datasets:[{label,data,backgroundColor:accent+'33',borderColor:accent,borderWidth:1,borderRadius:3}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{ticks:{color:'#5a8a5a',font:{family:'Share Tech Mono',size:10}},grid:{color:'rgba(255,255,255,0.04)'}},y:{ticks:{color:'#5a8a5a',font:{family:'Share Tech Mono',size:10}},grid:{color:'rgba(255,255,255,0.04)'},beginAtZero:true}}}});
}

// ── Auto login ────────────────────────────────────────────────
(async function(){
  let saved=localStorage.getItem('easyqso_token');
  if(!saved){const m=document.cookie.match(/(?:^|;\s*)easyqso_token=([a-f0-9]{64})/);if(m)saved=m[1]}
  if(!saved)return;
  token=saved;const d=await apiFetch('/auth/me');
  if(d.ok&&d.user){
    currentUser=d.user;
    const exp=new Date(Date.now()+30*24*60*60*1000).toUTCString();
    document.cookie='easyqso_token='+token+'; expires='+exp+'; path=/; SameSite=Lax; Secure';
    await bootApp();
  }else{
    token=null;localStorage.removeItem('easyqso_token');
    document.cookie='easyqso_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; SameSite=Lax; Secure';
  }
}());
</script>
</body>
</html>
