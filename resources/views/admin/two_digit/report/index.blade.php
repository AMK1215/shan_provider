@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1>2D Bet Slips</h1>
            </div>
            <div class="col-sm-6">
                <form class="form-inline float-sm-right" method="GET">
                    <input type="date" name="date" class="form-control mr-2" value="{{ request('date', now()->toDateString()) }}">
                    <select name="session" class="form-control mr-2">
                        <option value="morning" {{ request('session') == 'morning' ? 'selected' : '' }}>Morning</option>
                        <option value="evening" {{ request('session') == 'evening' ? 'selected' : '' }}>Evening</option>
                    </select>
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
                    <table id="betSlipTable" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Slip No</th>
                                <th>User</th>
                                <th>Total Bet Amount</th>
                                <th>Before Balance</th>
                                <th>After Balance</th>
                                <th>Status</th>
                                <th>Session</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slips as $slip)
                                <tr>
                                    <td>{{ $loop->iteration + ($slips->currentPage() - 1) * $slips->perPage() }}</td>
                                    <td>{{ $slip->slip_no }}</td>
                                    <td>{{ optional($slip->user)->user_name }}</td>
                                    <td>{{ number_format($slip->total_bet_amount, 2) }}</td>
                                    <td>{{ number_format($slip->before_balance, 2) }}</td>
                                    <td>{{ number_format($slip->after_balance, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $slip->status == 'pending' ? 'warning' : ($slip->status == 'won' ? 'success' : 'secondary') }}">
                                            {{ ucfirst($slip->status) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($slip->session) }}</td>
                                    <td>{{ $slip->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.twod.bet-slip-details', $slip->id) }}" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No bet slips found for this filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $slips->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $('#betSlipTable').DataTable();
});
</script>
@endsection
