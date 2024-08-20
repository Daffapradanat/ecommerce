<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Midtrans\Transaction;


class Order extends Model implements ShouldBroadcast
{
    use HasFactory, Dispatchable, InteractsWithSockets, SerializesModels;

    protected $fillable = [
        'buyer_id', 'order_id', 'email', 'phone', 'city', 'address', 'postal_code', 'total_price', 'payment_token', 'payment_method', 'payment_status'
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function generateInvoiceToken()
    {
        return hash('sha256', $this->id . $this->order_id . $this->created_at);
    }

    public function setStatusSuccess()
    {
        $this->update([
            // 'status' => 'completed',
            'payment_status' => 'paid'
        ]);
    }

    public function setStatusPending()
    {
        $this->update([
            // 'status' => 'pending',
            'payment_status' => 'pending'
        ]);
    }

    public function setStatusFailed()
    {
        $this->update([
            // 'status' => 'cancelled',
            'payment_status' => 'failed'
        ]);
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
