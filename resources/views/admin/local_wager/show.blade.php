@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Wager Detail (Local)</h1>
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
                        <table class="table table-bordered">
                            <tbody>
                                <tr><th>ID</th><td>{{ $wager->id }}</td></tr>
                                <tr><th>Wager Code</th><td>{{ $wager->code }}</td></tr>
                                <tr><th>Member Account</th><td>{{ $wager->member_account }}</td></tr>
                                <tr><th>Round ID</th><td>{{ $wager->round_id }}</td></tr>
                                <tr><th>Currency</th><td>{{ $wager->currency }}</td></tr>
                                <tr><th>Provider ID</th><td>{{ $wager->provider_id }}</td></tr>
                                <tr><th>Provider Line ID</th><td>{{ $wager->provider_line_id }}</td></tr>
                                <tr><th>Provider Product ID</th><td>{{ $wager->provider_product_id }}</td></tr>
                                <tr><th>Provider Product OID</th><td>{{ $wager->provider_product_oid }}</td></tr>
                                <tr><th>Game Type</th><td>{{ $wager->game_type }}</td></tr>
                                <tr><th>Game Code</th><td>{{ $wager->game_code }}</td></tr>
                                <tr><th>Valid Bet Amount</th><td>{{ $wager->valid_bet_amount }}</td></tr>
                                <tr><th>Bet Amount</th><td>{{ $wager->bet_amount }}</td></tr>
                                <tr><th>Prize Amount</th><td>{{ $wager->prize_amount }}</td></tr>
                                <tr><th>Status</th><td>{{ $wager->status }}</td></tr>
                                <tr><th>Payload</th><td><pre>{{ json_encode($wager->payload, JSON_PRETTY_PRINT) }}</pre></td></tr>
                                <tr><th>Settled At</th><td>{{ $wager->settled_at ? date('m/d/Y, h:i:s A', $wager->settled_at/1000) : '' }}</td></tr>
                                <tr><th>Created At (API)</th><td>{{ $wager->created_at_api ? date('m/d/Y, h:i:s A', $wager->created_at_api/1000) : '' }}</td></tr>
                                <tr><th>Updated At (API)</th><td>{{ $wager->updated_at_api ? date('m/d/Y, h:i:s A', $wager->updated_at_api/1000) : '' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 