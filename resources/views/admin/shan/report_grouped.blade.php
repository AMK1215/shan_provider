@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1>Shan Player Report - Grouped by Member Account</h1>
            </div>
            <div class="col-sm-6">
                <form class="form-inline float-sm-right" method="GET">
                    <input type="date" name="date_from" class="form-control mr-2" value="{{ request('date_from', now()->toDateString()) }}">
                    <input type="date" name="date_to" class="form-control mr-2" value="{{ request('date_to', now()->toDateString()) }}">
                    <input type="text" name="member_account" class="form-control mr-2" placeholder="Member Account" value="{{ request('member_account') }}">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-12">
                <a href="{{ route('admin.shan.player.report') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i> View Individual Reports
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="groupedReportTable" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Member Account</th>
                                <th>Total Transactions</th>
                                <th>Total Transaction Amount</th>
                                <th>Total Bet Amount</th>
                                <th>Total Valid Amount</th>
                                <th>Avg Before Balance</th>
                                <th>Avg After Balance</th>
                                <th>First Transaction</th>
                                <th>Last Transaction</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groupedReports as $report)
                                <tr>
                                    <td>{{ $loop->iteration + ($groupedReports->currentPage() - 1) * $groupedReports->perPage() }}</td>
                                    <td>
                                        <strong>{{ $report->member_account }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ number_format($report->total_transactions) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-{{ $report->total_transaction_amount >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($report->total_transaction_amount, 2) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($report->total_bet_amount, 2) }}</td>
                                    <td>{{ number_format($report->total_valid_amount, 2) }}</td>
                                    <td>{{ number_format($report->avg_before_balance, 2) }}</td>
                                    <td>{{ number_format($report->avg_after_balance, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($report->first_transaction)->format('Y-m-d H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($report->last_transaction)->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.shan.player.report.detail', $report->member_account) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted">No grouped reports found for this filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $groupedReports->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('#groupedReportTable').DataTable({
        "paging": false,
        "searching": true,
        "ordering": true,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "order": [[ 3, "desc" ]] // Sort by total transaction amount descending
    });
});
</script>
@endsection
