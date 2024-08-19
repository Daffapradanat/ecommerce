<!DOCTYPE html>
<html>

<head>
    <title>{{ $subject }}</title>
</head>

<body>
    <h1>{{ $subject }}</h1>
    @if (isset($notification->data['message']))
        <p>{{ $notification->data['message'] }}</p>
    @endif

    @if (isset($notification->data['order_id']))
        <p>Order ID: {{ $notification->data['order_id'] }}</p>
    @endif

    @if (isset($notification->data['product_name']))
        <p>Product: {{ $notification->data['product_name'] }}</p>
    @endif

    @if (isset($notification->data['total_price']))
        <p>Total Price: ${{ number_format($notification->data['total_price'], 2) }}</p>
    @endif

    @if (isset($notification->data['buyer_name']))
        <p>Buyer Name: {{ $notification->data['buyer_name'] }}</p>
    @endif

    @if (isset($notification->data['buyer_email']))
        <p>Buyer Email: {{ $notification->data['buyer_email'] }}</p>
    @endif

    @if (isset($notification->data['url']))
        <p>For more details, <a href="{{ url($notification->data['url']) }}">click here</a>.</p>
    @endif

    <p>Thank you for using our application!</p>
</body>

</html>

{{-- <!DOCTYPE html>
<html>
<head>
    <title>{{ $subject ?? 'Notification' }}</title>
</head>
<body>
    <h1>{{ $subject ?? 'New Notification' }}</h1>

    @if (isset($notification->data['message']))
        <p>{{ $notification->data['message'] }}</p>
    @endif

    @if (isset($notification->data['product_name']))
        <p>Product: {{ $notification->data['product_name'] }}</p>
    @endif

    @if (isset($notification->data['product_price']))
        <p>Price: ${{ number_format($notification->data['product_price'], 2) }}</p>
    @endif

    @if (isset($notification->data['url']))
        <p>For more details, <a href="{{ url($notification->data['url']) }}">click here</a>.</p>
    @endif

    <p>Thank you for using our application!</p>
</body>
</html> --}}
