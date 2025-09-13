@extends('layouts.master')
@section('style')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
    }
    .form-label {
        font-weight: 600;
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
    .badge {
        padding: 0.5em 0.9em;
        font-size: 0.9em;
    }
    .action-buttons .btn {
        margin-right: 5px;
    }
    .card-footer {
        background-color: #f8f9fa;
    }
</style>
@endsection
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Withdraw Request Lists</li>
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
                    <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                        <div class="card-header py-3">
                            <h5 class="m-0 font-weight-bold text-primary">Withdraw Request Filters</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.agent.withdraw') }}" method="GET" class="form-inline">
                                <div class="form-group mb-2 mr-sm-2">
                                    <label for="daterange" class="sr-only">Date Range</label>
                                    <input type="text" name="daterange" class="form-control" placeholder="Select Date Range" style="min-width: 240px;">
                                    <input type="hidden" name="start_date" id="start_date" value="{{ request()->start_date }}">
                                    <input type="hidden" name="end_date" id="end_date" value="{{ request()->end_date }}">
                                </div>
                                <div class="form-group mb-2 mr-sm-2">
                                    <label for="status" class="sr-only">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="all" {{ request()->get('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                                        <option value="0" {{ request()->get('status') == '0' ? 'selected' : '' }}>Pending</option>
                                        <option value="1" {{ request()->get('status') == '1' ? 'selected' : '' }}>Approved</option>
                                        <option value="2" {{ request()->get('status') == '2' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary mb-2 mr-1" type="submit"><i class="fas fa-search"></i> Search</button>
                                <a href="{{ route('admin.agent.withdraw') }}" class="btn btn-outline-secondary mb-2" title="Refresh">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm" style="border-radius: 15px;">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                             <h5 class="m-0 font-weight-bold text-primary">Withdraw Request Lists</h5>
                             <a href="{{ url()->previous() }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="mytable" class="table table-bordered table-hover">
                                    <thead>
                                        <th>#</th>
                                        <th>PlayerId</th>
                                        <th>PlayerName</th>
                                        <th>Requested Amount</th>
                                        <th>Payment Method</th>
                                        <th>Bank Account Name</th>
                                        <th>Bank Account Number</th>
                                        <th>Status</th>
                                        <th>DateTime</th>
                                        <th style="min-width:180px;">Action</th>
                                    </thead>
                                    <tbody>
                                        @forelse ($withdraws as $withdraw)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $withdraw->user->user_name }}</td>
                                                <td>{{ $withdraw->user->name }}</td>
                                                <td>{{ number_format($withdraw->amount) }}</td>
                                                <td>{{ $withdraw->paymentType->name ?? 'N/A' }}</td>
                                                <td>{{ $withdraw->account_name }}</td>
                                                <td>{{ $withdraw->account_number }}</td>
                                                <td>
                                                    @if ($withdraw->status == 0)
                                                        <span class="badge badge-warning"><i class="fas fa-hourglass-half mr-1"></i>Pending</span>
                                                    @elseif ($withdraw->status == 1)
                                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Approved</span>
                                                    @elseif ($withdraw->status == 2)
                                                        <span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Rejected</span>
                                                    @endif
                                                </td>
                                                <td>{{ $withdraw->created_at->setTimezone('Asia/Yangon')->format('d-m-Y H:i:s') }}</td>
                                                <td class="action-buttons">
                                                    @if ($withdraw->status == 0)
                                                        <form action="{{ route('admin.agent.withdrawStatusUpdate', $withdraw->id) }}" method="post" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="amount" value="{{ $withdraw->amount }}">
                                                            <input type="hidden" name="status" value="1">
                                                            <input type="hidden" name="player" value="{{ $withdraw->user_id }}">
                                                            <button class="btn btn-success btn-sm" type="submit" title="Approve">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.agent.withdrawStatusreject', $withdraw->id) }}" method="post" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="2">
                                                            <button class="btn btn-danger btn-sm" type="submit" title="Reject">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <a href="{{ route('admin.agent.withdrawLog', $withdraw->id) }}" class="btn btn-info btn-sm" title="View Details"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No withdraw requests found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($withdraws->count() > 0)
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Total Amounts:</th>
                                            <th>{{number_format($totalWithdraws)}}</th>
                                            <th colspan="2" class="text-right">Total Withdraw Count:</th>
                                            <th>{{$withdraws->count()}}</th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                    @endif
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
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function() {
    var startDate = '{{ request()->start_date }}';
    var endDate = '{{ request()->end_date }}';

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
