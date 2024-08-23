<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy"
        content="default-src 'self' https: data: blob: 'unsafe-inline' 'unsafe-eval'; img-src 'self' https: data: blob: http: https:; connect-src 'self' https: http: ws: wss:; font-src 'self' https: data:; media-src 'self' https: data:; object-src 'none'; script-src 'self' https: 'unsafe-inline' 'unsafe-eval'; style-src 'self' https: 'unsafe-inline';">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-commerce Dashboard</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('Asset/Logo.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> --}}
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
        }

        .main-sidebar {
            background-color: #343a40;
            box-shadow: 0 14px 28px rgba(0, 0, 0, .25), 0 10px 10px rgba(0, 0, 0, .22);
        }

        .main-sidebar .brand-link {
            border-bottom: 1px solid #4b545c;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
            background-color: #007bff;
            color: #fff;
        }

        .content-wrapper {
            background-color: #f4f6f9;
        }

        .navbar-light {
            background-color: #fff !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
        }

        .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
        }

        .navbar-nav .nav-item {
            display: flex;
            align-items: center;
        }

        .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
        }

        .user-panel img {
            height: 2.1rem;
            width: 2.1rem;
            object-fit: cover;
        }

        .alert-bottom-right {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
        }

        .dropdown-menu {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .dropdown-item:active {
            background-color: #f8f9fa;
            color: #16181b;
        }

        .notification-item.unread {
            background-color: #777777;
            font-weight: bold;
        }

        .notification-item.read {
            background-color: #ffffff;
        }


        .notification-bell {
            position: relative;
            font-size: 1.2rem;
        }

        .notification-count {
            position: absolute;
            top: -2px;
            right: 5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.15rem 0.5rem;
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                @auth
                    <!-- Notifikasi -->
                    <li class="nav-item">
                        <a class="nav-link notification-bell" href="{{ route('notifications.index') }}"
                            role="button">
                            <i class="fas fa-bell"></i>
                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <span class="notification-count">{{ Auth::user()->unreadNotifications->count() }}</span>
                            @endif
                        </a>
                    </li>

                    <!-- Dropdown User -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @php
                                $imageUrl = Auth::user()->image;
                                $isUrl = filter_var($imageUrl, FILTER_VALIDATE_URL);
                                $image =
                                    $isUrl && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $imageUrl)
                                        ? $imageUrl
                                        : (Auth::user()->image
                                            ? asset('storage/users/' . Auth::user()->image)
                                            : asset('default-avatar.png'));
                            @endphp
                            <img src="{{ $image }}" class="rounded-circle me-2" alt="User Image"
                                style="width: 32px; height: 32px; object-fit: cover;">
                            <span>{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" style="width: 220px;">
                            <li class="px-3 py-2">
                                <div class="d-flex align-items-center">
                                    @php
                                        $imageLarge =
                                            $isUrl && preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $imageUrl)
                                                ? $imageUrl
                                                : (Auth::user()->image
                                                    ? asset('storage/users/' . Auth::user()->image)
                                                    : asset('default-avatar.png'));
                                    @endphp
                                    <img src="{{ $imageLarge }}" class="rounded-circle me-2" alt="User Image"
                                        style="width: 48px; height: 48px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                        <small class="text-muted">Member since
                                            {{ Auth::user()->created_at->format('M. Y') }}</small>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('users.edit', Auth::id()) }}">Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('email.change') }}">Change Email</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Sign out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth

                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                @endguest
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">
                <img src="{{ asset('Asset/Logo.png') }}" alt="Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">E-commerce</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('lobby.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Home</p>
                            </a>
                        </li>
                        @auth
                            <li class="nav-item">
                                <a href="{{ route('products.index') }}" class="nav-link">
                                    <i class="nav-icon fas fa-box"></i>
                                    <p>Products</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('categories.index') }}" class="nav-link">
                                    <i class="nav-icon fas fa-tags"></i>
                                    <p>Categories</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>Admin</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('buyer.index') }}" class="nav-link">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Buyer</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('orders.index') }}" class="nav-link">
                                    <i class="nav-icon fas fa-shopping-cart"></i>
                                    <p>Orders</p>
                                </a>
                            </li>
                        @endauth
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    @yield('content-header')
                </div>
            </div>
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        <footer class="main-footer">
            {{-- <strong>Copyright &copy; 2023 E-commerce.</strong> All rights reserved. --}}
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $('.nav-sidebar .nav-link').each(function() {
                if ($(this).attr('href') === window.location.href) {
                    $(this).addClass('active');
                }
            });

            // Toastr configuration
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-bottom-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error('{{ $error }}');
                @endforeach
            @endif
        });

        document.addEventListener("DOMContentLoaded", function() {
            const imgElements = document.querySelectorAll('.nav-link img');

            imgElements.forEach(img => {
                const imgUrl = img.src;

                function isImageUrl(url) {
                    return /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(url);
                }

                if (!isImageUrl(imgUrl)) {
                    img.src = '{{ asset('default-avatar.png') }}';
                    alert('The provided URL is not a valid image.');
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
