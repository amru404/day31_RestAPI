<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'orders_id',
        'total_harga',
        'status_pembayaran',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class,'orders_id');
    }

    
}