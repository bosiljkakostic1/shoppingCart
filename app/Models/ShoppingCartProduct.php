<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingCartProduct extends Model
{
    protected $table = 'shoppingCartProducts';

    protected $fillable = [
        'shoppingCartId',
        'userId',
        'productId',
        'quantity',
        'updatedAt',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'updatedAt' => 'datetime',
    ];

    public $timestamps = false;

    protected $dates = ['updatedAt'];

    public function shoppingCart()
    {
        return $this->belongsTo(ShoppingCart::class, 'shoppingCartId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }
}
