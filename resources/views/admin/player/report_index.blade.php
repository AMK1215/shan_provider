@extends('layouts.master')
@section('content')
    <div class="container mt-4 col-12">
        <!-- Report Header with Filters -->
        <div class="card shadow-sm">
            <div class="card-header bg-white sticky-top shadow-sm">
                <h5 class="mb-0 d-flex justify-content-between align-items-center">
                    Game Report Detail
                    <a href="{{route('admin.player.index')}}" class="btn btn-primary col-1">Back</a>
                </h5>
            </div>
            <div class="card-body text-center ">
                <form method="GET" >
                    <div class="row">
                        <!-- Date Range Filter -->
                        <div class="col-md-2"></div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date"
                                value="{{ request()->start_date }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date"
                                value="{{ request()->end_date }}">
                        </div>

                        <!-- User ID Filter -->
                        {{-- <div class="col-md-3">
                            <label for="user_id" class="form-label">User ID</label>
                            <input type="text" class="form-control" name="user_id" placeholder="Enter User ID"
                                value="{{ request()->user_id }}">
                        </div> --}}

                        <!-- Search Button -->
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card mt-3 shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Player ID</th>
                            <th>Provider</th>
                            <th>Game Name</th>
                            <th>Game round id</th>
                            <th>Bet Amounts</th>
                            <th>Payout Amounts</th>
                            <th>Net Win Amounts</th>
                            <th>Bet Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportDetail as $data)
                            <tr class="text-center "  style="font-size: 14px !important;">
                                <td>{{ $data->member_name }}</td>
                                <td>{{ $data->provider_name }}</td>
                                <td>{{ $data->game_name }}</td>
                                <td>{{ $data->game_round_id }}</td>
                                {{-- <td>{{ $data->valid_bet_amount }}</td> --}}
                                <td class="text-bold">{{ number_format($data->bet_amount, 2) }}</td>
                                {{-- <td>{{ number_format($data->total_win_amount, 2) }}</td> --}}
                                <td class="text-bold">{{ number_format($data->payout_amount, 2) }}</td>
                                <?php
                                    $netWin =  number_format(  $data->payout_amount - $data->bet_amount, 2);
                                ?>
                                <td class="{{$netWin >= 0 ? "text-success" : 'text-danger'}} text-bold">{{$netWin}}</td>

                                <td>{{ \Carbon\Carbon::parse($data->created_at)->timezone('Asia/Yangon')->format('Y-m-d H:i:s') }}
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                    <tfoot class="text-center">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>Total Amount</th>
                        <th>{{ number_format($total['total_bet_amt'],2)}}</th>
                        <th>{{ number_format($total['total_payout_amt'],2)}}</th>
                        <th class="{{$total['total_net_win'] >= 0 ? 'text-success' : 'text-danger'}}">{{ number_format($total['total_net_win'],2)}}</th>
                        <th></th>
                    </tfoot>
                </table>
                <div class="text-center " style="font-weight: bold;">
                    {{$reportDetail->links()}}
                </div>

@if ($reportDetail->isEmpty())
<div class="text-center text-danger mt-3" style="font-weight: bold;">
    🔍 Data not found!
</div>
@endif


            </div>
        </div>
    </div>


    @endsection
