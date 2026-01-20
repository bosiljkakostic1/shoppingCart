<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    protected $table = 'shoppingCarts';

    protected $fillable = [
        'userId',
        'sum',
        'state',
        'updatedAt',
    ];

    protected $casts = [
        'sum' => 'decimal:2',
        'updatedAt' => 'datetime',
    ];

    public $timestamps = false;

    protected $dates = ['updatedAt'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function products()
    {
        return $this->hasMany(ShoppingCartProduct::class, 'shoppingCartId');
    }
}
