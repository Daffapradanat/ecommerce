<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notification' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .content {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .content p {
            margin: 10px 0;
        }

        .btn {
            display: inline-block;
            background-color: #3498db;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #7f8c8d;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>{{ $subject ?? 'Notification' }}</h1>

        <div class="content">
            @if (isset($notification->data['message']))
                <p>{{ $notification->data['message'] }}</p>
            @endif

            @if (isset($notification->data['order_id']))
                <p><strong>Order ID:</strong> {{ $notification->data['order_id'] }}</p>
            @endif

            @if (isset($notification->data['product_name']))
                <p><strong>Product:</strong> {{ $notification->data['product_name'] }}</p>
            @endif

            @if (isset($notification->data['total_price']))
                <p><strong>Total Price:</strong> ${{ number_format($notification->data['total_price'], 2) }}</p>
            @endif

            @if (isset($notification->data['buyer_name']))
                <p><strong>Buyer Name:</strong> {{ $notification->data['buyer_name'] }}</p>
            @endif

            @if (isset($notification->data['buyer_email']))
                <p><strong>Buyer Email:</strong> {{ $notification->data['buyer_email'] }}</p>
            @endif

            @if (isset($notification->data['url']))
                <p><a href="{{ url($notification->data['url']) }}" class="btn">View Details</a></p>
            @endif
        </div>

        <div class="footer">
            <p>Thank you for using our application!</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>

</html>
