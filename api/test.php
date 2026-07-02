<?php
header("Content-Type: text/plain");
echo "=== VERCEL DIAGNOSIS ===\n";
echo "Current File (__FILE__): " . __FILE__ . "\n";
echo "Current Dir  (__DIR__):  " . __DIR__ . "\n";
echo "Working Dir  (getcwd()): " . getcwd() . "\n\n";

echo "=== WAITING.PHP FIRST 10 LINES ===\n";
$waitingFile = __DIR__ . "/Waiting.php";
if (file_exists($waitingFile)) {
    $lines = file($waitingFile);
    for ($i = 0; $i < min(10, count($lines)); $i++) {
        echo ($i+1) . ": " . $lines[$i];
    }
} else {
    echo "NOT FOUND: $waitingFile\n";
}

echo "\n=== GMYPANEL DIR EXISTS? ===\n";
$gmypanel = __DIR__ . "/gmypanel";
echo file_exists($gmypanel) ? "YES: $gmypanel\n" : "NO: $gmypanel\n";

echo "\n=== FILES IN api/ ===\n";
print_r(scandir(__DIR__));
?>
