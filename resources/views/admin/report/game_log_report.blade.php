@extends('layouts.admin_app')
@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .transparent-btn {
        background: transparent;
        border: none;
        box-shadow: none;
        color: #fff;
    }

    .custom-btn-group {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .custom-btn-group .btn {
        border-radius: 5px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .tab-content {
        background: #333;
        padding: 1rem;
        border-radius: 0 0 5px 5px;
    }
    .nav-tabs .nav-link {
        border: 1px solid #555;
        border-bottom: none;
        background: #444;
        color: #fff;
    }
    .nav-tabs .nav-link.active {
        background: #333;
        border-color: #555;
        color: #a589d1;
    }
    .date-filter-btn {
        background-color: #555;
        border: 1px solid #777;
    }
    .date-filter-btn.active {
        background-color: #a589d1;
        color: #fff;
    }
    .table-dark-custom {
        background-color: #2a2a2a;
        color: #fff;
    }
    .table-dark-custom th, .table-dark-custom td, .table-dark-custom thead th {
        border-color: #454d55;
    }
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.agent.deposit') }}">Deposit</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.agent.withdraw') }}">Withdraw</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.reports.game_log_report') }}">Game Log</a>
                </li>
            </ul>
            <div class="tab-content">
                 <div class="d-flex justify-content-between mb-3">
                    <div class="btn-group">
                        <button id="todayBtn" class="btn btn-sm date-filter-btn">Today</button>
                        <button id="yesterdayBtn" class="btn btn-sm date-filter-btn">Yesterday</button>
                        <button id="thisWeekBtn" class="btn btn-sm date-filter-btn">This Week</button>
                        <button id="lastWeekBtn" class="btn btn-sm date-filter-btn">Last Week</button>
                    </div>

                    <form action="{{ route('admin.reports.game_log_report') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="daterange" class="form-control" value="" />
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>


                <div class="table-responsive">
                <table class="table table-dark-custom">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>Game Name</th>
                            <th>Count</th>
                            <th>Bet Amount</th>
                            <th>Win/Lose</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gameLogs as $log)
                        <tr>
                           <td>{{ $from }}</td>
                           <td>{{ $to }}</td>
                           <td>{{ $log->game_name }}</td>
                           <td>{{ $log->spin_count }}</td>
                           <td>{{ number_format($log->turnover, 2) }}</td>
                           <td class="{{ $log->win_loss >= 0 ? 'text-success' : 'text-danger' }}">
                               {{ number_format($log->win_loss, 2) }}
                           </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No Data Found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function() {
    var from = "{{ $from }}";
    var to = "{{ $to }}";

    let dateRangePicker = $('input[name="daterange"]').daterangepicker({
        opens: 'left',
        startDate: moment(from),
        endDate: moment(to),
        locale: {
          format: 'YYYY-MM-DD'
        }
    });

    $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
        var newUrl = new URL(window.location.href);
        newUrl.searchParams.set('from', picker.startDate.format('YYYY-MM-DD'));
        newUrl.searchParams.set('to', picker.endDate.format('YYYY-MM-DD'));
        
        // Remove daterange from params to avoid conflict
        newUrl.searchParams.delete('daterange');
        
        window.location.href = newUrl.toString();
    });
     $('form').submit(function() {
        var range = $('input[name="daterange"]').val().split(' - ');
        var from = moment(range[0], 'YYYY-MM-DD').format('YYYY-MM-DD');
        var to = moment(range[1], 'YYYY-MM-DD').format('YYYY-MM-DD');

        $(this).append('<input type="hidden" name="from" value="' + from + '" />');
        $(this).append('<input type="hidden" name="to" value="' + to + '" />');
        $(this).find('input[name="daterange"]').remove();
    });

    const setDateRange = (start, end) => {
        const url = new URL(window.location.href);
        url.searchParams.set('from', start.format('YYYY-MM-DD'));
        url.searchParams.set('to', end.format('YYYY-MM-DD'));
        window.location.href = url.toString();
    };

    $('#todayBtn').on('click', () => setDateRange(moment(), moment()));
    $('#yesterdayBtn').on('click', () => setDateRange(moment().subtract(1, 'days'), moment().subtract(1, 'days')));
    $('#thisWeekBtn').on('click', () => setDateRange(moment().startOf('week'), moment().endOf('week')));
    $('#lastWeekBtn').on('click', () => {
        const startOfLastWeek = moment().subtract(1, 'week').startOf('week');
        const endOfLastWeek = moment().subtract(1, 'week').endOf('week');
        setDateRange(startOfLastWeek, endOfLastWeek);
    });

    // Highlight active button
    const urlParams = new URLSearchParams(window.location.search);
    const fromParam = urlParams.get('from');
    const toParam = urlParams.get('to');

    if (fromParam && toParam) {
        if (fromParam === moment().format('YYYY-MM-DD') && toParam === moment().format('YYYY-MM-DD')) {
            $('#todayBtn').addClass('active');
        } else if (fromParam === moment().subtract(1, 'days').format('YYYY-MM-DD') && toParam === moment().subtract(1, 'days').format('YYYY-MM-DD')) {
            $('#yesterdayBtn').addClass('active');
        } else if (fromParam === moment().startOf('week').format('YYYY-MM-DD') && toParam === moment().endOf('week').format('YYYY-MM-DD')) {
            $('#thisWeekBtn').addClass('active');
        } else if (fromParam === moment().subtract(1, 'week').startOf('week').format('YYYY-MM-DD') && toParam === moment().subtract(1, 'week').endOf('week').format('YYYY-MM-DD')) {
            $('#lastWeekBtn').addClass('active');
        }
    } else {
        $('#todayBtn').addClass('active');
    }
});
</script>
@endsection 