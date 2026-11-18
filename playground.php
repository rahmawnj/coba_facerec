<?php
// AiFace API Playground - halaman pengujian untuk index.php pada folder yang sama.
date_default_timezone_set('Asia/Jakarta');
$endpoint = 'index.php';
$serial = 'AXUH03009366';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AiFace API Playground</title>
<style>
:root{color-scheme:dark;--bg:#0b1020;--panel:#121a2d;--panel2:#18233b;--line:#293653;--text:#e8eefc;--muted:#9aa9c4;--blue:#6d8cff;--green:#35c991;--red:#ff7185;--amber:#ffc56b}
*{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at 10% 0%,#1a2b50 0,transparent 34%),var(--bg);color:var(--text);font:15px/1.55 Inter,ui-sans-serif,system-ui,-apple-system,Segoe UI,sans-serif}
.wrap{max-width:1180px;margin:0 auto;padding:34px 22px 60px}.top{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:26px}.eyebrow{color:#8ea8ff;text-transform:uppercase;letter-spacing:.15em;font-size:11px;font-weight:800}.title{font-size:clamp(27px,4vw,38px);letter-spacing:-.04em;margin:7px 0}.sub{color:var(--muted);max-width:680px}.pill{border:1px solid #315b52;background:#102b29;color:#8af0c8;border-radius:99px;padding:7px 12px;font-size:12px;white-space:nowrap}
.grid{display:grid;grid-template-columns:1.05fr .95fr;gap:18px}.panel{background:linear-gradient(145deg,rgba(24,35,59,.96),rgba(15,23,40,.98));border:1px solid var(--line);border-radius:18px;padding:21px;box-shadow:0 18px 50px #0002}.panel h2{font-size:17px;margin:0 0 5px}.hint{color:var(--muted);font-size:13px;margin:0 0 18px}.field{margin:13px 0}.field label{display:block;color:#b9c6df;font-size:12px;font-weight:700;margin-bottom:7px}.input,textarea{width:100%;border:1px solid #344563;background:#0b1325;color:#e8eefc;border-radius:10px;padding:11px 12px;font:13px ui-monospace,SFMono-Regular,Consolas,monospace;outline:none}.input:focus,textarea:focus{border-color:var(--blue);box-shadow:0 0 0 3px #6d8cff22}textarea{min-height:220px;resize:vertical;line-height:1.6}.actions{display:flex;flex-wrap:wrap;gap:9px;margin-top:15px}.btn{border:1px solid #3a4c70;background:#243454;color:#eef3ff;padding:10px 14px;border-radius:10px;font-weight:750;cursor:pointer;transition:.15s}.btn:hover{filter:brightness(1.18);transform:translateY(-1px)}.btn.primary{background:var(--blue);border-color:var(--blue);color:#08112b}.btn.green{background:#123c34;border-color:#267d68;color:#9bf1d3}.btn.ghost{background:transparent}.btn:disabled{opacity:.5;cursor:wait;transform:none}.quick{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.quick button{text-align:left;min-height:74px}.quick strong{display:block;font-size:14px}.quick small{display:block;color:#aab8d3;font-weight:450;margin-top:3px}.result-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px}.status{font-size:12px;padding:5px 9px;border-radius:99px;background:#27334b;color:#c5d2eb}.status.ok{background:#123b32;color:#8df0cb}.status.bad{background:#481f2b;color:#ff9cab}.status.wait{background:#45351c;color:#ffd58a}.response{background:#080e1b;border:1px solid #273653;border-radius:12px;min-height:245px;padding:15px;overflow:auto;white-space:pre-wrap;overflow-wrap:anywhere;font:12px/1.65 ui-monospace,SFMono-Regular,Consolas,monospace;color:#c9d7f1}.meta{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:12px 0}.meta div{background:#0c1527;border:1px solid #263653;border-radius:10px;padding:9px}.meta small{display:block;color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.08em}.meta b{font-size:13px}.note{margin-top:15px;border:1px solid #6a512d;background:#302719;color:#ffdda1;border-radius:12px;padding:12px 14px;font-size:12px}.url{font-family:ui-monospace,monospace;color:#a9bcff;overflow-wrap:anywhere}.footer{color:#7888a7;font-size:12px;text-align:center;margin-top:22px}
@media(max-width:820px){.grid{grid-template-columns:1fr}.top{display:block}.pill{display:inline-block;margin-top:12px}.meta{grid-template-columns:1fr 1fr}}@media(max-width:460px){.wrap{padding:22px 13px}.panel{padding:16px}.quick{grid-template-columns:1fr}.meta{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
  <header class="top">
    <div><div class="eyebrow">Device integration / HTTP JSON</div><h1 class="title">AiFace API Playground</h1><p class="sub">Tes komunikasi perangkat AiFace tanpa perlu menulis kode. Pilih contoh request, tekan tombol kirim, lalu lihat status HTTP dan respons server di sebelah kanan.</p></div>
    <div class="pill">● Local test console</div>
  </header>

  <div class="grid">
    <section class="panel">
      <h2>01. Siapkan request</h2>
      <p class="hint">Pilih tes cepat atau ubah JSON secara manual.</p>
      <div class="field"><label for="endpoint">URL endpoint yang akan dites</label><input class="input" id="endpoint" value="<?= htmlspecialchars($endpoint, ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="field"><label for="serial">Serial number perangkat</label><input class="input" id="serial" value="<?= htmlspecialchars($serial, ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="field"><label>Quick requests</label>
        <div class="quick">
          <button class="btn" type="button" data-preset="checklive"><strong>♥ Check live</strong><small>Simulasi heartbeat perangkat</small></button>
          <button class="btn" type="button" data-preset="reg"><strong>▣ Registration</strong><small>Simulasi registrasi perangkat</small></button>
          <button class="btn" type="button" data-preset="sendlog"><strong>◉ Send log</strong><small>Simulasi log hasil scan wajah</small></button>
          <button class="btn" type="button" data-preset="unknown"><strong>⌘ Unknown command</strong><small>Uji respons command lain</small></button>
        </div>
      </div>
      <div class="field"><label for="json">JSON request body</label><textarea id="json" spellcheck="false">{
  "cmd": "checklive",
  "sn": "AXUH03009366",
  "time": "<?= date('Y-m-d H:i:s') ?>"
}</textarea></div>
      <div class="actions">
        <button class="btn primary" id="send" type="button">➤ Kirim request</button>
        <button class="btn ghost" id="format" type="button">Format JSON</button>
        <button class="btn ghost" id="reset" type="button">Reset</button>
      </div>
      <div class="note"><b>Catatan keamanan:</b> tes <code>sendlog</code> memakai data contoh dan receiver saat ini menjawab <code>access: 0</code> (akses ditolak). Playground ini tidak membuka pintu dan tidak mengirim perintah ke perangkat fisik.</div>
    </section>

    <section class="panel">
      <div class="result-head"><div><h2>02. Hasil pengiriman</h2><p class="hint" style="margin:3px 0 0">Respons aktual dari endpoint PHP kamu.</p></div><span class="status" id="status">Belum dites</span></div>
      <div class="meta">
        <div><small>HTTP status</small><b id="http">—</b></div>
        <div><small>Durasi</small><b id="duration">—</b></div>
        <div><small>Content type</small><b id="ctype">—</b></div>
      </div>
      <div class="response" id="response">Belum ada response.

Pilih salah satu quick request, lalu tekan "Kirim request".</div>
      <div class="actions"><button class="btn green" id="copy" type="button">Salin response</button><button class="btn ghost" id="clear" type="button">Bersihkan hasil</button></div>
      <div class="field"><label>Endpoint perangkat (untuk konfigurasi AiFace)</label><div class="url" id="deviceUrl">URL akan mengikuti alamat server yang kamu gunakan.</div></div>
    </section>
  </div>
  <div class="footer">AiFace BS · HTTP/HTTPS JSON Playground · <?= date('Y') ?></div>
</div>
<script>
const $ = (id) => document.getElementById(id);
const presets = {
  checklive: () => ({cmd:'checklive',sn:$('serial').value.trim(),time:new Date().toISOString().slice(0,19).replace('T',' ')}),
  reg: () => ({cmd:'reg',sn:$('serial').value.trim(),devinfo:{modelname:'AiFace',firmware:'playground-test',curip:'192.168.18.227'}}),
  sendlog: () => ({cmd:'sendlog',sn:$('serial').value.trim(),logindex:1,record:[{name:'Contoh Pengguna',userid:'DEMO001',time:new Date().toISOString().slice(0,19).replace('T',' '),mode:1,verify:'success'}]}),
  unknown: () => ({cmd:'verify',sn:$('serial').value.trim(),userid:'DEMO001'})
};
function showPreset(name){$('json').value=JSON.stringify(presets[name](),null,2);$('status').textContent='Siap dikirim';$('status').className='status';}
document.querySelectorAll('[data-preset]').forEach(btn=>btn.addEventListener('click',()=>showPreset(btn.dataset.preset)));
$('format').addEventListener('click',()=>{try{$('json').value=JSON.stringify(JSON.parse($('json').value),null,2);$('status').textContent='JSON valid';$('status').className='status ok';}catch(e){$('status').textContent='JSON tidak valid';$('status').className='status bad';$('response').textContent='JSON error: '+e.message;}});
$('reset').addEventListener('click',()=>showPreset('checklive'));
$('clear').addEventListener('click',()=>{$('response').textContent='Hasil dibersihkan.';$('http').textContent='—';$('duration').textContent='—';$('ctype').textContent='—';$('status').textContent='Belum dites';$('status').className='status';});
$('copy').addEventListener('click',async()=>{try{await navigator.clipboard.writeText($('response').textContent);$('status').textContent='Response disalin';$('status').className='status ok';}catch(e){$('status').textContent='Gagal menyalin';$('status').className='status bad';}});
$('send').addEventListener('click',async()=>{
  let body;
  try{body=JSON.parse($('json').value);}catch(e){$('status').textContent='JSON tidak valid';$('status').className='status bad';$('response').textContent='Perbaiki JSON terlebih dahulu.\n'+e.message;return;}
  const endpoint=$('endpoint').value.trim();
  if(!endpoint){$('status').textContent='URL kosong';$('status').className='status bad';return;}
  const btn=$('send');btn.disabled=true;btn.textContent='Mengirim…';$('status').textContent='Mengirim request';$('status').className='status wait';$('response').textContent='Menunggu respons dari '+endpoint+' …';
  const started=performance.now();
  try{
    const response=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const raw=await response.text();const elapsed=Math.round(performance.now()-started);
    let pretty=raw;try{pretty=JSON.stringify(JSON.parse(raw),null,2);}catch(e){}
    $('http').textContent=response.status+' '+response.statusText;$('duration').textContent=elapsed+' ms';$('ctype').textContent=response.headers.get('content-type')||'—';
    $('response').textContent=pretty;
    const parsed=(()=>{try{return JSON.parse(raw)}catch(e){return null}})();
    const success=response.ok && parsed && (parsed.result===true || parsed.ret==='checklive');
    $('status').textContent=success?'Response diterima':'Server merespons / perlu diperiksa';$('status').className='status '+(success?'ok':'bad');
  }catch(error){
    $('http').textContent='Tidak terhubung';$('duration').textContent=Math.round(performance.now()-started)+' ms';$('ctype').textContent='—';$('status').textContent='Network error';$('status').className='status bad';
    $('response').textContent='Gagal menghubungi endpoint.\n\n'+error.message+'\n\nPeriksa: server PHP sedang berjalan, URL endpoint benar, dan halaman playground dibuka dari server yang sama.';
  }finally{btn.disabled=false;btn.textContent='➤ Kirim request';}
});
function updateDeviceUrl(){const url=new URL($('endpoint').value||'index.php',window.location.href);$('deviceUrl').textContent=url.origin+url.pathname;}
$('endpoint').addEventListener('input',updateDeviceUrl);updateDeviceUrl();
</script>
</body>
</html>
