@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Player Reports</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Agent List</li>
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
                                <th>#</th>
                                <th>PlayerID</th>
                                <th>Name</th>
                                <th>Balance</th>
                                <th>Total Win/Lose</th>
                            </thead>
                            <tbody>
                                @if (isset($users))
                                @if (count($users) > 0)
                                @foreach ($users as $user)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$user->user_name}}</td>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->balanceFloat}}</td>
                                    <td>{{ number_format($user->poneWinePlayer->sum('win_lose_amt') + $user->results->sum('net_win') + $user->betNResults->sum('net_win'), 2)}}</td>
                                    </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td col-span=8>
                                        There was no Player report.
                                    </td>
                                </tr>
                                @endif
                                @endif
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