@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-8">
                <h1 class="mb-3">2D Bet Slip Details</h1>
                <div class="card bg-light mb-3 shadow-sm">
                    <div class="card-body d-flex flex-wrap align-items-center">
                        <div class="mr-4 mb-2">
                            <span class="text-secondary"><i class="fa fa-receipt"></i> <strong>Slip No:</strong></span>
                            <span class="ml-1 font-weight-bold text-primary">{{ $slip->slip_no }}</span>
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="text-secondary"><i class="fa fa-user"></i> <strong>User:</strong></span>
                            <span class="ml-1">{{ optional($slip->user)->user_name }}</span>
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="text-secondary"><i class="fa fa-coins"></i> <strong>Total Bet:</strong></span>
                            <span class="ml-1 text-success">{{ number_format($slip->total_bet_amount, 2) }}</span>
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="text-secondary"><i class="fa fa-clock"></i> <strong>Session:</strong></span>
                            <span class="ml-1">{{ ucfirst($slip->session) }}</span>
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="text-secondary"><i class="fa fa-info-circle"></i> <strong>Status:</strong></span>
                            <span class="ml-1 badge badge-{{ $slip->status == 'pending' ? 'warning' : ($slip->status == 'won' ? 'success' : 'secondary') }}">
                                {{ ucfirst($slip->status) }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary"><i class="fa fa-calendar-alt"></i> <strong>Placed At:</strong></span>
                            <span class="ml-1">{{ $slip->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 text-right">
                <a href="{{ route('admin.twod.bet-slip-list') }}" class="btn btn-outline-primary mt-2">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-list-ol"></i> Bets in this Slip</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    @if($bets->count())
                        <table class="table table-bordered table-hover table-striped align-middle">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>AgentID</th>
                                    <th>Player</th>
                                    <th>Number</th>
                                    <th>Amount</th>
                                    <th>Win/Lose</th>
                                    <th>Bet Time</th>
                                    <th>Bet Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bets as $i => $bet)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ optional($bet->agent)->user_name }}</td>
                                        <td>{{ optional($bet->user)->user_name }}</td>
                                        <td>
                                            <span class="font-weight-bold text-primary">{{ $bet->bet_number }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success">{{ number_format($bet->bet_amount, 2) }}</span>
                                        </td>
                                        
                                        <td>
                                            @if($bet->win_lose)
                                                <span class="badge badge-success"><i class="fa fa-trophy"></i> Win</span>
                                            @else
                                                <span class="badge badge-danger"><i class="fa fa-times-circle"></i> Lose</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($bet->game_time)->setTimezone('Asia/Yangon')->format('H:i:s') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($bet->game_date)->setTimezone('Asia/Yangon')->format('Y-m-d') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info text-center">No bets found for this slip.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('.table').DataTable();
});
</script>
@endsection
