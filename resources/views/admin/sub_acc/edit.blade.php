@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Edit Sub Account</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card col-lg-6 offset-lg-3 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1"
            style="border-radius: 15px;">
            <div class="card-header">
                <div class="card-title col-12">
                    <h5 class="d-inline fw-bold">
                        Edit Sub Account
                    </h5>
                    <a href="{{ route('admin.subacc.index') }}" class="btn btn-primary d-inline float-right">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.subacc.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body mt-2">
                    <div class="row">
                        <div class="col-lg-12 offset-lg-0 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1 ">
                            <div class="form-group">
                                <label>PlayerId<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="user_name" value="{{ $user->user_name }}"
                                    readonly>
                                @error('user_name')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ $user->name }}">
                                @error('name')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Phone<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone" value="{{ $user->phone }}">
                                @error('phone')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer col-12 bg-white">
                    <button type="submit" class="btn btn-success float-right">Submit</button>
                </div>
            </form>
        </div>
    </div>
    </div>
</section>
@endsection