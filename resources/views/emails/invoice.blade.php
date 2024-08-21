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
            align-items: center;
            border-bottom: 1px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .invoice-header img {
            max-height: 60px;
            margin-right: 20px;
        }

        .invoice-header h1 {
            margin: 0;
        }

        .invoice-details {
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

        .invoice-footer p {
            margin: 0;
        }

        .invoice-footer a {
            color: #3498db;
            text-decoration: none;
        }

        .invoice-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <img src="{{ public_path('Asset/Logo.png') }}" alt="Logo">
        <div>
            <h1>Invoice for Order #{{ $order->order_id }}</h1>
            <p>Amount Due: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
        </div>
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
        <p>Sub Total: Rp {{ number_format($order->orderItems->sum(function($item) { return $item->price; }), 0, ',', '.') }}</p>
        <p>Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
    </div>

    <div class="invoice-footer">
        <div class="invoice-footer">
            <p style="margin: 0;">Thanks!</p>
            <p style="margin: 0;">Your ecommerce.com Team</p>
            <p style="margin: 0;">Introducing Infrastructure Stability</p>
            <p style="margin: 0;"><a href="https://intern-daffa.arfani.my.id">https://intern-daffa.arfani.my.id</a></p>
            <p style="margin: 0;">Dedicated Servers, Private Cloud, DRaaS, Colocation & VPS</p>
            <p style="margin: 0;">Technical Support: <a href="mailto:support@ecommerce.com">support@ecommerce.com</a></p>
            <p style="margin: 0;">Sales Inquiries: <a href="mailto:sales@ecommerce.com">sales@ecommerce.com</a></p>
            <p style="margin: 0;"><a href="https://intern-daffa.arfani.my.id">Visit our website</a> | <a href="#">Log in to your account</a> | <a href="#">Get support</a></p>
        </div>
    </div>
</body>
</html>
