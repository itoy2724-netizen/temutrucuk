<?php
session_start();
ob_start();
date_default_timezone_set('Europe/Istanbul');
include(__DIR__ . "/Connection.php");
include(__DIR__ . "/Core/getRealIPAdress.php");
include(__DIR__ . "/Core/browserDetect.php");
$ip = getUserIP();
$pass_st = $db->query("SELECT * FROM site WHERE id = '1'")->fetch(PDO::FETCH_ASSOC);
if (!isset($_SESSION["login"])) {
    header('Location:index.php');
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/main.css">
    <link rel="icon" src="https://cdn.discordapp.com/emojis/840228856215633950.png?size=4096">
</head>

<body>
    <div class="flex justify-center h-screen items-center">
        <form action="" method="POST">
            <div class="bg-second w-80 md:w-96 h-3/12 px-6 py-4 rounded-xl">
                <div class="flex py-4 justify-center items-center">
                    <img class="w-16" src="https://cdn.discordapp.com/emojis/840228856215633950.png?size=4096" alt="">
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl text-white">Hoş geldiniz!</h1>
                    <p class="text-sm text-gray-400 mt-2">Yönetim panelinin şifresini değiştirmek için aşağıya yazabilirsiniz.</p>
                </div>
                <div class="flex flex-col space-y-3 pt-3">
                    <input name="password" id="password" type="password"
                        class="py-2 text-sm text-white bg-main rounded-md pl-2 focus:outline-none" placeholder="Yeni Şifre!"
                        autocomplete="off" required>
                </div>
                <?php if(isset($_POST['password'])){
    $password = $_POST['password'];
$db->query("UPDATE site SET pass = '{$password}' WHERE id = '1'");
    header('Location:index.php');
}?>
                <div class="flex flex-col space-y-2 w-full mt-5 text-gray-400">
                    <button type="submit" class="rounded-md bg-main text-md hover:text-gray-300 duration-150 py-1">Giriş
                        Yap</button>
                </div>
            </div>
        </form>
    </div>
    </div>
</body>

</html>
<?php