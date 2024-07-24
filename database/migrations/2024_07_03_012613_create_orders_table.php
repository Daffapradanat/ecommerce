<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('buyer_id')->constrained()->onDelete('cascade');
            $table->string('payment_token')->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'awaiting_payment', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->decimal('total_price', 12, 2);
            $table->string('email');
            $table->string('phone');
            $table->string('city');
            $table->text('address');
            $table->string('postal_code');
            $table->string('snap_token')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
