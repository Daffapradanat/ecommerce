@extends('layouts')


@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h1 class="h3 mb-0">Product Details</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            @if($product->images && count($product->images) > 0)
                                <div id="productImageCarousel" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @foreach($product->images as $index => $image)
                                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                                <img src="{{ asset('storage/' . $image) }}" class="d-block w-100 rounded product-image" alt="{{ $product->name }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(count($product->images) > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#productImageCarousel" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#productImageCarousel" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="bg-light text-center p-5 rounded">
                                    <span class="text-muted">No image available</span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h2 class="h4 mb-3">{{ $product->name }}</h2>
                            <p class="text-muted mb-3">{{ $product->description }}</p>
                            <table class="table table-sm">
                                <tr>
                                    <th class="w-35">Price:</th>
                                    <td>${{ number_format($product->price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Stock:</th>
                                    <td>{{ $product->stock }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ $product->category->name }}</td>
                                </tr>
                                <tr>
                                    <th>Created at:</th>
                                    <td>{{ $product->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Last updated:</th>
                                    <td>{{ $product->updated_at->format('d M Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to List</a>
                        <div>
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning me-2">Edit</a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                Delete
                            </button>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the product "{{ $product->name }}"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .product-image {
        width: 100%;
        height: 350px; /* Slightly reduced height */
        object-fit: contain;
        background-color: #f8f9fa;
    }
    #productImageCarousel .carousel-control-prev,
    #productImageCarousel .carousel-control-next {
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    #productImageCarousel:hover .carousel-control-prev,
    #productImageCarousel:hover .carousel-control-next {
        opacity: 0.5;
    }
    #productImageCarousel .carousel-control-prev:hover,
    #productImageCarousel .carousel-control-next:hover {
        opacity: 1;
    }
    .table.table-sm th {
        width: 35%;
    }
    .table.table-sm td, .table.table-sm th {
        padding: 0.5rem;
    }
    .carousel-inner {
        display: flex;
        transition: transform 0.3s ease-in-out;
    }
    .carousel-item {
        flex: 0 0 100%;
    }
    .product-image {
        width: 100%;
        height: 350px;
        object-fit: contain;
        background-color: #f8f9fa;
        user-select: none;
    }
    #productImageCarousel {
        overflow: hidden;
        touch-action: pan-y;
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
        interval: false,
        touch: true
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'ArrowLeft') {
            carousel.prev();
        } else if (event.key === 'ArrowRight') {
            carousel.next();
        }
    });

    let isDragging = false;
    let startPosition;
    let currentTranslate = 0;

    carouselElement.addEventListener('mousedown', dragStart);
    carouselElement.addEventListener('touchstart', dragStart);
    carouselElement.addEventListener('mouseup', dragEnd);
    carouselElement.addEventListener('touchend', dragEnd);
    carouselElement.addEventListener('mousemove', drag);
    carouselElement.addEventListener('touchmove', drag);

    function dragStart(event) {
        if (event.type === 'touchstart') {
            startPosition = event.touches[0].clientX;
        } else {
            startPosition = event.clientX;
            event.preventDefault();
        }
        isDragging = true;
    }

    function drag(event) {
        if (isDragging) {
            let currentPosition;
            if (event.type === 'touchmove') {
                currentPosition = event.touches[0].clientX;
            } else {
                currentPosition = event.clientX;
            }
            const diff = currentPosition - startPosition;
            if (Math.abs(diff) > 100) {
                if (diff > 0) {
                    carousel.prev();
                } else {
                    carousel.next();
                }
                isDragging = false;
            }
        }
    }

    function dragEnd() {
        isDragging = false;
    }
});
</script>
@endpush
