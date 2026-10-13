<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = ['items', 'material_ins', 'material_outs', 'stock_logs', 'material_rejects', 'stock_opnames'];
foreach ($tables as $table) {
    if (!Schema::hasTable($table)) continue;
    echo "=== Table: {$table} ===\n";
    $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
    foreach ($columns as $col) {
        if (preg_match('/quantity|stock|qty/i', $col->Field)) {
            echo "  - {$col->Field}: {$col->Type} (Null: {$col->Null}, Default: {$col->Default})\n";
        }
    }
}
