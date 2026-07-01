<?php
header("Content-Type: text/plain");
echo "Vercel Build Diagnosis:\n";
echo "=======================\n";
echo "Current File Path: " . __FILE__ . "\n";
echo "Current Working Directory: " . getcwd() . "\n";
echo "\nFiles in api/ directory:\n";
print_r(scandir(__DIR__));
echo "\nFiles in project root directory:\n";
print_r(scandir(dirname(__DIR__)));
?>
