<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'category', 'price', 'qty', 'photo'];

    public function cart(){
        return $this->hasMany(Cart::class, 'productId');
    }
}
