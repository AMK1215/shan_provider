@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Top Ten Withdraw</li>
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
                    <div class="card col-lg-6 offset-lg-3 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1"
                        style="border-radius: 15px;">
                        <div class="card-header">
                            <div class="card-title col-12">
                                <h3 class="d-inline fw-bold">Top Ten Withdraw Create </h3>
                                <a href="{{ route('admin.top-10-withdraws.index') }}" class="btn btn-danger float-right"><i
                                        class="fas fa-arrow-left text-white  "></i>Back</a>
                            </div>
                        </div>
                        <div
                            class="card-body  col-lg-12 offset-lg-0 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1">
                            <form role="form" class="text-start" action="{{ route('admin.top-10-withdraws.store') }}"
                                method="post">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-bold" for="inputEmail1">PlayerID </label>
                                    <input type="text" class="form-control border border-1 border-secondary px-2"
                                        id="inputEmail1" name="player_id" placeholder="Enter PlayerID (P00112244)">
                                    @error('player_id')
                                        <span class="text-danger d-block">*{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-bold" for="inputEmail1">WithdrawAmount </label>
                                    <input type="text" class="form-control border border-1 border-secondary px-2"
                                        id="inputEmail1" name="amount"
                                        placeholder="Enter PlayerWithdrawAmount (1000000.00)">
                                    @error('amount')
                                        <span class="text-danger d-block">*{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-success float-right" type="submit">Create</button>
                                </div>
                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
@endsection
