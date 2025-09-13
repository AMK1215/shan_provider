@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3>TransferLog</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">TransferLog</li>
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
                <div class="card">
                    <div class="card-body">
                        <form role="form" class="text-start" action="{{ route('admin.transferLog') }}" method="GET">
                            <div class="row ">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold" for="inputEmail1">From Date</label>
                                        <input type="date" class="form-control border border-1 border-secondary px-2"
                                            id="inputEmail1" name="start_date" value="{{ request()->start_date }}">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label text-dark fw-bold" for="inputEmail1">End Date</label>
                                        <input type="date" class="form-control border border-1 border-secondary px-2"
                                            id="end_date" name="end_date" value="{{ request()->end_date }}">
                                    </div>
                                </div>
                                <div class="col-log-3">
                                    <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Search</button>
                                    <a href="{{ route('admin.transferLog') }}" class="btn btn-warning" style="margin-top: 32px;">Refresh</a>
                                </div>
                            </div>
                        </form>
                        <table id="mytable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>UserId</th>
                                    <th>UserName</th>
                                    <th>Old Balance</th>
                                    <th>Amount</th>
                                    <th>New Balance</th>
                                    <th>Type</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transferLogs as $log)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{ $log->created_at }}</td>
                                    <td>{{$log->targetUser->user_name}}</td>
                                    <td>{{ $log->targetUser->name }}</td>
                                    <td>{{$log->old_balance}}</td>
                                    <td>
                                        <div class="d-flex align-items-center text-{{ $log->type == 'withdraw' ? 'success' : 'danger' }} text-gradient text-sm font-weight-bold ms-auto">
                                            {{ $log->type == 'withdraw' ? '+' : '-' }} {{ number_format(abs($log->amountFloat)) }}
                                        </div>
                                    </td>
                                    <td>{{ $log->new_balance}}</td>
                                    <td>
                                        @if ($log->type == 'deposit')
                                        <p class="text-danger font-weight-bold">Withdraw</p>
                                        @else
                                        <p class="text-success font-weight-bold">Deposit</p>
                                        @endif
                                    </td>
                                    @if($log->note == "null")
                                    <td></td>
                                    @else
                                    <td>{{$log->note}}</td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total Amount:</th>
                                    <th></th>
                                    <th colspan="2">
                                        Deposit:
                                        <span class="text-success">
                                            {{ number_format(abs(intdiv($withdrawTotal, 100)), 2) }}
                                        </span>
                                    </th>
                                    <th colspan="2">
                                        Withdraw:
                                        <span class="text-danger">
                                            {{ number_format(abs(intdiv($depositTotal, 100)), 2) }}
                                        </span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection