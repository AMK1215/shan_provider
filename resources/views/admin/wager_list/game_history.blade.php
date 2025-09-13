@extends('layouts.master')
@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Game History</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.wager-list') }}">Wager List</a></li>
                    <li class="breadcrumb-item active">Game History</li>
                </ol>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if($content)
                            @if(Str::startsWith($content, 'http'))
                                <iframe src="{{ $content }}" width="100%" height="600" frameborder="0"></iframe>
                            @else
                                {!! $content !!}
                            @endif
                        @else
                            <div class="alert alert-warning">No game history available.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 