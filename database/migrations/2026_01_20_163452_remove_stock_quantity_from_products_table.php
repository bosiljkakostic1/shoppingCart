<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column exists before dropping (SQLite compatibility)
        if (Schema::hasColumn('products', 'stockQuantity')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('stockQuantity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't support adding NOT NULL columns without default
        // This migration is one-way in SQLite
        if (config('database.default') !== 'sqlite') {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('stockQuantity')->default(0)->after('price');
            });
        }
    }
};
