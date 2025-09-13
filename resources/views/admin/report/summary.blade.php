@extends('layouts.master') {{-- Or your layout file --}}

@section('content')
<div class="container">
    <h2 class="mb-4">Player Bet Summary</h2>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Player Username</th>
                <th>Total Stake</th>
                <th>Total Bet</th>
                <th>Total Win</th>
                <th>Total Lost</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($summary as $record)
                <tr>
                    <td>{{ $record->member_account }}</td>
                    <td>{{ number_format($record->total_stake, 2) }}</td>
                    <td>{{ number_format($record->total_bet, 2) }}</td>
                    <td>{{ number_format($record->total_win, 2) }}</td>
                    <td>{{ number_format($record->total_lost, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No report data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
