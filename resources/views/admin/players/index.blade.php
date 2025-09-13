@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Players List</h3>
                    @if(Auth::user()->hasPermission('player_create'))
                    <div class="card-tools">
                        <a href="{{ route('admin.player.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create New Player
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Balance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($players as $player)
                            <tr>
                                <td>{{ $player->name }}</td>
                                <td>{{ $player->user_name }}</td>
                                <td>{{ $player->balance }}</td>
                                <td>
                                    @if(Auth::user()->hasPermission('player_view'))
                                    <a href="{{ route('admin.player.show', $player) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @endif

                                    @if(Auth::user()->hasPermission('player_edit'))
                                    <a href="{{ route('admin.player.edit', $player) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @endif

                                    @if(Auth::user()->hasPermission('deposit_withdraw'))
                                    <a href="{{ route('admin.player.deposit', $player) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-money-bill-wave"></i> Deposit
                                    </a>
                                    <a href="{{ route('admin.player.withdraw', $player) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-money-bill-wave"></i> Withdraw
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 