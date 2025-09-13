@extends('layouts.master')

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- Your Balance -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($user->wallet->balanceFloat ?? 0, 2) }}</h3>
                        <p>Your Balance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>

            <!-- Downline Total Balance -->
            <!-- @if(in_array($role, ['Owner', 'Agent', 'SubAgent']))
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($totalBalance / 100, 2) }}</h3>
                        <p>{{ $role }} Downline Balance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            @endif -->

            <!-- Player Balance -->
            @if($playerBalance > 0)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($playerBalance, 2) }}</h3>
                        <p>Player Balance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                </div>
            </div>
            @endif

            <!-- User Counts -->
            @if($totalOwner)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3>{{ $totalOwner }}</h3>
                        <p>Owners</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>
            @endif

            @if($totalAgent)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $totalAgent }}</h3>
                        <p>Agents</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-secret"></i>
                    </div>
                </div>
            </div>
            @endif

            @if($totalSubAgent)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>{{ $totalSubAgent }}</h3>
                        <p>Sub Agents</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chess-king"></i>
                    </div>
                </div>
            </div>
            @endif

            @if($totalPlayer)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $totalPlayer }}</h3>
                        <p>Players</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
            @endif

            <!-- Agent Stats -->
            <!-- @if(in_array($role, ['Agent', 'SubAgent']))
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalWinlose }}</h3>
                        <p>Total Win/Lose</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ $todayWinlose }}</h3>
                        <p>Today Win/Lose</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $todayDeposit }}</h3>
                        <p>Today's Deposit</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $todayWithdraw }}</h3>
                        <p>Today's Withdraw</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
            </div>
            @endif -->

            <!-- Owner Balance Top-Up -->
            @can('owner_access')
            <div class="col-lg-4 col-12">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Balance Top-Up</h3>
                    </div>
                    <form action="{{ route('admin.balanceUp') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="balance">Amount</label>
                                <input type="number" name="balance" id="balance" class="form-control" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-dark">Update Balance</button>
                        </div>
                    </form>
                </div>
            </div>
            @endcan

        </div>
    </div>
</section>
@endsection
