@extends('layouts.master')

@section('style')
<style>
.digital-clock {
    font-family: 'Courier New', Courier, monospace;
    min-width: 160px;
    text-align: center;
    background: #222;
    border: 2px solid #007bff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>
@endsection


@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
           
            <div class="row mb-3">
    <div class="col-12">
        <div id="digitalClock" class="digital-clock bg-dark text-white rounded px-3 py-2 d-inline-block shadow-sm" style="font-size:1.5rem; letter-spacing:2px;"></div>
    </div>
</div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12">
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
    <div class="row mb-3 justify-content-center">
        <div class="col-12 col-lg-11 col-xl-10">
            <div class="card shadow rounded">
                <div class="card-header bg-light border-bottom-0">
                    <h5 class="mb-0">Player Report Summary Table</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table id="mytable" class="table table-bordered table-hover">

                            <thead class="thead-light">
                                <tr>
                                    <th>PlayerID</th>
                                    <th>AgentID</th>
                                    <th>Total Bet</th>
                                    <th>TotalPayoutAmount</th>
                                    <th>Win/Lose</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report as $row)
                                    <tr>
                                        <td>{{ $row->member_account }}</td>
                                        <td>{{ $row->agent_name }}</td>
                                        <!-- <td><span class="badge badge-info">{{ $row->stake_count }}</span></td> -->
                                        <td class="text-right">{{ number_format($row->total_bet, 2) }}</td>
                                        <td class="text-right">{{ number_format($row->total_win, 2) }}</td>
                                            <td >
                                                @if($row->total_win > $row->total_bet)
                                                <span class="text-success">+ {{ number_format($row->total_win - $row->total_bet, 2) }}</span>
                                            @else
                                                <span class="text-danger">- {{ number_format($row->total_bet - $row->total_win, 2) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.report.detail', ['member_account' => $row->member_account]) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No data found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3 justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card shadow rounded">
                <div class="card-body text-center">
                    <h5 class="mb-3">Totals</h5>
                    <ul class="list-group list-group-flush">
                        <!-- <li class="list-group-item">Total Stake Count: <strong>{{ $total['totalstake'] }}</strong></li> -->
                        <li class="list-group-item">Total Bet Amount: <strong class="text-dark">{{ number_format($total['totalBetAmt'], 2) }}</strong></li>
                        <li class="list-group-item">Total Payout Amount: <strong class="text-dark">{{ number_format($total['totalWinAmt'], 2) }}</strong></li>
                        <li class="list-group-item">
                            @if($total['totalWinAmt'] > $total['totalBetAmt'])
                                Total Win/Lose: <strong class="text-success">
                                   + {{ number_format($total['totalWinAmt'] - $total['totalBetAmt'], 2) }}
                            </strong>
                            @else
                                Total Win/Lose: <strong class="text-danger">
                                    - {{ number_format($total['totalBetAmt'] - $total['totalWinAmt'], 2) }}
                                </strong>
                            @endif
                    </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 

@section('script')
<script>
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