<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/db.php';

echo "=== VERİTABANI BAĞLANTI TESTİ ===\n\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (empty(DB_PASS) ? "Boş" : "Dolu (Uzunluk: " . strlen(DB_PASS) . ")") . "\n\n";

try {
    echo "Bağlantı kuruluyor...\n";
    $start = microtime(true);
    
    // Doğrudan bağlantı testi (db() fonksiyonu çağrısı)
    $pdo = db();
    
    $end = microtime(true);
    echo "BAĞLANTI BAŞARILI! (Süre: " . round($end - $start, 3) . " saniye)\n\n";
    
    echo "Tablolar listeleniyor:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "- Hiç tablo bulunamadı.\n";
    } else {
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    }
} catch (Exception $e) {
    echo "\n❌ BAĞLANTI HATASI!\n";
    echo "Hata Mesajı: " . $e->getMessage() . "\n";
    echo "Hata Kodu: " . $e->getCode() . "\n";
}
?>
