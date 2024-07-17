<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['buyer_id', 'status', 'email', 'phone', 'city', 'address', 'postal_code','total_price', 'payment_token', 'payment_method', 'payment_status'];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function setStatusPending()
    {
        $this->status = 'pending';
        $this->save();
    }

    public function setStatusSuccess()
    {
        $this->status = 'success';
        $this->save();
    }

    public function setStatusFailed()
    {
        $this->status = 'failed';
        $this->save();
    }

    public function setStatusExpired()
    {
        $this->status = 'expired';
        $this->save();
    }
}
