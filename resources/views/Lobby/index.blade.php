@extends('layouts')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Dashboard</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text display-4">{{ \App\Models\User::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Products</h5>
                    <p class="card-text display-4">{{ \App\Models\Product::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Categories</h5>
                    <p class="card-text display-4">{{ \App\Models\Category::count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <h5 class="card-title">Orders</h5>
                    <p class="card-text display-4">{{ \App\Models\Order::count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    Recent Activities
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            New user registered
                            <span class="badge bg-primary rounded-pill">Just now</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            New order placed
                            <span class="badge bg-primary rounded-pill">5 min ago</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Product inventory updated
                            <span class="badge bg-primary rounded-pill">1 hour ago</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    Top Selling Products
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Product A</td>
                                    <td>100</td>
                                </tr>
                                <tr>
                                    <td>Product B</td>
                                    <td>85</td>
                                </tr>
                                <tr>
                                    <td>Product C</td>
                                    <td>75</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 767.98px) {
        .display-4 {
            font-size: 2.5rem;
        }
    }
</style>
@endsection
