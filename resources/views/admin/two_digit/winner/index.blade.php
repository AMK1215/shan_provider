@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1>2D Daily Winners</h1>
            </div>
            <div class="col-sm-6">
                <form class="form-inline float-sm-right" method="GET">
                    <input type="date" name="date" class="form-control mr-2" value="{{ request('date', now()->toDateString()) }}">
                    <select name="session" class="form-control mr-2">
                        <option value="all" {{ request('session') == 'all' ? 'selected' : '' }}>All Sessions</option>
                        <option value="morning" {{ request('session') == 'morning' ? 'selected' : '' }}>Morning</option>
                        <option value="evening" {{ request('session') == 'evening' ? 'selected' : '' }}>Evening</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @if(isset($result) && is_array($result))
            <!-- Dual Session View (All Sessions) -->
            <div class="row">
                @foreach(['morning', 'evening'] as $session)
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header">
                                <h3 class="card-title">{{ ucfirst($session) }} Session - {{ request('date', now()->toDateString()) }}</h3>
                            </div>
                            <div class="card-body">
                                @if(isset($result[$session]['message']))
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> {{ $result[$session]['message'] }}
                                    </div>
                                @elseif(isset($result[$session]['win_digit']))
                                    <div class="text-center mb-4">
                                        <div class="alert alert-success">
                                            <h4 class="mb-2">
                                                <i class="fas fa-trophy text-warning"></i> 
                                                Winning Number: <span class="badge badge-success badge-lg">{{ $result[$session]['win_digit'] }}</span>
                                            </h4>
                                        </div>
                                    </div>
                                    
                                    @if(isset($result[$session]['winners']) && count($result[$session]['winners']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>Winning Number</th>
                                                        <th>Total Bet Amount</th>
                                                        <th>Win Amount (80x)</th>
                                                        <th>Winners Count</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($result[$session]['winners'] as $winner)
                                                        <tr class="table-success">
                                                            <td><strong>{{ $winner->bet_number }}</strong></td>
                                                            <td class="text-success font-weight-bold">
                                                                {{ number_format($winner->total_bet, 2) }}
                                                            </td>
                                                            <td class="text-warning font-weight-bold">
                                                                {{ number_format($winner->win_amount, 2) }}
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-success">Winner</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No winners found for this session.
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif(isset($results))
            <!-- Single Session View -->
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">{{ ucfirst(request('session', 'all')) }} Session - {{ request('date', now()->toDateString()) }}</h3>
                </div>
                <div class="card-body">
                    @if(isset($results->win_number))
                        <div class="text-center mb-4">
                            <div class="alert alert-success">
                                <h4 class="mb-2">
                                    <i class="fas fa-trophy text-warning"></i> 
                                    Winning Number: <span class="badge badge-success badge-lg">{{ $results->win_number }}</span>
                                </h4>
                                <p class="mb-0">
                                    <strong>Session:</strong> {{ ucfirst($results->session) }} | 
                                    <strong>Date:</strong> {{ $results->result_date }} | 
                                    <strong>Time:</strong> {{ $results->result_time }}
                                </p>
                            </div>
                        </div>
                        
                        @if(isset($winners) && count($winners) > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Winning Number</th>
                                            <th>Total Bet Amount</th>
                                            <th>Win Amount (80x)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($winners as $winner)
                                            <tr class="table-success">
                                                <td><strong>{{ $winner->bet_number }}</strong></td>
                                                <td class="text-success font-weight-bold">
                                                    {{ number_format($winner->total_bet, 2) }}
                                                </td>
                                                <td class="text-warning font-weight-bold">
                                                    {{ number_format($winner->win_amount, 2) }}
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">Winner</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> No winners found for this session.
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No winning result found for this session and date.
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- No Data Available -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No winner data available for the selected date and session.
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Summary Statistics -->
        @if(isset($result) && is_array($result))
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Summary Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $totalWinners = 0;
                            $totalBetAmount = 0;
                            $totalWinAmount = 0;
                            
                            foreach(['morning', 'evening'] as $session) {
                                if(isset($result[$session]['winners'])) {
                                    $totalWinners += count($result[$session]['winners']);
                                    foreach($result[$session]['winners'] as $winner) {
                                        $totalBetAmount += $winner->total_bet;
                                        $totalWinAmount += $winner->win_amount;
                                    }
                                }
                            }
                        @endphp
                        
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-trophy"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Winners</span>
                                    <span class="info-box-number">{{ $totalWinners }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-money-bill"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Bet Amount</span>
                                    <span class="info-box-number">{{ number_format($totalBetAmount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Win Amount</span>
                                    <span class="info-box-number">{{ number_format($totalWinAmount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Date</span>
                                    <span class="info-box-number">{{ request('date', now()->toDateString()) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

<script>
$(document).ready(function() {
    // Initialize DataTable for better UX
    $('.table').DataTable({
        "pageLength": 25,
        "order": [[0, "asc"]],
        "responsive": true
    });
});
</script>
@endsection
