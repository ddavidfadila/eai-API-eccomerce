<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['userId', 'productId', 'qty', 'totalPrice'];

    public function product(){
        return $this->belongsTo(Product::class, 'productId');
    }
}
