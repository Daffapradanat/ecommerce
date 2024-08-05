<div class="btn-group" role="group">
    <a href="{{ route('products.show', $product->id) }}" class="btn btn-info btn-sm me-2">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm me-2">
        <i class="fas fa-edit"></i>
    </a>
    <button type="button" class="btn btn-danger btn-sm me-0" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->id }}">
        <i class="fas fa-trash"></i>
    </button>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $product->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel{{ $product->id }}">Confirm Product Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the product "{{ $product->name }}"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
