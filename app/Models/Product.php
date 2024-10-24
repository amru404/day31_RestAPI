<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DataTables;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [
        'kode_product',
        'nama',
        'harga',
        'stock',
    ];

    public function order()
    {
        return $this->hasMany(Order::class);
    }
    
}