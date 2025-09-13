@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Transfer Logs</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Filtered Deposits</span>
                                    <span class="info-box-number">{{ number_format($dailyTotalDeposit, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-arrow-down"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Filtered Withdrawals</span>
                                    <span class="info-box-number">{{ number_format($dailyTotalWithdraw, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Filtered Profit</span>
                                    <span class="info-box-number">{{ number_format($dailyProfit, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-piggy-bank"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">All-Time Deposits</span>
                                    <span class="info-box-number">{{ number_format($allTimeTotalDeposit, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-hand-holding-usd"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">All-Time Withdrawals</span>
                                    <span class="info-box-number">{{ number_format($allTimeTotalWithdraw, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-purple elevation-1"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">All-Time Profit</span>
                                    <span class="info-box-number">{{ number_format($allTimeProfit, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Filters -->
                    <form action="{{ route('admin.transfer-logs.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type">Transfer Type</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">ChooseTypes</option>
                                        <option value="top_up" {{ request('type') == 'top_up' ? 'selected' : '' }}>TopUp</option>
                                        <option value="withdraw" {{ request('type') == 'withdraw' ? 'selected' : '' }}>Withdraw</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_from">Date From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_to">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Transfer Logs Table -->
                    <div class="table-responsive">
                    <table id="mytable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    @canany(['subagent_access', 'agent_access'])
                                    <th>ApprovedBy</th>
                                    @endcanany
                                    <!-- <th>Action</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transferLogs as $log)
                                <tr>
                                    <td>{{ $log->fromUser->user_name ?? 'N/A' }}</td>
                                    <td>{{ $log->toUser->user_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($log->type === 'top_up' || $log->type === 'deposit')
                                            <span class="badge badge-success">
                                               + {{ number_format($log->amount, 2) }}
                                            </span>
                                        @elseif($log->type === 'withdraw' || $log->type === 'refund')
                                          <span class="badge badge-danger">
                                            - {{ number_format($log->amount, 2) }}
                                          </span>
                                        @else
                                            <span class="badge badge-info">
                                                {{ number_format($log->amount, 2) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = 'badge-secondary';
                                            if ($log->type === 'top_up' || $log->type === 'deposit') {
                                                $badgeClass = 'badge-success';
                                            } elseif ($log->type === 'withdraw' || $log->type === 'refund') {
                                                $badgeClass = 'badge-danger';
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $log->description }}</td>
                                    @canany(['subagent_access', 'agent_access'])
                                    <td>{{ $log->sub_agent_name ?? 'N/A' }}</td>
                                    @endcanany
                                    <!-- <td>
                                        <a href="{{ route('admin.PlayertransferLogDetail', $log->id) }}" class="btn btn-primary">View</a>
                                    </td> -->
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No transfer logs found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $transferLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 