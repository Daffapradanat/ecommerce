@extends('layouts')

@section('content')
<div class="container">
    <h1>Create New Order</h1>
    <form action="{{ route('orders.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="buyer_id">Buyer</label>
            <select name="buyer_id" id="buyer_id" class="form-control" required>
                @foreach(App\Models\Buyer::all() as $buyer)
                    <option value="{{ $buyer->id }}">{{ $buyer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" name="phone" id="phone" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" id="address" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" name="city" id="city" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="postal_code">Postal Code</label>
            <input type="text" name="postal_code" id="postal_code" class="form-control" required>
        </div>
        <div id="items">
            <div class="item">
                <h3>Item 1</h3>
                <div class="form-group">
                    <label>Product</label>
                    <select name="items[0][product_id]" class="form-control" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} - Rp{{ number_format($product->price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="items[0][quantity]" class="form-control" min="1" value="1" required>
                </div>
            </div>
        </div>
        <button type="button" id="add-item" class="btn btn-secondary mt-2">Add Item</button>
        <button type="submit" class="btn btn-primary mt-2">Create Order</button>
    </form>
</div>

<script>
let itemCount = 1;
document.getElementById('add-item').addEventListener('click', function() {
    const itemsDiv = document.getElementById('items');
    const newItem = document.createElement('div');
    newItem.className = 'item';
    newItem.innerHTML = `
        <h3>Item ${itemCount + 1}</h3>
        <div class="form-group">
            <label>Product</label>
            <select name="items[${itemCount}][product_id]" class="form-control" required>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }} - Rp{{ number_format($product->price, 0, ',', '.') }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="items[${itemCount}][quantity]" class="form-control" min="1" value="1" required>
        </div>
    `;
    itemsDiv.appendChild(newItem);
    itemCount++;
});
</script>
@endsection
