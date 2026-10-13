<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$allTables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$tableKey = "Tables_in_" . $dbName;

foreach ($allTables as $t) {
    $tableName = $t->$tableKey;
    $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");
    foreach ($columns as $col) {
        if (preg_match('/quantity|stock|qty/i', $col->Field)) {
            echo "{$tableName}.{$col->Field} ({$col->Type})\n";
        }
    }
}
