<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Order extends Model implements ShouldBroadcast
{
    use HasFactory, Dispatchable, InteractsWithSockets, SerializesModels;

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
        $this->payment_status = 'pending';
        $this->save();
    }

    public function setStatusSuccess()
    {
        $this->status = 'processing';
        $this->payment_status = 'paid';
        $this->save();
    }

    public function setStatusFailed()
    {
        $this->status = 'cancelled';
        $this->payment_status = 'failed';
        $this->save();
    }

    public function updatePaymentStatus($status)
    {
        $this->payment_status = $status;
        $this->save();
    }

    public function broadcastOn()
    {
        return new Channel('order-status');
    }

    public function broadcastAs()
    {
        return 'payment-updated';
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->id,
            'status' => $this->payment_status,
        ];
    }
}
