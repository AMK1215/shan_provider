@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">GCSGameProvider</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm" style="border-radius: 20px;">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="border-radius: 20px 20px 0 0;">
                            <h4 class="mb-0">GSC Plus Game Provider List</h4>
                            <!-- <a href="" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Product
                            </a> -->
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="mytable" class="table table-hover table-striped align-middle mb-0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="bg-success text-white">Game Type</th>
                                            <th class="bg-danger text-white">Product</th>
                                            <th class="bg-danger text-white">Code</th>
                                            <th class="bg-warning text-white">Image</th>
                                            <th class="bg-info text-white">Status</th>
                                            <th class="bg-info text-white">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($gameTypes as $gameType)
                                            @foreach ($gameType->products as $product)
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <span class="badge bg-success text-white px-3 py-2">{{ $gameType->name }}</span>
                                                    </td>
                                                    <td class="text-center align-middle">{{ $product->product_title }}</td>
                                                    <td class="text-center align-middle">{{ $product->product_code }}</td>
                                                    <td class="text-center align-middle">
                                                        <img src="{{ $product->getImgUrlAttribute() }}" alt="" width="60" height="60" class="rounded shadow-sm border" style="object-fit:cover;">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span class="badge {{ $product->game_list_status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                                            <i class="fas {{ $product->game_list_status == 1 ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                            {{ $product->game_list_status == 1 ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button class="btn btn-outline-warning btn-sm toggle-status-btn"
                                                            data-product-id="{{ $product->id }}"
                                                            data-status="{{ $product->status }}"
                                                            data-bs-toggle="tooltip" title="Toggle Status">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </button>
                                                        <a href="{{ route('admin.gametypes.edit', [$gameType->id, $product->id]) }}"
                                                            class="btn btn-outline-info btn-sm" style="width: 40px;" data-bs-toggle="tooltip" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                       
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
@section('script')
    <script>
        document.querySelectorAll('.toggle-status-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const currentStatus = this.getAttribute('data-status');
                const newStatus = currentStatus == "ACTIVATED" ? 'UNACTIVATED' : 'ACTIVATED';
                const statusLabel = document.querySelector(`.status-label[data-product-id="${productId}"]`);

                fetch('{{ route('admin.gametypes.toggle-status', ':productId') }}'.replace(':productId',
                        productId), {
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
                            this.textContent = data.newStatus == 'ACTIVATED' ? 'Deactivate' : 'Activate';
                            statusLabel.textContent = data.newStatus == 'ACTIVATED' ? 'Active' : 'Inactive';
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

@endsection
