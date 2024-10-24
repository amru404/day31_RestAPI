<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'products_id',
        'qty',
        'total_harga',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class,'products_id');
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }
}