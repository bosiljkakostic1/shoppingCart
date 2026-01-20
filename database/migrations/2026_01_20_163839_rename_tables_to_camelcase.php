<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename tables if they exist (for existing databases)
        if (Schema::hasTable('shopping_carts') && !Schema::hasTable('shoppingCarts')) {
            DB::statement('ALTER TABLE "shopping_carts" RENAME TO "shoppingCarts"');
        }
        
        if (Schema::hasTable('shopping_cart_products') && !Schema::hasTable('shoppingCartProducts')) {
            DB::statement('ALTER TABLE "shopping_cart_products" RENAME TO "shoppingCartProducts"');
            
            // Update foreign key constraint name if needed
            DB::statement('PRAGMA foreign_key_check');
        }
        
        if (Schema::hasTable('product_inputs') && !Schema::hasTable('productInputs')) {
            DB::statement('ALTER TABLE "product_inputs" RENAME TO "productInputs"');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shoppingCarts') && !Schema::hasTable('shopping_carts')) {
            DB::statement('ALTER TABLE "shoppingCarts" RENAME TO "shopping_carts"');
        }
        
        if (Schema::hasTable('shoppingCartProducts') && !Schema::hasTable('shopping_cart_products')) {
            DB::statement('ALTER TABLE "shoppingCartProducts" RENAME TO "shopping_cart_products"');
        }
        
        if (Schema::hasTable('productInputs') && !Schema::hasTable('product_inputs')) {
            DB::statement('ALTER TABLE "productInputs" RENAME TO "product_inputs"');
        }
    }
};
