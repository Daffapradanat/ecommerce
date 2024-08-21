<!DOCTYPE html>
<html>
<head>
    <title>Invoice for Order #{{ $order->order_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .invoice-items {
            margin-bottom: 20px;
        }

        .invoice-items table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-items th, .invoice-items td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .invoice-total {
            display: flex;
            justify-content: flex-end;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .invoice-footer {
            font-size: 0.9em;
            color: #7f8c8d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1>Invoice for Order #{{ $order->order_id }}</h1>
        <p>Amount Due: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
    </div>

    <div class="invoice-details">
        <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
    </div>

    <div class="invoice-items">
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderItems as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->product_description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->product_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="invoice-total">
        <p>Sub Total: Rp {{ number_format($order->orderItems->sum('price'), 0, ',', '.') }}</p>
        <p>Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
    </div>

    <div class="invoice-footer">
        {{-- <p>Payment will be taken automatically from the credit card Visa-6566 on {{ $order->created_at->format('m/d/Y') }}. To change or pay with a
            different payment method, please login at <a href="https://my.racknerd.com/viewinvoice.php?id=52335805">this
                link</a> and click Pay Now, then follow the instructions on screen.</p>
        <hr> --}}
        <p>Thanks!</p>
        <p>Your ecommerce.com Team</p>
        <p>Introducing Infrastructure Stability</p>
        <p><a href="https://intern-daffa.arfani.my.id">https://intern-daffa.arfani.my.id</a></p>
        <p>Dedicated Servers, Private Cloud, DRaaS, Colocation & VPS</p>
        <p>Technical Support: <a href="mailto:support@ecommerce.com">support@ecommerce.com</a></p>
        <p>Sales Inquiries: <a href="mailto:sales@ecommerce.com">sales@ecommerce.com</a></p>
        <p><a href="https://intern-daffa.arfani.my.id">Visit our website</a> | <a href="#">Log in to your account</a> | <a href="#">Get support</a></p>
    </div>
</body>
</html>
