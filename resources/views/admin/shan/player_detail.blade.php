@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1>Player Detail Report - {{ $memberAccount }}</h1>
            </div>
            <div class="col-sm-6">
                <form class="form-inline float-sm-right" method="GET">
                    <input type="date" name="date_from" class="form-control mr-2" value="{{ request('date_from', now()->toDateString()) }}">
                    <input type="date" name="date_to" class="form-control mr-2" value="{{ request('date_to', now()->toDateString()) }}">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-12">
                <a href="{{ route('admin.shan.player.report.grouped') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Grouped Report
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($summary->total_transactions) }}</h3>
                        <p>Total Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-{{ $summary->total_transaction_amount >= 0 ? 'success' : 'danger' }}">
                    <div class="inner">
                        <h3>{{ number_format($summary->total_transaction_amount, 2) }}</h3>
                        <p>Total Transaction Amount</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($summary->total_bet_amount, 2) }}</h3>
                        <p>Total Bet Amount</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($summary->total_valid_amount, 2) }}</h3>
                        <p>Total Valid Amount</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Details Table -->
        <div class="card shadow">
            <div class="card-header">
                <h3 class="card-title">Individual Transactions</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="transactionTable" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Wager Code</th>
                                <th>Agent</th>
                                <th>Valid Amount</th>
                                <th>Bet Amount</th>
                                <th>Transaction Amount</th>
                                <th>Before Balance</th>
                                <th>After Balance</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}</td>
                                    <td>{{ $transaction->wager_code }}</td>
                                    <td>{{ $transaction->agent->user_name ?? 'N/A' }}</td>
                                    <td>{{ number_format(is_numeric($transaction->valid_amount) ? $transaction->valid_amount : 0, 2) }}</td>
                                    <td>{{ number_format(is_numeric($transaction->bet_amount) ? $transaction->bet_amount : 0, 2) }}</td>
                                    <td>
                                        <span class="text-{{ $transaction->transaction_amount >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format(is_numeric($transaction->transaction_amount) ? $transaction->transaction_amount : 0, 2) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format(is_numeric($transaction->before_balance) ? $transaction->before_balance : 0, 2) }}</td>
                                    <td>{{ number_format(is_numeric($transaction->after_balance) ? $transaction->after_balance : 0, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $transaction->settled_status == 'settled_win' ? 'success' : ($transaction->settled_status == 'settled_loss' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst(str_replace('settled_', '', $transaction->settled_status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No transactions found for this player.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('#transactionTable').DataTable({
        "paging": false,
        "searching": true,
        "ordering": true,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "order": [[ 9, "desc" ]] // Sort by created_at descending
    });
});
</script>
@endsection
