@extends('layouts.master')

@section('content')
<div class="container mt-4">
    <h2>Add Product</h2>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('admin.product.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.product.partials.form')
        <button type="submit" class="btn btn-primary">Create</button>
        <a href="{{ route('admin.product.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

    @section('script')
    
@endsection
