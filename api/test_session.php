<?php
require_once __DIR__ . '/db.php';

echo "=== SESSION TEST ===\n\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n\n";

if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 1;
    echo "Test counter initialized to 1.\n";
} else {
    $_SESSION['test_counter']++;
    echo "Test counter incremented to: " . $_SESSION['test_counter'] . "\n";
}

echo "\nSession contents:\n";
print_r($_SESSION);

echo "\nChecking database tapu_sessions table content:\n";
try {
    $stmt = db()->prepare("SELECT * FROM tapu_sessions WHERE id = ?");
    $stmt->execute([session_id()]);
    $row = $stmt->fetch();
    if ($row) {
        echo "Found session in DB! Last access: " . $row['access'] . "\n";
        echo "Data length: " . strlen($row['data']) . " bytes\n";
    } else {
        echo "Session NOT found in DB!\n";
    }
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
?>
