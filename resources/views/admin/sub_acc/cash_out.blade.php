@extends('layouts.master')
@section('content')
<section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                        <li class="breadcrumb-item active">Withdrawl</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

        <!-- Main content -->
        <section class="content">
        <div class="container-fluid">
            <div class="card col-lg-6 offset-lg-3 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1" style="border-radius: 15px;">
                <div class="card-header">
                    <div class="card-title col-12">
                        <h5 class="d-inline fw-bold">Withdrawl</h5>
                        <a href="{{ route('admin.subacc.agent_players') }}" class="btn btn-primary float-right">
                            <i class="fas fa-arrow-left" style="font-size: 20px;"></i> Back
                        </a>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="badge badge-success">{{ $agent->user_name }} : Agent Balance : {{ number_format(optional($agent)->balanceFloat, 2) }}</span>
                </div>
                <form action="{{route('admin.subacc.player.makeCashOut', $player->id)}}" method="POST">
                    @csrf
                    <div class="card-body mt-2">
                        <div class="row">
                            <div class="col-lg-12 offset-lg-0 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1">
                                <div class="form-group">
                                    <label>AgentId<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="" value="{{$player->user_name}}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Name<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="{{$player->name}}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Current Balance<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="" value="{{number_format($player->wallet->balanceFloat, 2)}}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Amount<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="amount">
                                    @error('amount')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Note</label>
                                    <textarea name="note" id="" cols="30" rows="5" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer col-12 bg-white">
                        <button type="submit" class="btn btn-success float-right">Submit</button>
                    </div>
                </form>
            </div>

        </div>
        </div>
    </section>
@endsection
