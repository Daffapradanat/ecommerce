@extends('layouts')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0 text-primary">Dashboard</h1>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md col-sm-6 mb-4">
            <div class="card bg-primary text-white h-100 shadow">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text display-4 mb-0">{{ \App\Models\User::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md col-sm-6 mb-4">
            <div class="card bg-success text-white h-100 shadow">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <h5 class="card-title">Products</h5>
                    <p class="card-text display-4 mb-0">{{ \App\Models\Product::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md col-sm-6 mb-4">
            <div class="card bg-info text-white h-100 shadow">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <h5 class="card-title">Categories</h5>
                    <p class="card-text display-4 mb-0">{{ \App\Models\Category::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md col-sm-6 mb-4">
            <div class="card bg-warning text-dark h-100 shadow">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <h5 class="card-title">Orders</h5>
                    <p class="card-text display-4 mb-0">{{ \App\Models\Order::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md col-sm-6 mb-4">
            <div class="card bg-secondary text-white h-100 shadow">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <h5 class="card-title">Buyers</h5>
                    <p class="card-text display-4 mb-0">{{ \App\Models\Buyer::count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activities</h5>
                    <span class="badge bg-light text-primary">Last 24 hours</span>
                </div>
                <div class="card-body">
                    @php
                        $recentActivities = collect();

                        $recentUsers = \App\Models\User::latest()->take(5)->get()->map(function ($user) {
                            return [
                                'type' => 'user',
                                'message' => 'New user registered',
                                'name' => $user->name,
                                'email' => $user->email,
                                'time' => $user->created_at,
                                'icon' => 'fas fa-user-plus text-primary'
                            ];
                        });

                        $recentBuyers = \App\Models\Buyer::latest()->take(5)->get()->map(function ($buyer) {
                            return [
                                'type' => 'buyer',
                                'message' => 'New buyer registered',
                                'name' => $buyer->name,
                                'email' => $buyer->email,
                                'time' => $buyer->created_at,
                                'icon' => 'fas fa-shopping-bag text-success'
                            ];
                        });

                        $recentOrders = \App\Models\Order::with('buyer')->latest()->take(5)->get()->map(function ($order) {
                            return [
                                'type' => 'order',
                                'message' => 'New order placed',
                                'order_id' => $order->id,
                                'buyer_name' => $order->buyer->name,
                                'total' => $order->total,
                                'time' => $order->created_at,
                                'icon' => 'fas fa-file-invoice-dollar text-warning'
                            ];
                        });

                        $recentProducts = \App\Models\Product::latest('updated_at')->take(5)->get()->map(function ($product) {
                            return [
                                'type' => 'product',
                                'message' => $product->created_at->isToday() ? 'New product added' : 'Product updated',
                                'name' => $product->name,
                                'price' => $product->price,
                                'updated_by' => $product->updated_by_user->name ?? 'System',
                                'time' => $product->updated_at,
                                'icon' => $product->created_at->isToday() ? 'fas fa-box text-info' : 'fas fa-edit text-info'
                            ];
                        });

                        $recentActivities = $recentUsers->concat($recentBuyers)->concat($recentOrders)->concat($recentProducts)
                            ->sortByDesc('time')
                            ->take(10);
                    @endphp

                        @if($recentActivities->isNotEmpty())
                        <ul class="list-group list-group-flush">
                            @foreach($recentActivities as $activity)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="{{ $activity['icon'] }} me-2"></i>
                                            <strong>{{ $activity['message'] }}</strong>
                                        </span>
                                        <span class="badge bg-secondary rounded-pill">{{ $activity['time']->diffForHumans() }}</span>
                                    </div>
                                    <div class="mt-2">
                                        @switch($activity['type'])
                                            @case('user')
                                            @case('buyer')
                                                <p class="mb-0">Name: {{ $activity['name'] }}</p>
                                                <p class="mb-0">Email: {{ $activity['email'] }}</p>
                                                @break
                                            @case('order')
                                                <p class="mb-0">Order ID: #{{ $activity['order_id'] }}</p>
                                                <p class="mb-0">Buyer: {{ $activity['buyer_name'] }}</p>
                                                <p class="mb-0">Total: ${{ number_format($activity['total'], 2) }}</p>
                                                @break
                                            @case('product')
                                                <p class="mb-0">Product: {{ $activity['name'] }}</p>
                                                <p class="mb-0">Price: ${{ number_format($activity['price'], 2) }}</p>
                                                <p class="mb-0">Updated by: {{ $activity['updated_by'] }}</p>
                                                @break
                                        @endswitch
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center my-3">No recent activities</p>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <a href="#" class="btn btn-primary btn-sm">View All Activities</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Top Selling Products</h5>
                </div>
                <div class="card-body">
                    @php
                        $topSellingProducts = \App\Models\Product::select('products.*')
                            ->join('order_items', 'products.id', '=', 'order_items.product_id')
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->where('orders.payment_status', 'completed')
                            ->groupBy('products.id')
                            ->orderByRaw('SUM(order_items.quantity) DESC')
                            ->selectRaw('SUM(order_items.quantity) as total_quantity')
                            ->selectRaw('SUM(order_items.quantity * products.price) as total_revenue')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($topSellingProducts->isNotEmpty() && $topSellingProducts->sum('total_quantity') > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Quantity Sold</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">% of Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalSales = $topSellingProducts->sum('total_quantity');
                                        $totalRevenue = $topSellingProducts->sum('total_revenue');
                                    @endphp
                                    @foreach($topSellingProducts as $product)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($product->image)
                                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-secondary me-2" style="width: 40px; height: 40px;"></div>
                                                    @endif
                                                    {{ $product->name }}
                                                </div>
                                            </td>
                                            <td class="text-end">{{ number_format($product->total_quantity) }}</td>
                                            <td class="text-end">${{ number_format($product->total_revenue, 2) }}</td>
                                            <td class="text-end">
                                                {{ number_format(($product->total_quantity / $totalSales) * 100, 1) }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-end">{{ number_format($totalSales) }}</th>
                                        <th class="text-end">${{ number_format($totalRevenue, 2) }}</th>
                                        <th class="text-end">100%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-center my-3">No product sales data available</p>
                    @endif
                </div>
            </div>
        </div>

        <style>
            @media (max-width: 767.98px) {
                .display-4 {
                    font-size: 2rem;
                }
                .card-title {
                    font-size: 1rem;
                }
            }
            .list-group-item {
                transition: background-color 0.3s ease;
            }
            .list-group-item:hover {
                background-color: #f8f9fa;
            }
            .card {
                transition: all 0.3s ease;
            }
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
            }
        </style>
        @endsection
