<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$files = [
    'app/Livewire/ItemTableComponent.php',
    'app/Livewire/MaterialInComponent.php',
    'app/Livewire/MaterialOutComponent.php',
    'app/Livewire/StockOpnameComponent.php',
    'app/Models/Item.php',
    'app/Models/MaterialIn.php',
    'app/Models/MaterialOut.php',
    'app/Models/StockLog.php',
    'app/Models/MaterialReject.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $lines = file($file);
    foreach ($lines as $num => $line) {
        if (preg_match('/(integer|min:1|\(int\)|increment|decrement|number_format|min_stock)/i', $line) && preg_match('/(quantity|stock|qty)/i', $line)) {
            echo "{$file}:" . ($num + 1) . " -> " . trim($line) . "\n";
        }
    }
}
