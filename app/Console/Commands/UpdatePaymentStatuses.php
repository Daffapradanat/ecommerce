<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\PaymentGateway; // Assume you have a payment gateway service

class UpdatePaymentStatuses extends Command
{
    protected $signature = 'orders:update-payment-statuses';
    protected $description = 'Update payment statuses for pending orders';

    public function handle(PaymentGateway $paymentGateway)
    {
        $pendingOrders = Order::where('payment_status', 'pending')->get();

        foreach ($pendingOrders as $order) {
            $status = $paymentGateway->checkPaymentStatus($order->id);

            if ($status === 'paid') {
                $order->payment_status = 'paid';
                $order->save();
                $this->info("Order #{$order->id} status updated to paid.");
            } elseif ($status === 'failed') {
                $order->payment_status = 'failed';
                $order->save();
                $this->info("Order #{$order->id} status updated to failed.");
            }
        }

        $this->info('Payment status update completed.');
    }
}
