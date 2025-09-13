@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1>2D Bet Daily Ledger</h1>
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
        @if(isset($result))
            <!-- Single Session View -->
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">{{ ucfirst(request('session', 'all')) }} Session - {{ request('date', now()->toDateString()) }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Number</th>
                                    <th>Total Bet Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($result as $number => $amount)
                                    <tr>
                                        <td><strong>{{ $number }}</strong></td>
                                        <td class="{{ $amount > 0 ? 'text-success font-weight-bold' : 'text-muted' }}">
                                            {{ number_format($amount, 2) }}
                                        </td>
                                        <td>
                                            @if($amount > 0)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">No Bets</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- Dual Session View -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header">
                            <h3 class="card-title">Morning Session - {{ request('date', now()->toDateString()) }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Number</th>
                                            <th>Total Bet Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($morning as $number => $amount)
                                            <tr>
                                                <td><strong>{{ $number }}</strong></td>
                                                <td class="{{ $amount > 0 ? 'text-success font-weight-bold' : 'text-muted' }}">
                                                    {{ number_format($amount, 2) }}
                                                </td>
                                                <td>
                                                    @if($amount > 0)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">No Bets</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header">
                            <h3 class="card-title">Evening Session - {{ request('date', now()->toDateString()) }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Number</th>
                                            <th>Total Bet Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($evening as $number => $amount)
                                            <tr>
                                                <td><strong>{{ $number }}</strong></td>
                                                <td class="{{ $amount > 0 ? 'text-success font-weight-bold' : 'text-muted' }}">
                                                    {{ number_format($amount, 2) }}
                                                </td>
                                                <td>
                                                    @if($amount > 0)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">No Bets</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Summary Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Summary Statistics</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(isset($result))
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-chart-bar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Active Numbers</span>
                                    <span class="info-box-number">{{ $result->filter(function($amount) { return $amount > 0; })->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-money-bill"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Bet Amount</span>
                                    <span class="info-box-number">{{ number_format($result->sum(), 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Session</span>
                                    <span class="info-box-number">{{ ucfirst(request('session', 'all')) }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-sun"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Morning Active</span>
                                    <span class="info-box-number">{{ $morning->filter(function($amount) { return $amount > 0; })->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-moon"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Evening Active</span>
                                    <span class="info-box-number">{{ $evening->filter(function($amount) { return $amount > 0; })->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-money-bill"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Amount</span>
                                    <span class="info-box-number">{{ number_format($morning->sum() + $evening->sum(), 2) }}</span>
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
                    @endif
                </div>
            </div>
        </div>
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
