@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Create Player</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card col-lg-6 offset-lg-3 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1"
                style="border-radius: 15px;">
                <div class="card-header">
                    <div class="card-title col-12">
                        <h5 class="d-inline fw-bold">
                            Create Player
                        </h5>
                        <a href="{{ route('admin.subacc.agent_players') }}" class="btn btn-primary d-inline float-right">
                            <i class="fas fa-arrow-left mr-2"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.subacc.player.store') }}" method="POST">
                    @csrf
                    <div class="card-body mt-2">
                        <div class="row">
                            <div class="col-lg-12 offset-lg-0 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1 ">
                                <div class="form-group">
                                    <label>PlayerId<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="user_name" value="{{ $player_name }}"
                                        readonly>
                                    @error('user_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Name<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Phone<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12 offset-lg-0 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1 ">
                                <div class="form-group">
                                    <label>Password<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="password"
                                        value="{{ old('password') }}">
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Amount</label>
                                    <span
                                        class="badge badge-sm bg-gradient-success">{{ auth()->user()->balanceFloat }}</span>
                                    <input type="text" class="form-control" name="amount" value="{{ old('amount') }}">
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
@section('script')
    <script>
        var successMessage = @json(session('successMessage'));
        var user_name = @json(session('user_name'));
        var password = @json(session('password'));
        var amount = @json(session('amount'));
        var site_link = @json(session('site_link'));

        @if (session()->has('successMessage'))
            toastr.success(successMessage +
                `
    <div>
        <button class="btn btn-primary btn-sm" data-toggle="modal"
            data-user_name="${user_name}"
            data-password="${password}"
            data-amount="${amount}"
            data-url="${site_link}"
            onclick="copyToClipboard(this)">Copy</button>
    </div>`, {
                    allowHtml: true
                });
        @endif

        function copyToClipboard(button) {
            var user_name = $(button).data('user_name');
            var password = $(button).data('password');
            var amount = $(button).data('amount');
            var url = $(button).data('url');

            // var textToCopy = "Phone: " + phone + "\nPassword: " + password + "\nAmount: " + amount + "\nURL: " + url;
            var textToCopy = "URL: " + url + "\nLogin ID: " + user_name + "\nPassword: " + password + "\nAmount: " + amount;

            navigator.clipboard.writeText(textToCopy).then(function() {
                toastr.success("Credentials copied to clipboard!");
            }).catch(function(err) {
                toastr.error("Failed to copy text: " + err);
            });
        }
    </script>
@endsection
