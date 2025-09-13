@extends('layouts.master')

@section('content')
<section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>PlayerTransfer Log</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Transfer Log</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('home') }}" class="btn btn-success " style="width: 100px;"><i
                                class="fas fa-plus text-white  mr-2"></i>Back</a>
                    </div>
                    <div class="card">
        <div class="card-body">
            <!-- Filter Form -->
            <dl class="row">
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9">{{ $transferLog->id }}</dd>

                <dt class="col-sm-3">From User</dt>
                <dd class="col-sm-9">
                    {{ $transferLog->fromUser?->name ?? '-' }} ({{ $transferLog->fromUser?->user_name ?? '-' }})
                </dd>

                <dt class="col-sm-3">To User</dt>
                <dd class="col-sm-9">
                    {{ $transferLog->toUser?->name ?? '-' }} ({{ $transferLog->toUser?->user_name ?? '-' }})
                </dd>

                <dt class="col-sm-3">Amount</dt>
                <dd class="col-sm-9">{{ number_format($transferLog->amount, 2) }}</dd>

                <dt class="col-sm-3">Type</dt>
                <dd class="col-sm-9">{{ ucfirst($transferLog->type) }}</dd>

                <dt class="col-sm-3">Note</dt>
                <dd class="col-sm-9">{{ $transferLog->note ?? '-' }}</dd>

                <dt class="col-sm-3">Created At</dt>
                <dd class="col-sm-9">{{ $transferLog->created_at->format('Y-m-d H:i:s') }}</dd>
            </dl>

           
        </div>
    </div>
                    <!-- /.card -->
                </div>

            </div>
        </div>
</section>


@endsection 