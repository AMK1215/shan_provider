@extends('layouts.master')
@section('style')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
    }
    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653b4;
    }
    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #e3e6f0;
        font-weight: 600;
    }
    .badge-credit {
        color: #155724;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
    }
    .badge-debit {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
    }
    .badge-neutral {
        color: #383d41;
        background-color: #e2e3e5;
        border: 1px solid #d6d8db;
    }
</style>
@endsection
@section('content')
<section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Transfer Log</h1>
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
                    <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                        <div class="card-header py-3">
                            <h5 class="m-0 font-weight-bold text-primary">Filters</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.subacc.tran.logs') }}" method="GET" class="form-inline">
                                <div class="form-group mb-2 mr-sm-2">
                                    <label for="daterange" class="sr-only">Date Range</label>
                                    <input type="text" name="daterange" class="form-control" placeholder="Select Date Range" style="min-width: 240px;">
                                    <input type="hidden" name="date_from" id="start_date" value="{{ request()->date_from }}">
                                    <input type="hidden" name="date_to" id="end_date" value="{{ request()->date_to }}">
                                </div>
                                <div class="form-group mb-2 mr-sm-2">
                                    <label for="type" class="sr-only">Type</label>
                                    <select class="form-control" id="type" name="type" style="min-width: 200px;">
                                        <option value="">ChooseTypes</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ ucfirst(str_replace('-', ' ', $type)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="btn btn-primary mb-2 mr-1" type="submit"><i class="fas fa-search"></i> Search</button>
                                <a href="{{ route('admin.subacc.tran.logs') }}" class="btn btn-outline-secondary mb-2" title="Refresh">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm" style="border-radius: 15px;">
                         <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                             <h5 class="m-0 font-weight-bold text-primary">Transfer Logs</h5>
                             <a href="{{ url()->previous() }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="mytable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Date</th>
                                            <th>ApprovedBy</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $creditTypes = ['withdraw-approve'];
                                            $debitTypes = ['deposit-approve', 'top_up'];
                                        @endphp
                                        @forelse($transferLogs as $log)
                                            <tr>
                                                <td>{{ $loop->iteration + ($transferLogs->currentPage() - 1) * $transferLogs->perPage() }}</td>
                                                <td>{{ $log->fromUser->user_name ?? '-' }}</td>
                                                <td>{{ $log->toUser->user_name ?? '-' }}</td>
                                                <td>
                                                    @if(in_array($log->type, $creditTypes))
                                                        <span class="badge badge-credit" style="font-size: 1em;">
                                                            + {{ number_format($log->amount, 2) }}
                                                        </span>
                                                    @elseif(in_array($log->type, $debitTypes))
                                                         <span class="badge badge-debit" style="font-size: 1em;">
                                                            - {{ number_format($log->amount, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-neutral" style="font-size: 1em;">
                                                            {{ number_format($log->amount, 2) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                     @if(in_array($log->type, $creditTypes))
                                                        <span class="badge badge-success">{{ ucfirst(str_replace('-', ' ', $log->type)) }}</span>
                                                    @elseif(in_array($log->type, $debitTypes))
                                                        <span class="badge badge-danger">{{ ucfirst(str_replace('-', ' ', $log->type)) }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ ucfirst(str_replace('-', ' ', $log->type)) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $log->description }}</td>
                                                <td>{{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Yangon')->format('d-m-Y H:i:s') }}</td>
                                                <td>{{ $log->sub_agent_name ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No transfer logs found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div>
                                {{ $transferLogs->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
@endsection
@section('script')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function() {
    var startDate = '{{ request()->date_from }}';
    var endDate = '{{ request()->date_to }}';

    var initialRanges = {};
    if (startDate && endDate) {
        initialRanges.startDate = moment(startDate);
        initialRanges.endDate = moment(endDate);
    }

    $('input[name="daterange"]').daterangepicker({
        ...initialRanges,
        opens: 'right',
        locale: {
          format: 'YYYY-MM-DD',
          cancelLabel: 'Clear'
        },
        autoUpdateInput: false
    });

    $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
        $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
    });

    $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $('#start_date').val('');
        $('#end_date').val('');
    });
    
    if (startDate && endDate) {
        $('input[name="daterange"]').val(moment(startDate).format('YYYY-MM-DD') + ' - ' + moment(endDate).format('YYYY-MM-DD'));
    }
});
</script>
@endsection 