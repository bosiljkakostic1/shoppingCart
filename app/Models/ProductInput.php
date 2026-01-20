<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInput extends Model
{
    protected $table = 'productInputs';

    protected $fillable = [
        'productId',
        'addedQuantity',
        'createdAt',
    ];

    protected $casts = [
        'addedQuantity' => 'integer',
        'createdAt' => 'datetime',
    ];

    public $timestamps = false;

    protected $dates = ['createdAt'];

    protected static function booted()
    {
        static::created(function ($productInput) {
            // Check for low stock after adding product input
            $productInput->product->checkLowStock();
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }
}
