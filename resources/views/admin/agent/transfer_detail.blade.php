@extends('layouts.master')
@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <!-- Card header -->
                <div class="card-header pb-0">
                    <div class="d-lg-flex">
                        <div>
                            <h5 class="mb-0">Transfer Detail </h5>
                        </div>

                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-flush" id="users-search">
                        <thead class="thead-light">
                            <th>#</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Cash In</th>
                            <th>Cash Out</th>
                            <th>Profit</th>
                            <th>CurrentCashBalance</th>
                            <th>Date</th>

                        </thead>

                        <tbody>
                            @foreach ($transfer_detail as $index => $log)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $log->fromUser->name }} </td>
                                    <td>{{ $log->toUser->name }}</td>
                                    <td>
                                        @if ($log->cash_in == null)
                                            ----
                                        @else
                                            {{ $log->cash_in }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($log->cash_out == null)
                                            ----
                                        @else
                                            {{ $log->cash_out }}
                                        @endif
                                    </td>
                                    <td>
                                        @php

                                            $profit = $log->cash_in - $log->cash_out;
                                        @endphp
                                        {{-- if profit value is -, show span red color. else profit value is +, show profit value with green color --}}
                                        @if ($profit < 0)
                                            <span class="text-danger">{{ $profit }}</span>
                                        @else
                                            <span class="text-success">{{ $profit }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->cash_balance }}</td>
                                    <td>{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

