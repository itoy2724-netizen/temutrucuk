<?php
/**
 * includes/header.php
 * Referans görsel ile birebir eşleştirildi
 */

if (!isset($aktif_adim)) $aktif_adim = 1;

$adimlar = [
  1 => 'Başvuru',
  2 => 'Randevu Seçimi',
  3 => 'Ödeme',
  4 => 'Sonuç',
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="x-ua-compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Web Tapu İşlemleri - turkiye.gov.tr</title>
  <meta name="description" content="Tapu ve Kadastro Genel Müdürlüğü Web Tapu İşlemleri Randevu Başvurusu">
  <link rel="icon" type="image/png"
    href="https://cdn.e-devlet.gov.tr/themes/izmir/images/favicons/favicon-196x196.1.8.0.png"
    sizes="196x196">

  <!-- Orijinal e-Devlet CSS (lokal kopya) -->
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/base.1.9.5.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/common-pages.1.9.5.css">
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/web-tapu-custom.css">

  <!-- Ana stil dosyası -->
  <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">

  <style>
    /* Mobile input focus auto-zoom prevention */
    @media (max-width: 768px) {
      input, select, textarea {
        font-size: 16px !important;
      }
    }
    /* ===== LOADER GİZLE ===== */
    .ed-loader-wrapper, .ed-loader, .ed-loader-svg-holder { display:none!important; }
    body { visibility:visible!important; opacity:1!important; margin:0; padding:0; }

    /* ===== HEADER ===== */
    #top {
      background: #2e6da4 !important;
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      right: 0 !important;
      z-index: 9999 !important;
      height: 90px !important;
      min-height: 90px !important;
      max-height: 90px !important;
      display: flex !important;
      align-items: stretch !important;
      padding: 0 !important;
      margin: 0 !important;
      box-sizing: border-box !important;
    }
    .headerGroup {
      display: flex !important;
      flex-direction: row !important;
      align-items: stretch !important;
      justify-content: space-between !important;
      width: 100% !important;
      height: 100% !important;
      min-height: 90px !important;
      padding: 0 12px !important;
      box-sizing: border-box !important;
      margin: 0 !important;
    }
    #brandingBlock {
      display: flex !important;
      align-items: center !important;
      margin: 0 !important;
      padding: 0 !important;
      flex: 1 !important;
      height: 100% !important;
      min-height: 90px !important;
    }
    #homeLink {
      display: flex !important;
      align-items: center !important;
      justify-content: flex-start !important;
      text-decoration: none !important;
      height: 100% !important;
      min-height: 90px !important;
      padding: 0 !important;
      overflow: visible !important;
      text-indent: 0 !important;
      background-image: none !important;
      max-height: none !important;
      max-width: none !important;
      width: auto !important;
    }
    #homeLink img {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
      height: 80px !important;
      width: 270px !important;
      max-height: none !important;
      max-width: none !important;
      min-height: 0 !important;
      min-width: 0 !important;
      overflow: visible !important;
      clip: auto !important;
      flex-shrink: 0 !important;
      object-fit: contain !important;
    }
    .resp-menu-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 44px;
      height: 44px;
      cursor: pointer;
      flex-shrink: 0;
      align-self: center !important;
      margin-left: 4px;
    }
    .resp-menu-btn svg {
      display: block;
      fill: white;
    }

    /* ===== DESKTOP HEADER ACTIONS ===== */
    #header-desktop-actions {
      display: none; /* mobile'da gizli */
    }
    #hdr-search-form {
      display: flex;
      align-items: center;
      background: rgba(255,255,255,0.15);
      border-radius: 4px;
      padding: 0 2px 0 10px;
      border: 1px solid rgba(255,255,255,0.3);
      height: 34px;
    }
    #hdr-search-input {
      background: transparent;
      border: none;
      outline: none;
      color: #fff;
      font-size: 13px;
      width: 180px;
      padding: 0;
    }
    #hdr-search-input::placeholder { color: rgba(255,255,255,0.7); }
    #hdr-search-form button {
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 0 8px;
      color: rgba(255,255,255,0.85);
      display: flex;
      align-items: center;
    }
    .hdr-action-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 0 12px;
      height: 34px;
      border-radius: 4px;
      color: rgba(255,255,255,0.9);
      text-decoration: none;
      font-size: 13px;
      white-space: nowrap;
      cursor: pointer;
      transition: background 0.15s;
    }
    .hdr-action-btn:hover {
      background: rgba(255,255,255,0.15);
      color: #fff;
    }
    .hdr-action-chat { gap: 6px; }
    .hdr-action-key { gap: 5px; }
    .hdr-caret { opacity: 0.75; flex-shrink:0; }
    .hdr-login-btn {
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.4);
      font-weight: 600;
      color: #fff !important;
    }
    .hdr-login-btn:hover {
      background: rgba(255,255,255,0.28);
    }

    /* ===== BREADCRUMB ===== */
    .breadcrumbBlock {
      background: #fff;
      border-bottom: 1px solid #dde4ea;
      padding: 10px 14px;
      margin-top: 90px; /* header height */
    }
    ol.breadcrumb {
      display: flex !important;
      flex-direction: row !important;
      flex-wrap: wrap;
      align-items: center;
      list-style: none !important;
      list-style-type: none !important;
      margin: 0 !important;
      padding: 0 !important;
      gap: 0;
      font-size: 13px;
    }
    ol.breadcrumb li {
      display: flex;
      align-items: center;
      list-style: none !important;
    }
    ol.breadcrumb li + li::before {
      content: '›';
      color: #8a9baa;
      font-size: 18px;
      margin: 0 6px;
      line-height: 1;
    }
    ol.breadcrumb a {
      color: #3b77ac;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 4px;
    }
    ol.breadcrumb li:last-child span {
      color: #555;
    }
    .ico-home-svg {
      width: 18px;
      height: 18px;
      display: inline-block;
      vertical-align: middle;
    }

    /* ===== SERVİS BAŞLIĞI ===== */
    .tapu-page-bg {
      background: #ecf0f5;
      padding-bottom: 0;
    }
    .tapu-service-card {
      background: #fff;
      padding: 24px 20px 20px 20px;
      position: relative;
    }
    .tapu-service-identity {
      display: flex;
      flex-direction: row;
      align-items: flex-start;
      gap: 14px;
    }
    .tapu-agency-logo {
      width: 72px;
      height: 72px;
      object-fit: contain;
      flex-shrink: 0;
      display: block;
    }
    .tapu-agency-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
      min-width: 0;
    }
    .tapu-agency-info a.agency-name {
      font-size: 15px;
      color: #3b77ac;
      text-decoration: none;
      font-weight: 400;
      line-height: 1.3;
      display: block;
    }
    .tapu-agency-info .service-name {
      font-size: 20px;
      color: #333;
      font-weight: 400;
      line-height: 1.3;
      display: block;
    }
    /* Yuvarlak floating paylaş butonu */
    .tapu-share-float {
      position: absolute;
      bottom: 20px;
      right: 16px;
    }
    .tapu-share-float button {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      background: #f0f4f8;
      border: 1px solid #d0dce8;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,.12);
      transition: background .15s, box-shadow .15s;
      padding: 0;
    }
    .tapu-share-float button:hover {
      background: #e0eaf3;
      box-shadow: 0 3px 12px rgba(0,0,0,.18);
    }
    .tapu-share-float button svg {
      width: 22px;
      height: 22px;
      fill: #4a6785;
    }
    /* Paylaş dropdown */
    .share-dropdown {
      display: none;
      position: absolute;
      right: 0;
      bottom: 60px;
      width: 185px;
      background: #fff;
      border-radius: 6px;
      box-shadow: 0 4px 16px rgba(0,0,0,.18);
      z-index: 200;
      overflow: hidden;
    }
    .share-dropdown a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 11px 14px;
      font-size: 13px;
      color: #3a4a5a;
      text-decoration: none;
      border-bottom: 1px solid #eee;
    }
    .share-dropdown a:last-child { border-bottom: none; }
    .share-dropdown a:hover { background: #f5f8fb; }
    .share-dropdown a svg { width:16px; height:16px; flex-shrink:0; }

    /* ===== ACCESSIBILITY ===== */
    #accesibilityBlock { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0,0,0,0); }
    #accesibilityBlock a:focus { position:fixed; top:5px; left:5px; background:#fff; color:#333; padding:4px 10px; z-index:99999; width:auto; height:auto; clip:auto; }
  </style>
</head>
<body>

<!-- Loader + Header JS fix -->
<script>
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    // Loader gizle
    document.querySelectorAll('.ed-loader-wrapper,.ed-loader,.ed-loader-svg-holder')
      .forEach(function(e){ e.style.display='none'; e.style.visibility='hidden'; });
    document.body.style.visibility = 'visible';
    document.body.style.opacity = '1';
    // Header top sıfırla
    var h = document.getElementById('top');
    if(h){
      h.style.top = '0';
      h.style.position = 'fixed';
      // Breadcrumb'u header yüksekliği kadar it
      var bc = document.querySelector('.breadcrumbBlock');
      if(bc) bc.style.marginTop = h.offsetHeight + 'px';
    }
    // Logo görünürlüğünü garantile
    var logo = document.getElementById('govtr-logo-img');
    if(logo){
      logo.style.setProperty('display','block','important');
      logo.style.setProperty('visibility','visible','important');
      logo.style.setProperty('opacity','1','important');
      logo.style.setProperty('overflow','visible','important');
    }
  });
  // Sürekli kontrol (JS top:Xpx inject ederse düzelt)
  setInterval(function(){
    var h = document.getElementById('top');
    if(h && h.style.top && h.style.top !== '0px'){
      h.style.top = '0';
      h.style.position = 'fixed';
    }
  }, 150);
})();
</script>

<!-- ===== HEADER ===== -->
<header id="top">
  <div id="accesibilityBlock">
    <a href="#pageContentBlock" accesskey="1">İçeriğe Git</a>
    <a href="<?= BASE_PATH ?>/" accesskey="2">Ana Sayfa</a>
  </div>
  <div class="headerGroup">
    <h1 id="brandingBlock">
      <a id="homeLink" href="<?= BASE_PATH ?>/" title="Ana Sayfa">
        <img src="<?= BASE_PATH ?>/assets/images/govtr-logo.png"
             id="govtr-logo-img"
             alt="türkiye.gov.tr - Devletin Kapısı">
      </a>
    </h1>
    <!-- Masaüstü sağ aksiyonlar (≥1024px'de görünür) -->
    <div id="header-desktop-actions">
      <!-- Hızlı Çözüm -->
      <a href="#" class="hdr-action-btn hdr-action-chat" title="Hızlı Çözüm Merkezi">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>Hızlı Çözüm</span>
      </a>
      <!-- Anahtar / Şifre -->
      <div class="hdr-action-btn hdr-action-key" title="Şifre İşlemleri">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
        <svg class="hdr-caret" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 15l-8-8h16z"/></svg>
      </div>
      <!-- Arama -->
      <form id="hdr-search-form" action="#" method="get" role="search">
        <input type="search" id="hdr-search-input" name="q" placeholder="Nasıl yardım edebilirim?" autocomplete="off" aria-label="Arama">
        <button type="submit" aria-label="Ara">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>
      </form>
      <!-- Giriş Yap -->
      <a href="#" class="hdr-action-btn hdr-login-btn" title="Giriş Yap">
        <span>Giriş Yap</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      </a>
    </div>
    <!-- Hamburger (mobile'da görünür) -->
    <div class="resp-menu-btn" aria-label="Menü" role="button" tabindex="0">
      <svg viewBox="0 0 24 24" width="28" height="28"><rect y="4" width="24" height="2.5" rx="1.5" fill="white"/><rect y="10.75" width="24" height="2.5" rx="1.5" fill="white"/><rect y="17.5" width="24" height="2.5" rx="1.5" fill="white"/></svg>
    </div>
  </div>
</header>

<!-- ===== BREADCRUMB ===== -->
<div class="breadcrumbBlock">
  <ol class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
      <a href="<?= BASE_PATH ?>/" itemprop="item">
        <svg class="ico-home-svg" viewBox="0 0 24 24" fill="none" stroke="#3b77ac" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><polyline points="9 21 9 12 15 12 15 21"/></svg>
        <span itemprop="name" class="sr-only">Ana Sayfa</span>
      </a>
      <meta itemprop="position" content="1">
    </li>
    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
      <a href="#" itemprop="item">
        <span itemprop="name">Tapu ve Kadastro Genel Müdürlüğü</span>
      </a>
      <meta itemprop="position" content="2">
    </li>
    <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
      <span itemprop="name">Web Tapu İşlemleri</span>
      <meta itemprop="position" content="3">
    </li>
  </ol>
</div>

<!-- ===== MAIN ===== -->
<main>
  <div class="tapu-page-bg">
    <section id="pageContentBlock" class="themed">
      <div id="serviceBlock">

        <!-- Servis Başlığı Kartı -->
        <div class="tapu-service-card">
          <div class="tapu-service-identity">
            <img class="tapu-agency-logo"
              src="<?= BASE_PATH ?>/assets/images/tkgm-logo.webp"
              alt="Tapu ve Kadastro Genel Müdürlüğü">
            <div class="tapu-agency-info">
              <a href="#" class="agency-name">Tapu ve Kadastro Genel Müdürlüğü</a>
              <span class="service-name">Web Tapu İşlemleri</span>
            </div>
          </div>

          <!-- Yuvarlak Paylaş Butonu (sağ-alt floating) -->
          <div class="tapu-share-float">
            <button id="share_button"
              aria-expanded="false"
              aria-haspopup="true"
              aria-controls="share_dropdown"
              onclick="toggleShare(event)"
              title="Paylaş">
              <!-- Share-2 ikonu (Feather Icons stilinde) -->
              <svg viewBox="0 0 24 24" fill="none" stroke="#4a6785" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
              </svg>
            </button>
            <div class="share-dropdown" id="share_dropdown">
              <a href="#" onclick="shareTwitter();return false;">
                <svg viewBox="0 0 24 24" fill="#1da1f2"><path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 01-1.93.07 4.28 4.28 0 004 2.98 8.521 8.521 0 01-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/></svg>
                Twitter'da Paylaş
              </a>
              <a href="#" onclick="shareFacebook();return false;">
                <svg viewBox="0 0 24 24" fill="#1877f2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                Facebook'ta Paylaş
              </a>
              <a href="#" onclick="copyLink();return false;">
                <svg viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                Linki Kopyala
              </a>
            </div>
          </div>
        </div><!-- /.tapu-service-card -->

        <!-- Share JS -->
        <script>
        function toggleShare(e) {
          e.stopPropagation();
          var menu = document.getElementById('share_dropdown');
          var btn  = document.getElementById('share_button');
          var open = menu.style.display === 'block';
          menu.style.display = open ? 'none' : 'block';
          btn.setAttribute('aria-expanded', !open);
        }
        document.addEventListener('click', function(){
          var m = document.getElementById('share_dropdown');
          if(m) m.style.display = 'none';
        });
        function shareTwitter() {
          window.open('https://twitter.com/intent/tweet?url='+encodeURIComponent(location.href)+'&text='+encodeURIComponent(document.title),'_blank');
        }
        function shareFacebook() {
          window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(location.href),'_blank');
        }
        function copyLink() {
          navigator.clipboard.writeText(location.href).then(function(){alert('Link kopyalandı!');});
        }
        </script>

        <!-- ===== ADIM ÇUBUĞU ===== -->
        <div class="webtapu-flow">
          <nav class="webtapu-steps" aria-label="Başvuru adımları" role="navigation">
            <?php foreach ($adimlar as $no => $isim):
              $cls = '';
              if ($no < $aktif_adim)       $cls = 'is-completed';
              elseif ($no === $aktif_adim) $cls = 'is-active';
              else                          $cls = 'is-disabled';
              $hasConnector = $no < count($adimlar);
            ?>
            <div class="webtapu-step-wrapper">
              <?php if ($no === $aktif_adim): ?>
                <a href="#" class="webtapu-step <?= $cls ?>" aria-current="step">
                  <span class="webtapu-step-number"><?= $no ?></span>
                  <span class="webtapu-step-label"><?= $isim ?></span>
                </a>
              <?php elseif ($cls === 'is-completed'): ?>
                <span class="webtapu-step <?= $cls ?>" aria-current="false">
                  <span class="webtapu-step-number">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                      fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                  </span>
                  <span class="webtapu-step-label"><?= $isim ?></span>
                </span>
              <?php else: ?>
                <span class="webtapu-step <?= $cls ?>" aria-current="false">
                  <span class="webtapu-step-number"><?= $no ?></span>
                  <span class="webtapu-step-label"><?= $isim ?></span>
                </span>
              <?php endif; ?>
              <?php if ($hasConnector): ?>
                <div class="webtapu-step-connector <?= ($no < $aktif_adim) ? 'is-completed' : '' ?>"></div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </nav>
