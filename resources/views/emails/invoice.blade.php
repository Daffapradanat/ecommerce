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

        h1 {
            color: #2c3e50;
            border-bottom: 1px solid #3498db;
            padding-bottom: 10px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .total {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #7f8c8d;
        }
    </style>
</head>

<body>
    <h1>Invoice for Order #{{ $order->order_id }}</h1>

    <div class="info-section">
        <p><strong>Amount Due:</strong> Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
    </div>

    <div class="info-section">
        <h2>Invoice Items</h2>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
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

    <div class="total">
        <p>Sub Total: Rp {{ number_format($order->orderItems->sum('price'), 0, ',', '.') }}</p>
        <p>Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
    </div>

    <div class="info-section">
        <p>Payment will be taken automatically from the credit card Visa-6566 on {{ $order->created_at->format('m/d/Y') }}. To change or pay with a
            different payment method, please login at <a href="https://my.racknerd.com/viewinvoice.php?id=52335805">this
                link</a> and click Pay Now, then follow the instructions on screen.</p>
    </div>

    <div class="footer">
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
