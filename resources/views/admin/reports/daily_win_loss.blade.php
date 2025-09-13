@extends('layouts.master')
@section('style')
<style>
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
    }
    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #e3e6f0;
        font-weight: 600;
    }
    .win-loss-positive {
        color: #155724;
        background-color: #d4edda;
        font-weight: 600;
    }
    .win-loss-negative {
        color: #721c24;
        background-color: #f8d7da;
        font-weight: 600;
    }
</style>
@endsection
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Daily Win/Loss Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Daily Win/Loss Report</li>
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
                        <h5 class="m-0 font-weight-bold text-primary">Select Date</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.reports.daily_win_loss') }}" method="GET" class="form-inline">
                            <div class="form-group mb-2 mr-sm-2">
                                <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
                            </div>
                            <button class="btn btn-primary mb-2" type="submit"><i class="fas fa-search"></i> View Report</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm" style="border-radius: 15px;">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h5 class="m-0 font-weight-bold text-primary">Report for {{ $date->format('d M, Y') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Player</th>
                                        <th>Total Turnover</th>
                                        <th>Total Payout</th>
                                        <th>Win/Loss</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dailyReports as $report)
                                    @php
                                        $winLoss = $report->total_payout - $report->total_turnover;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $report->user_name }}</td>
                                        <td>{{ number_format($report->total_turnover, 2) }}</td>
                                        <td>{{ number_format($report->total_payout, 2) }}</td>
                                        <td class="{{ $winLoss >= 0 ? 'win-loss-positive' : 'win-loss-negative' }}">
                                            {{ number_format($winLoss, 2) }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No data found for the selected date.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-right">Grand Totals:</th>
                                        <th>{{ number_format($totalTurnover, 2) }}</th>
                                        <th>{{ number_format($totalPayout, 2) }}</th>
                                        <th class="{{ $totalWinLoss >= 0 ? 'win-loss-positive' : 'win-loss-negative' }}">
                                            {{ number_format($totalWinLoss, 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 