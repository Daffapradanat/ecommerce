@component('mail::message')
# Invoice Pesanan #{{ $order->order_id }}

Terima kasih atas pembelian Anda, {{ $order->buyer->name }}.

## Detail Pesanan

**ID Pesanan:** {{ $order->order_id }}
**Tanggal Pemesanan:** {{ $order->created_at->format('d F Y') }}

## Rincian Pembelian

@foreach ($order->orderItems as $item)
- {{ $item->name }} - Rp {{ number_format($item->price, 0, ',', '.') }} x {{ $item->quantity }} = Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
@endforeach

Sub Total: Rp {{ number_format($order->orderItems->sum('price'), 0, ',', '.') }}
**Total Harga:** Rp {{ number_format($order->total_price, 0, ',', '.') }}

Terima kasih telah memilih kami. Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami melalui email di support@commerce.com atau telepon di +62 123 4567.

@component('mail::button', ['url' => route('orders.public-invoice', ['order' => $order->id, 'token' => $order->generateInvoiceToken()])])
Lihat Invoice
@endcomponent

Salam,<br>
{{ config('app.name') }}

---

Ikuti kami di sosial media:
- [Facebook](https://www.facebook.com/example)
- [Twitter](https://twitter.com/example)
- [Instagram](https://www.instagram.com/example)
@endcomponent
