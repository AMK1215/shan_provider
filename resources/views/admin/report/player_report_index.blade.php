@extends('layouts.master')

@section('style')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<!-- Daterange picker -->
<link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
<style>
    .digital-clock {
        font-family: 'Courier New', Courier, monospace;
        min-width: 160px;
        text-align: center;
        background: #222;
        border: 2px solid #007bff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection


@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div id="digitalClock" class="digital-clock bg-dark text-white rounded px-3 py-2 d-inline-block shadow-sm" style="font-size:1.5rem; letter-spacing:2px;"></div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <!-- <form method="GET" action="">
                <div class="form-row align-items-end">
                    <div class="col-md-5">
                        <label for="date_range">Date Range</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" class="form-control" id="date_range" readonly>
                        </div>
                        <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-5">
                        <label for="member_account">Player</label>
                        <input type="text" class="form-control" name="member_account" id="member_account" value="{{ request('member_account') }}" placeholder="Player Username">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Filter</button>
                    </div>
                </div>
            </form> -->
            <div class="row">
                <div class="col-md-12">
                <form method="GET" action="">
                <div class="form-row align-items-end">
                    <div class="col-auto">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-auto">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-auto">
                        <label for="member_account">Player</label>
                        <input type="text" class="form-control" name="member_account" id="member_account" value="{{ request('member_account') }}" placeholder="Player Username">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Player Report Table -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Player Report Summary</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="playerReportTable" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Player ID</th>
                            <th>Agent ID</th>
                            <!-- <th>Total Spins</th> -->
                            <th>Total Bet</th>
                            <th>Total Payout</th>
                            <th>Win/Lose</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $row)
                        <tr>
                            <td>{{ $row->player_user_name }}</td>
                            <td>{{ $row->agent_user_name }}</td>
                            <!-- <td>{{ $row->total_spins }}</td> -->
                            <td>{{ number_format($row->total_bet, 2) }}</td>
                            <td>{{ number_format($row->total_payout, 2) }}</td>
                            <td>
                                @if($row->win_lose >= 0)
                                <span class="text-success font-weight-bold">+{{ number_format($row->win_lose, 2) }}</span>
                                @else
                                <span class="text-danger font-weight-bold">{{ number_format($row->win_lose, 2) }}</span>
                                @endif
                            </td>
                            <td>
                            <a href="{{ route('admin.report.detail', ['member_account' => $row->player_user_name]) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Totals -->
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3 shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Bet Amount</h5>
                    <p class="card-text h3">{{ number_format($totals['total_bet'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3 shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Payout Amount</h5>
                    <p class="card-text h3">{{ number_format($totals['total_payout'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white {{ $totals['win_lose'] >= 0 ? 'bg-success' : 'bg-danger' }} mb-3 shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Win/Lose</h5>
                    <p class="card-text h3">
                        {{ $totals['win_lose'] >= 0 ? '+' : '' }}{{ number_format($totals['win_lose'], 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('script')
<!-- DataTables & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<!-- Moment.js -->
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<!-- Date-range-picker -->
<script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>

<script>
    $(function() {
        // Initialize DataTables
        $("#playerReportTable").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
        });

        // Initialize Daterangepicker
        var start = {{ request('start_date') ? 'moment("'.request('start_date').'")' : 'moment().startOf("day")' }};
        var end = {{ request('end_date') ? 'moment("'.request('end_date').'")' : 'moment().endOf("day")' }};

        function cb(start, end) {
            $('#date_range').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        }

        $('#date_range').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end);

    });

    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('digitalClock').textContent = `${h}:${m}:${s}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection