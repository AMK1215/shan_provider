@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Player List</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Player</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="mytable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Player Name</th>
                                    <th>PlayerId</th>
                                    <th>AgentName</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Balance</th>
                                    <th>CreatedAt</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($players as $player)
                            <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $player->name }}</td>
                                    <td>{{ $player->user_name }}</td>
                                    <td>{{ $player->parent->name }}</td>
                                    <td>{{ $player->phone }}</td>
                                    <td>
                                        <p>{{ $player->status == 1 ? 'Active' : 'Inactive' }}</p>
                                    </td>
                                    <td>{{ number_format($player->balanceFloat) }}</td>
                                    <td>{{ $player->created_at->timezone('Asia/Yangon')->format('d-m-Y H:i:s') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>
@endsection