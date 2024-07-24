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
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    @php
                        $recentActivities = collect();

                        $recentUsers = \App\Models\User::latest()->take(5)->get()->map(function ($user) {
                            return [
                                'type' => 'user',
                                'message' => 'New user registered: ' . $user->name,
                                'time' => $user->created_at
                            ];
                        });

                        $recentBuyers = \App\Models\Buyer::latest()->take(5)->get()->map(function ($user) {
                            return [
                                'type' => 'buyer',
                                'message' => 'New Buyer registered: ' . $user->name,
                                'time' => $user->created_at
                            ];
                        });

                        $recentOrders = \App\Models\Order::latest()->take(5)->get()->map(function ($order) {
                            return [
                                'type' => 'order',
                                'message' => 'New order placed: #' . $order->id,
                                'time' => $order->created_at
                            ];
                        });

                        $recentProducts = \App\Models\Product::latest('updated_at')->take(5)->get()->map(function ($product) {
                            return [
                                'type' => 'product',
                                'message' => 'Product updated: ' . $product->name,
                                'time' => $product->updated_at
                            ];
                        });

                        $recentActivities = $recentUsers->concat($recentBuyers)->concat($recentOrders)->concat($recentProducts)
                            ->sortByDesc('time')
                            ->take(5);
                    @endphp

                    @if($recentActivities->isNotEmpty())
                        <ul class="list-group list-group-flush">
                            @foreach($recentActivities as $activity)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        @switch($activity['type'])
                                            @case('user')
                                                <i class="fas fa-user text-primary me-2"></i>
                                                @break
                                            @case('buyer')
                                                <i class="fas fa-shopping-cart text-success me-2"></i>
                                                @break
                                            @case('order')
                                                <i class="fas fa-file-invoice-dollar text-warning me-2"></i>
                                                @break
                                            @case('product')
                                                <i class="fas fa-box text-info me-2"></i>
                                                @break
                                        @endswitch
                                        {{ $activity['message'] }}
                                    </span>
                                    <span class="badge bg-secondary rounded-pill">{{ $activity['time']->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center my-3">No recent activities</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Top Selling Products</h5>
                </div>
                <div class="card-body">
                    @php
                        $topSellingProducts = \App\Models\Product::select('products.*')
                            ->join('order_items', 'products.id', '=', 'order_items.product_id')
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->where('orders.status', 'completed')
                            ->groupBy('products.id')
                            ->orderByRaw('SUM(order_items.quantity) DESC')
                            ->selectRaw('SUM(order_items.quantity) as total_quantity')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($topSellingProducts->isNotEmpty() && $topSellingProducts->sum('total_quantity') > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Total Quantity Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topSellingProducts as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td class="text-end">{{ $product->total_quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center my-3">No product sales data available</p>
                    @endif
                </div>
            </div> --}}
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
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
</style>
@endsection
