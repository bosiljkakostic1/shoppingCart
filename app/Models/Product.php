<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'price',
        'minStockQuantity',
        'unit',
        'updatedAt',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'minStockQuantity' => 'integer',
        'updatedAt' => 'datetime',
    ];

    public $timestamps = false;

    protected $dates = ['updatedAt'];

    public function shoppingCartProducts()
    {
        return $this->hasMany(ShoppingCartProduct::class, 'productId');
    }

    public function productInputs()
    {
        return $this->hasMany(ProductInput::class, 'productId');
    }

    /**
     * Calculate available quantity based on product inputs and cart items
     */
    public function getAvailableQuantity(): int
    {
        // Sum of all added quantities from product_inputs
        $totalAdded = $this->productInputs()->sum('addedQuantity');
        
        // Sum of all quantities in all shopping carts (active, ordered, etc.)
        $totalInCarts = ShoppingCartProduct::where('productId', $this->id)
            ->sum('quantity');
        
        return max(0, $totalAdded - $totalInCarts);
    }

    /**
     * Check if product is low on stock and dispatch notification if needed
     */
    public function checkLowStock(): void
    {
        $availableQuantity = $this->getAvailableQuantity();
        
        // Check if stock is equal to or less than minimum stock quantity
        if ($availableQuantity <= $this->minStockQuantity) {
            // Dispatch low stock notification job
            \App\Jobs\LowStockNotificationJob::dispatch($this->refresh());
        }
    }
}
