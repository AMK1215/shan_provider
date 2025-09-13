@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Game List</li>
                </ol>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="border-radius: 20px 20px 0 0;">
                        <h4 class="mb-0">Game List</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="bg-warning text-white">Image</th>
                                        <th class="bg-danger text-white">Product</th>
                                        <th class="bg-danger text-white">Code</th>
                                        <th class="bg-success text-white">Game Types</th>
                                        <th class="bg-info text-white">Status</th>
                                        <th class="bg-info text-white">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td class="text-center align-middle">
                                            <img src="{{ $product->getImageUrlAttribute() }}" alt="" width="60" height="60" class="rounded shadow-sm border" style="object-fit:cover;">
                                        </td>
                                        <td class="text-center align-middle">{{ $product->title }}</td>
                                        <td class="text-center align-middle">{{ $product->code }}</td>
                                        <td class="text-center align-middle">
                                            @foreach($product->gameTypes as $gameType)
                                                <span class="badge bg-success text-white px-2 py-1 mb-1">{{ $gameType->name }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge {{ $product->status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="fas {{ $product->status == 1 ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                {{ $product->status == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button class="btn btn-outline-warning btn-sm toggle-status-btn"
                                                data-product-id="{{ $product->id }}"
                                                data-status="{{ $product->status }}"
                                                data-bs-toggle="tooltip" title="Toggle Status">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No products found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            {!! $products->appends(request()->query())->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
    document.querySelectorAll('.toggle-status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const currentStatus = this.getAttribute('data-status');
            const newStatus = currentStatus == 1 ? 0 : 1;
            const statusLabel = this.closest('tr').querySelector('td:nth-child(5) .badge');
            fetch('/admin/product/toggle-status/' + productId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({}),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.setAttribute('data-status', data.newStatus);
                    statusLabel.textContent = data.newStatus == 1 ? 'Active' : 'Inactive';
                    statusLabel.className = 'badge ' + (data.newStatus == 1 ? 'bg-success' : 'bg-secondary');
                    this.classList.toggle('btn-warning');
                    this.classList.toggle('btn-success');
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the status.');
            });
        });
    });
</script>
@endsection 