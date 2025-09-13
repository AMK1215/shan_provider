@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Create Sub Account</li>
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
                        Create Sub Account
                    </h5>
                    <a href="{{ route('admin.subacc.index') }}" class="btn btn-primary d-inline float-right">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.subacc.store') }}" method="POST">
                @csrf
                <div class="card-body mt-2">
                    <div class="row">
                        <div class="col-lg-12 offset-lg-0 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1 ">
                            <div class="form-group">
                                <label>SubAccId<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="user_name" value="{{ $agent_name }}"
                                    readonly>
                                @error('user_name')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                                @error('name')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Phone<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Password<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="password"
                                    value="{{ old('password') }}">
                                @error('password')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Permission Group<span class="text-danger">*</span></label>
                                <select name="permission_group" class="form-control">
                                    <option value="">Select Permission Group</option>
                                    @foreach($permission_groups as $key => $value)
                                        <option value="{{ $key }}" {{ old('permission_group') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('permission_group')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Permission Group Details</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="permission-details">
                                            <!-- <h5>View Only Group</h5>
                                            <ul>
                                                <li>View player list</li>
                                                <li>View player report</li>
                                                <li>View transaction log</li>
                                            </ul> -->

                                            <h5>Player Creation Group</h5>
                                            <ul>
                                                <li>View player list</li>
                                                <li>Create/Edit players</li>
                                                <li>Change player passwords</li>
                                                <li>View player reports</li>
                                                <li>View transaction logs</li>
                                            </ul>

                                            <h5>Deposit/Withdraw Group</h5>
                                            <ul>
                                                <li>View player list</li>
                                                <li>View player reports</li>
                                                <li>View transaction logs</li>
                                                <li>View withdraw requests</li>
                                                <li>View deposit requests</li>
                                                <li>Process withdrawals</li>
                                                <li>Process deposits</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
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