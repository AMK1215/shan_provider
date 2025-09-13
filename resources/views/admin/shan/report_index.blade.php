@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1>Shan Player Report</h1>
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
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reportTable" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Slip No</th>
                                <th>PlayerID</th>
                                <th>AgentID</th>
                                <th>ValidAmount</th>
                                <th>BetAmount</th>
                                <th>Payout</th>
                                <th>BeforeBalance</th>
                                <th>AfterBalance</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>{{ $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage() }}</td>
                                    <td>{{ $report->wager_code }}</td>

                                    <td>{{ $report->member_account }}</td>
                                    <td>{{ $report->agent->user_name ?? 'N/A' }}</td>
                                    <td>{{ number_format(is_numeric($report->valid_amount) ? $report->valid_amount : 0, 2) }}</td>
                                    <td>{{ number_format(is_numeric($report->bet_amount) ? $report->bet_amount : 0, 2) }}</td>
                                    <td>{{ number_format(is_numeric($report->transaction_amount) ? $report->transaction_amount : 0, 2) }}</td>
                                    <td>{{ number_format(is_numeric($report->before_balance) ? $report->before_balance : 0, 2) }}</td>
                                    <td>{{ number_format(is_numeric($report->after_balance) ? $report->after_balance : 0, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $report->settled_status == 'settled_win' ? 'success' : ($report->settled_status == 'settled_loss' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst(str_replace('settled_', '', $report->settled_status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $report->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No reports found for this filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('#reportTable').DataTable();
});
</script>
@endsection
