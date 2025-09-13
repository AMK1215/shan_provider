@extends('layouts.master')

@section('content')
<section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Agent List</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Player List</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('admin.subacc.player.create') }}" class="btn btn-success " style="width: 100px;"><i
                                class="fas fa-plus text-white  mr-2"></i>Create</a>
                    </div>
                    <div class="card">
                        <div class="card-body">
                        <h2>Players of Agent: {{ $agent->name }}</h2>

<form method="GET" class="row g-3 mb-3">
    <div class="col-md-4">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, username, or phone">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary" type="submit">Filter</button>
    </div>
    <div class="col-md-2">
        <a href="{{ route('admin.subacc.agent_players') }}" class="btn btn-secondary">Reset</a>
    </div>
</form>

<table id="mytable" class="table table-bordered table-hover">
    <thead>
        <tr>
        <th>ID</th>
    <th>Name</th>
    <th>Username</th>
    <th>Phone</th>
    <th>Balance</th>
    <th>Total Stake</th>
    <th>Total Bet</th>
    <th>Total Payout</th>
    <th>Min Before Balance</th>
    <th>Max Balance</th>
    <th>Status</th>
    <th>Actions</th>
    <th>Finance</th>
            
        </tr>
    </thead>
    <tbody>
    @forelse($players as $player)
        <tr>
        <td>{{ $player->id }}</td>
    <td>{{ $player->name }}</td>
    <td>{{ $player->user_name }}</td>
    <td>{{ $player->phone }}</td>
    <td>{{ number_format($player->balanceFloat, 2) }}</td>
    <td>{{ $player->total_stake }}</td>
    <td>{{ number_format($player->total_bet, 2) }}</td>
    <td>{{ number_format($player->total_payout, 2) }}</td>
    <td>{{ number_format($player->min_before_balance, 2) }}</td>
    <td>{{ number_format($player->max_balance, 2) }}</td>
            <td>
                @if($player->status)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-danger">Inactive</span>
                @endif
            </td>
            <td>
            @if ($player->status == 1)
                <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $player->id }}').submit();"
                    class="me-2" href="#" data-bs-toggle="tooltip"
                    data-bs-original-title="Active Player">
                    <i class="fas fa-user-check text-success"
                        style="font-size: 20px;"></i>
                </a>
            @else
                <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $player->id }}').submit();"
                    class="me-2" href="#" data-bs-toggle="tooltip"
                    data-bs-original-title="InActive Player">
                    <i class="fas fa-user-slash text-danger"
                        style="font-size: 20px;"></i>
                </a>
            @endif
            <form class="d-none" id="banUser-{{ $player->id }}"
                action="{{ route('admin.player.ban', $player->id) }}"
                method="post">
                @csrf
                @method('PUT')
            </form>
            <a class="me-1"
                href="{{ route('admin.player.getChangePassword', $player->id) }}"
                data-bs-toggle="tooltip"
                data-bs-original-title="Change Password">
                <i class="fas fa-lock text-info" style="font-size: 20px;"></i>
            </a>
            <a class="me-1" href="{{ route('admin.player.edit', $player->id) }}"
                data-bs-toggle="tooltip" data-bs-original-title="Edit Agent">
                <i class="fas fa-edit text-info" style="font-size: 20px;"></i>
            </a>

        </td>
        <td>
            <a href="{{ route('admin.subacc.player.getCashIn', $player->id) }}"
                data-bs-toggle="tooltip"
                data-bs-original-title="Deposit To Player"
                class="btn btn-info btn-sm">
                <i class="fas fa-plus text-white me-1"></i>
                Deposit
            </a>
            <a href="{{ route('admin.subacc.player.getCashOut', $player->id) }}"
                data-bs-toggle="tooltip"
                data-bs-original-title="WithDraw To Player"
                class="btn btn-info btn-sm">
                <i class="fas fa-minus text-white me-1"></i>
                Withdrawl
            </a>

            <a href="{{ route('admin.logs', $player->id) }}"
                data-bs-toggle="tooltip" data-bs-original-title="Logs"
                class="btn btn-info btn-sm">
                <i class="fas fa-right-left text-white me-1"></i>
                Logs
            </a>
            <a href="{{ route('admin.PlayertransferLogDetail', $player->id) }}"
                data-bs-toggle="tooltip" data-bs-original-title="Transfer Logs"
                class="btn btn-info btn-sm">
                <i class="fas fa-right-left text-white me-1"></i>
                transferLogs
            </a>
            <!-- <a href="{{ route('admin.subacc.player.report_detail', $player->user_name) }}"
                data-bs-toggle="tooltip" data-bs-original-title="Reports"
                class="btn btn-info btn-sm ">
                <i class="fas fa-right-left text-white me-1"></i>
                Reports
            </a> -->
            <a href="{{ route('admin.subacc.player.report_detail', $player->id) }}"
    data-bs-toggle="tooltip" data-bs-original-title="Reports"
    class="btn btn-info btn-sm ">
    <i class="fas fa-right-left text-white me-1"></i>
    Reports
</a>
        </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center">No players found.</td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-center">
    {{ $players->links() }}
</div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <div class="modal fade" id="credentialsModal" tabindex="-1" role="dialog"
                    aria-labelledby="credentialsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="credentialsModalLabel">Your Credentials</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Username:</strong> <span id="modal-username"></span></p>
                                <p><strong>Password:</strong> <span id="modal-password"></span></p>
                                <p><strong>Amount:</strong> <span id="modal-amount"></span></p>
                                <p><strong>URL:</strong> <span id="modal-url"></span></p>
                                <button class="btn btn-success" onclick="copyToClipboard()">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

    @section('script')
    <script>
        var successMessage = @json(session('successMessage'));
        var username = @json(session('username'));
        var password = @json(session('password'));
        var amount = @json(session('amount'));

        @if (session()->has('successMessage'))
            toastr.success(successMessage +
                `
    <div>
        <button class="btn btn-primary btn-sm" data-toggle="modal"
            data-username="${username}"
            data-password="${password}"
            data-amount="${amount}"
            onclick="copyToClipboard(this)">Copy</button>
    </div>`, {
                    allowHtml: true
                });
        @endif

        function copyToClipboard(button) {
            var username = $(button).data('username');
            var password = $(button).data('password');
            var amount = $(button).data('amount');

            var textToCopy = "Username: " + username + "\nPassword: " + password + "\nAmount: " + amount;

            navigator.clipboard.writeText(textToCopy).then(function() {
                toastr.success("Credentials copied to clipboard!");
            }).catch(function(err) {
                toastr.error("Failed to copy text: " + err);
            });
        }
    </script>
@endsection
