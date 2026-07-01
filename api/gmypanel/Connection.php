<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$servername = getenv('DB_HOST') ?: "localhost";
$username   = getenv('DB_USER') ?: "root1";
$password   = getenv('DB_PASSWORD') ?: "J0*Bzlm4Fd%umm1i";
$dbname     = getenv('DB_NAME') ?: "gmy_os33ym";

try {
     $db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
     $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $db->exec("SET NAMES 'utf8'; SET CHARSET 'utf8'");
    }
catch(PDOException $e)
    {
     die("SQL baglanti hatasi: " . $e->getMessage());
    }

?>