<!DOCTYPE html>
<html>
<head>
    <title>Invoice for Order #{{ $order->order_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Invoice for Order #{{ $order->order_id }}</h1>

    <h2>Buyer Information:</h2>
    <p>Name: {{ $order->buyer->name }}</p>
    <p>Email: {{ $order->buyer->email }}</p>

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

    <h3>Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</h3>
</body>
</html>