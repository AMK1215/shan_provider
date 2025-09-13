@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="mb-0">Player Bet History - <span class="text-primary">{{ $member_account }}</span></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.report.index') }}">Back to Summary</a></li>
                </ol>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-11 col-xl-10">
                <div class="card shadow rounded">
                    <div class="card-header bg-light border-bottom-0">
                        <h5 class="mb-0">Bet Details</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                        <table id="mytable" class="table table-bordered table-hover">

                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <!-- <th>Player ID</th> -->
                                        <!-- <th>Agent ID</th> -->
                                        <th>Provider</th>
                                        <th>Game</th>
                                        <!-- <th>Game Type</th> -->
                                        <th>Bet Amount</th>
                                        <th>Payout</th>
                                        <th>Win/Lose</th>
                                        <th>Before Balance</th>
                                        <th>After Balance</th>
                                        <!-- <th>Status</th> -->
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bets as $bet)
                                    <tr>
                                        <td>{{ $bet->id }}</td>
                                        <!-- <td>{{ $bet->player_id }}</td> -->
                                        <!-- <td>{{ $bet->player_agent_id }}</td> -->
                                        <td>{{ $bet->provider_name }}</td>
                                        <td>{{ $bet->game_name }}</td>
                                        <!-- <td>{{ $bet->game_type }}</td> -->
                                        <td class="text-right text-success">
                                            @if($bet->currency == 'MMK2')
                                            {{ number_format($bet->bet_amount * 1000, 2) }}
                                            @else
                                            {{ number_format($bet->bet_amount, 2) }}
                                            @endif
                                        </td>
                                        <td class="text-right text-info">
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
                                        <!-- <td>
                                            <span class="badge badge-{{ $bet->status === 'SETTLED' ? 'success' : 'secondary' }}">
                                                {{ $bet->status }}
                                            </span>
                                        </td> -->
                                        <td>{{ $bet->created_at ? $bet->created_at->timezone('Asia/Yangon')->format('m/d/Y, h:i:s A') : '' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="10" class="text-center">No data found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center my-4">
                            {{ $bets->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 