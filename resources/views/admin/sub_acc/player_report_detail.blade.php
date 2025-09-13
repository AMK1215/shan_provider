@extends('layouts.master')

@section('content')
<section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Report Detail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Player Reports Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('home') }}" class="btn btn-success " style="width: 100px;"><i
                                class="fas fa-plus text-white  mr-2"></i>Back</a>
                    </div>
                    <div class="mb-3">
                        <div class="card">
                        <form method="GET" class="row g-3 mb-3">
    <div class="col-md-3">
        <label for="provider_name" class="form-label">Provider</label>
        <select name="provider_name" id="provider_name" class="form-control">
            <option value="">All</option>
            @foreach($providers as $provider)
                <option value="{{ $provider }}" {{ request('provider_name') == $provider ? 'selected' : '' }}>{{ $provider }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label for="start_date" class="form-label">Start Date</label>
        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
    </div>
    <div class="col-md-3">
        <label for="end_date" class="form-label">End Date</label>
        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
</form>
                        </div>
                    </div>
                        <div class="card">
                            <div class="card-body">
                       
            <table id="mytable" class="table table-bordered table-hover">

                        <thead>
            <tr>
                <th>#</th>
                <th>PlayerID</th>
                <th>Provider</th>
                <th>Game</th>
                <!-- <th>Game Type</th> -->
                <th>Wager Status</th>
                <th>Bet Amount</th>
                <th>Payout</th>
                <th>Win/Lost</th>
                <th>Before Balance</th>
                <th>After Balance</th>
                <th>Request Time</th>
                <!-- <th>Status</th> -->
            </tr>
        </thead>
        <tbody>
            @foreach($bets as $index => $bet)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $bet->member_account }}</td>
                <td>{{ $bet->provider_name }}</td>
                <td>{{ $bet->game_name }}</td>
                <!-- <td>{{ $bet->game_type }}</td> -->
                <td>{{ $bet->wager_status }}</td>
                <td>
                    @if($bet->currency == 'MMK2')
                    {{ number_format($bet->bet_amount * 1000, 2) }}
                    @else
                    {{ number_format($bet->bet_amount, 2) }}
                    @endif
                </td>
                <td>
                    @if($bet->currency == 'MMK2')
                    {{ number_format($bet->prize_amount * 1000, 2) }}
                    @else
                    {{ number_format($bet->prize_amount, 2) }}
                    @endif
                </td>
                <td>
                    @if($bet->currency == 'MMK2')
                    {{ number_format(($bet->prize_amount - $bet->bet_amount) * 1000, 2) }}
                    @else
                    {{ number_format($bet->prize_amount - $bet->bet_amount, 2) }}
                    @endif
                </td>
                <td>{{ number_format($bet->before_balance, 2) }}</td>
                <td>{{ number_format($bet->balance, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($bet->created_at)->timezone('Asia/Yangon')->format('d-m-Y H:i:s') }}</td>
                <!-- <td>{{ $bet->request_time }}</td> -->
                <!-- <td>{{ $bet->status }}</td> -->
            </tr>
            @endforeach
        </tbody>
                        </table>



                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                    <div class="row mb-4">
    <!-- <div class="col-md-3 col-6">
        <div class="card text-center shadow-sm border-success">
            <div class="card-body">
                <h6 class="text-success"><i class="fas fa-layer-group"></i> Total Stake</h6>
                <h3 class="fw-bold">{{ number_format($total_stake) }}</h3>
            </div>
        </div>
    </div> -->
    <div class="col-md-3 col-6">
        <div class="card text-center shadow-sm border-primary">
            <div class="card-body">
                <h6 class="text-primary"><i class="fas fa-coins"></i> Total Bet</h6>
                <h3 class="fw-bold">{{ number_format($total_bet, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mt-3 mt-md-0">
        <div class="card text-center shadow-sm border-info">
            <div class="card-body">
                <h6 class="text-info"><i class="fas fa-trophy"></i> Total Payout</h6>
                <h3 class="fw-bold">{{ number_format($total_win, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-6 mt-3 mt-md-0">
    <div class="card text-center shadow-sm border-{{ $net_win < 0 ? 'danger' : 'success' }}">
        <div class="card-body">
            @if($is_win)
                <h6 class="text-success">
                    <i class="fas fa-trophy"></i> Total Win
                </h6>
                <h3 class="fw-bold">{{ number_format(abs($net_win), 2) }}</h3>
            @elseif($is_lost)
                <h6 class="text-danger">
                    <i class="fas fa-times-circle"></i> Total Lost
                </h6>
                <h3 class="fw-bold">{{ number_format(abs($net_win), 2) }}</h3>
            @else
                <h6 class="text-secondary">
                    <i class="fas fa-equals"></i> Net Zero
                </h6>
                <h3 class="fw-bold">0.00</h3>
            @endif
        </div>
    </div>
</div>

    <!-- <div class="col-md-3 col-6 mt-3 mt-md-0">
        <div class="card text-center shadow-sm border-danger">
            <div class="card-body">
                <h6 class="text-danger"><i class="fas fa-times-circle"></i> Total Lost</h6>
                <h3 class="fw-bold">{{ number_format($total_lost, 2) }}</h3>
            </div>
        </div>
    </div> -->
</div>


                </div>

            </div>
        </div>
    </section>
@endsection

    @section('script')
    
@endsection
