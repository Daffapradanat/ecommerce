@extends('layouts')


@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h1 class="h3 mb-0">{{ __('products.product_details') }}</h1>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            @if($product->image->isNotEmpty())
                            <div id="productImageCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach($product->image as $index => $image)
                                        <button type="button" data-bs-target="#productImageCarousel" data-bs-slide-to="{{ $index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div class="carousel-inner">
                                    @foreach($product->image as $index => $image)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->name }}" class="d-block w-100 product-image">
                                        </div>
                                    @endforeach
                                </div>
                                @if($product->image->count() > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#productImageCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">{{ __('products.previous') }}</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#productImageCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">{{ __('products.next') }}</span>
                                    </button>
                                @endif
                            </div>
                            @else
                                <div class="bg-light text-center p-5 rounded">
                                    <span class="text-muted">{{ __('products.no_image_available') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h2 class="h4 mb-3">{{ $product->name }}</h2>
                            <p class="text-muted mb-4">{{ $product->description }}</p>
                            <table class="table table-sm">
                                <tr>
                                    <th class="w-35">{{ __('products.price') }}:</th>
                                    <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('products.stock') }}:</th>
                                    <td>{{ $product->stock }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('products.category') }}:</th>
                                    <td>{{ $product->category->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('products.created_at') }}:</th>
                                    <td>{{ $product->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('products.update_product') }}:</th>
                                    <td>{{ $product->updated_at->format('d M Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">{{ __('products.back_to_list') }}</a>
                        <div>
                            @if(Auth::user()->can('update', $product))
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning me-2">{{ __('products.edit') }}</a>
                            @endif
                        
                            @if(Auth::user()->can('delete', $product))
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    {{ __('products.delete') }}
                                </button>
                            @endif
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">{{ __('products.confirm_product_deletion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ __('products.sure_delete_product') }} "{{ $product->name }}"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('products.cancel') }}</button>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('products.delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-body {
        padding: 2rem;
    }

    #productImageCarousel {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        position: relative;
    }

    .carousel-indicators {
        bottom: 0;
        background-color: rgba(0,0,0,0.7);
        padding: 10px 0;
        margin: 0;
        border-radius: 0 0 0.5rem 0.5rem;
    }

    .carousel-indicators button {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #fff;
        opacity: 0.7;
        margin: 0 6px;
        border: 2px solid #fff;
    }

    .carousel-indicators button.active {
        opacity: 1;
        background-color: #007bff;
    }

    .carousel-inner {
        height: 400px;
    }

    .carousel-item {
        height: 100%;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
    }

    #productImageCarousel .carousel-control-prev,
    #productImageCarousel .carousel-control-next {
        background-color: rgba(0, 0, 0, 0.5);
        width: 15%;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    #productImageCarousel:hover .carousel-control-prev,
    #productImageCarousel:hover .carousel-control-next {
        opacity: 1;
    }

    #productImageCarousel .carousel-control-prev-icon,
    #productImageCarousel .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.8);
        border-radius: 50%;
        padding: 20px;
        background-size: 50%;
    }

    .carousel-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom,
            rgba(0,0,0,0.3) 0%,
            rgba(0,0,0,0) 20%,
            rgba(0,0,0,0) 80%,
            rgba(0,0,0,0.3) 100%);
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .carousel-inner {
            height: 300px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var deleteModal = document.getElementById('deleteModal');
    new bootstrap.Modal(deleteModal);

    var carouselElement = document.getElementById('productImageCarousel');
    var carousel = new bootstrap.Carousel(carouselElement, {
        interval: 5000, // Ubah ini ke 5000 (5 detik) atau lebih jika Anda ingin lebih lambat
        pause: 'hover', // Menghentikan slide saat kursor di atas carousel
        touch: true
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft') {
            carousel.prev();
        } else if (event.key === 'ArrowRight') {
            carousel.next();
        }
    });

    // Touch swipe functionality (tetap sama seperti sebelumnya)
    let touchStartX = 0;
    let touchEndX = 0;

    carouselElement.addEventListener('touchstart', function(event) {
        touchStartX = event.changedTouches[0].screenX;
    }, false);

    carouselElement.addEventListener('touchend', function(event) {
        touchEndX = event.changedTouches[0].screenX;
        handleSwipe();
    }, false);

    function handleSwipe() {
        if (touchEndX < touchStartX) {
            carousel.next();
        }
        if (touchEndX > touchStartX) {
            carousel.prev();
        }
    }
});
</script>
@endpush
