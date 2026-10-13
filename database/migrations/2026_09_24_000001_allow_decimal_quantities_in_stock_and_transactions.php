<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Items table: stock, min_stock, conversion_factor
        DB::statement("ALTER TABLE items 
            MODIFY COLUMN stock DECIMAL(15, 3) NOT NULL DEFAULT 0,
            MODIFY COLUMN min_stock DECIMAL(15, 3) NOT NULL DEFAULT 0,
            MODIFY COLUMN conversion_factor DECIMAL(15, 3) NOT NULL DEFAULT 1");

        // 2. Material Ins table: quantity, wholesale_qty
        DB::statement("ALTER TABLE material_ins 
            MODIFY COLUMN quantity DECIMAL(15, 3) NOT NULL,
            MODIFY COLUMN wholesale_qty DECIMAL(15, 3) NULL DEFAULT NULL");

        // 3. Material Outs table: quantity, wholesale_qty
        DB::statement("ALTER TABLE material_outs 
            MODIFY COLUMN quantity DECIMAL(15, 3) NOT NULL,
            MODIFY COLUMN wholesale_qty DECIMAL(15, 3) NULL DEFAULT NULL");

        // 4. Stock Logs table: quantity
        DB::statement("ALTER TABLE stock_logs 
            MODIFY COLUMN quantity DECIMAL(15, 3) NOT NULL");

        // 5. Material Rejects table: quantity
        DB::statement("ALTER TABLE material_rejects 
            MODIFY COLUMN quantity DECIMAL(15, 3) NOT NULL DEFAULT 1");

        // 6. Daily Stock Opnames table: initial_stock, final_stock, used_qty, system_out_qty, difference
        DB::statement("ALTER TABLE daily_stock_opnames 
            MODIFY COLUMN initial_stock DECIMAL(15, 3) NOT NULL DEFAULT 0,
            MODIFY COLUMN final_stock DECIMAL(15, 3) NULL DEFAULT NULL,
            MODIFY COLUMN used_qty DECIMAL(15, 3) NOT NULL DEFAULT 0,
            MODIFY COLUMN system_out_qty DECIMAL(15, 3) NOT NULL DEFAULT 0,
            MODIFY COLUMN difference DECIMAL(15, 3) NOT NULL DEFAULT 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE daily_stock_opnames 
            MODIFY COLUMN initial_stock INT NOT NULL DEFAULT 0,
            MODIFY COLUMN final_stock INT NULL DEFAULT NULL,
            MODIFY COLUMN used_qty INT NOT NULL DEFAULT 0,
            MODIFY COLUMN system_out_qty INT NOT NULL DEFAULT 0,
            MODIFY COLUMN difference INT NOT NULL DEFAULT 0");

        DB::statement("ALTER TABLE material_rejects 
            MODIFY COLUMN quantity INT NOT NULL DEFAULT 1");

        DB::statement("ALTER TABLE stock_logs 
            MODIFY COLUMN quantity INT NOT NULL");

        DB::statement("ALTER TABLE material_outs 
            MODIFY COLUMN quantity INT NOT NULL,
            MODIFY COLUMN wholesale_qty INT NULL DEFAULT NULL");

        DB::statement("ALTER TABLE material_ins 
            MODIFY COLUMN quantity INT NOT NULL,
            MODIFY COLUMN wholesale_qty INT NULL DEFAULT NULL");

        DB::statement("ALTER TABLE items 
            MODIFY COLUMN stock INT NOT NULL DEFAULT 0,
            MODIFY COLUMN min_stock INT NOT NULL DEFAULT 0,
            MODIFY COLUMN conversion_factor INT UNSIGNED NOT NULL DEFAULT 1");
    }
};
