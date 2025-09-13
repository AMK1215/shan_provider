@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                        <li class="breadcrumb-item active">Change Password</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card col-lg-6 offset-lg-3 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1" style="border-radius: 15px;">
                <div class="card-header">
                    <div class="card-title col-12">
                        <h5 class="d-inline fw-bold">Change Password</h5>
                        <a href="{{ route('admin.agent.index') }}" class="btn btn-primary d-inline float-right ">
                            <i class="fas fa-arrow-left" style="font-size: 20px;"></i> Back
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.agent.makeChangePassword',$agent->id) }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8 offset-lg-2 col-md-8 offset-md-2 col-sm-8 offset-sm-2 col-10 offset-1">
                                <div class="form-group">
                                    <label>New Password<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="password">
                                </div>
                                <div class="form-group">
                                    <label>Confirm Password<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="password_confirmation">
                                    @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer  bg-white col-12">
                        <button type="submit" class="btn btn-success float-right">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
