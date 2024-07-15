<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['buyer_id', 'status', 'total_price'];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
