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
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h2 {
            color: #2980b9;
            font-size: 1.2em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #2c3e50;
        }
        .total {
            font-size: 1.2em;
            font-weight: bold;
            text-align: right;
            color: #2c3e50;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('path/to/your/logo.png') }}" alt="Company Logo" class="logo">
        <h1>Invoice for Order #{{ $order->order_id }}</h1>
    </div>

    <div class="info-section">
        <h2>Buyer Information:</h2>
        <p><strong>Name:</strong> {{ $order->buyer->name }}</p>
        <p><strong>Email:</strong> {{ $order->buyer->email }}</p>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
    </div>

    <div class="info-section">
        <h2>Order Details:</h2>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="total">
        <p>Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
    </div>

    <div class="footer">
        <p>Thank you for your order!</p>
        <p>If you have any questions, please contact our customer support.</p>
    </div>
</body>
</html>
