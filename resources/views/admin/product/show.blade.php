@extends('layouts.master')

@section('content')
<div class="container mt-4">
    <h2>Product Detail</h2>
    <div class="card p-4">
        <div class="row">
            <div class="col-md-4">
                <img src="{{ $product->imgUrl }}" alt="Image" style="width:120px;height:120px;" class="mb-3 rounded border">
            </div>
            <div class="col-md-8">
                <p><strong>Provider:</strong> {{ $product->provider }}</p>
                <p><strong>Currency:</strong> {{ $product->currency }}</p>
                <p><strong>Status:</strong> {{ $product->status }}</p>
                <p><strong>Provider ID:</strong> {{ $product->provider_id }}</p>
                <p><strong>Provider Product ID:</strong> {{ $product->provider_product_id }}</p>
                <p><strong>Product Code:</strong> {{ $product->product_code }}</p>
                <p><strong>Product Name:</strong> {{ $product->product_name }}</p>
                <p><strong>Game Type:</strong> {{ $product->game_type }}</p>
                <p><strong>Product Title:</strong> {{ $product->product_title }}</p>
                <p><strong>Short Name:</strong> {{ $product->short_name }}</p>
                <p><strong>Order:</strong> {{ $product->order }}</p>
                <p><strong>Game List Status:</strong>
                    @if($product->game_list_status)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('admin.product.index') }}" class="btn btn-secondary mt-3">Back to List</a>
    </div>
</div>
@endsection

    @section('script')
    
@endsection
