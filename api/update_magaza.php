<?php
include("gmypanel/Connection.php");
try {
    $db->query("UPDATE site SET magaza_name = 'TAPU' WHERE id = '1'");
    echo "BASARILI: magaza_name degeri TAPU olarak guncellendi!\n";
} catch (Exception $e) {
    echo "HATA: " . $e->getMessage() . "\n";
}
?>
