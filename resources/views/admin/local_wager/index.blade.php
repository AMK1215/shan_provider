@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Local Wager List</h1>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="get" class="mb-3 form-inline">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control mx-1" placeholder="Start Date">
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control mx-1" placeholder="End Date">
                            <input type="text" name="member_account" value="{{ request('member_account') }}" class="form-control mx-1" placeholder="Member Account">
                            <select name="status" class="form-control mx-1">
                                <option value="">All Status</option>
                                <option value="BET" @if(request('status')=='BET') selected @endif>BET</option>
                                <option value="SETTLED" @if(request('status')=='SETTLED') selected @endif>SETTLED</option>
                            </select>
                            <button type="submit" class="btn btn-primary mx-1">Filter</button>
                        </form>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member Account</th>
                                    <th>Round ID</th>
                                    <th>Currency</th>
                                    <th>Provider ID</th>
                                    <th>Game Type</th>
                                    <th>Game Code</th>
                                    <th>Bet Amount</th>
                                    <th>Prize Amount</th>
                                    <th>Status</th>
                                    <th>Created At (API)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wagers as $wager)
                                <tr>
                                    <td>{{ $wager->id }}</td>
                                    <td>{{ $wager->member_account }}</td>
                                    <td>{{ $wager->round_id }}</td>
                                    <td>{{ $wager->currency }}</td>
                                    <td>{{ $wager->provider_id }}</td>
                                    <td>{{ $wager->game_type }}</td>
                                    <td>{{ $wager->game_code }}</td>
                                    <td>{{ $wager->bet_amount }}</td>
                                    <td>{{ $wager->prize_amount }}</td>
                                    <td>{{ $wager->status }}</td>
                                    <td>{{ $wager->created_at_api ? date('m/d/Y, h:i:s A', $wager->created_at_api/1000) : '' }}</td>
                                    <td><a href="{{ route('admin.local-wager.show', $wager->id) }}" class="btn btn-info btn-sm" target="_blank">View</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="12" class="text-center">No wagers found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div>{{ $wagers->withQueryString()->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 