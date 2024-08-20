@component('mail::message')
# Pesanan Baru #{{ $order->order_id }}

Terima kasih atas pesanan Anda, {{ $order->buyer->name }}.

Rincian pesanan Anda:

**ID Pesanan:** {{ $order->order_id }}
**Total Harga:** Rp {{ number_format($order->total_price, 0, ',', '.') }}

Invoice lengkap terlampir dalam email ini.

@component('mail::button', ['url' => route('orders.public-invoice', ['order' => $order->id, 'token' => $order->generateInvoiceToken()])])
Lihat Invoice
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent