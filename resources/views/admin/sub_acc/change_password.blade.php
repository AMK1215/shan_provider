@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">

                <div class="col-12">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Change Password</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card col-lg-6 offset-lg-3 col-md-6 offset-md-3 col-sm-8 offset-sm-2 col-10 offset-1"
                style="border-radius: 20px;">
                <div class="card-header ">
                    <div class="card-title  col-12 my-3">
                        <h5 class="d-inline">Change Password</h5>
                        <a href="{{ route('admin.subacc.index') }}" class="btn btn-primary float-right">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>

                    </div>
                </div>

                <div class="card-body ">
                    <form role="form" method="POST" class="text-start"
                        action="{{ route('admin.subacc.makeChangePassword', $agent->id) }}">
                        @csrf
                        <div class="col-lg-8 offset-lg-2 col-md-8 offset-md-2 col-sm-8 offset-sm-2 col-10 offset-1">
                            <div class="custom-form-group mb-3">
                                <label for="title">New Password <span class="text-danger">*</span></label>
                                <input type="text" name="password" class="form-control">
                                @error('password')
                                    <span class="text-danger d-block">*{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="custom-form-group mb-3">
                                <label for="title">Confirm Password <span class="text-danger">*</span></label>
                                <input type="text" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                </div>
                <div class="card-footer bg-white col-12">
                    <button type="submit" class="btn btn-success float-right" type="button">Confirm</button>
                </div>
                </form>

            </div>
        </div>
    </section>
@endsection


@section('scripts')
    <script>
        var errorMessage = @json(session('error'));
        var successMessage = @json(session('success'));
        var url = 'https://goldenjacks.pro/login';
        var name = @json(session('username'));
        var pw = @json(session('password'));

        @if (session()->has('success'))
            Swal.fire({
                title: successMessage,
                icon: "success",
                background: 'hsl(230, 40%, 10%)',
                showConfirmButton: false,
                showCloseButton: true,
                html: `
  <table class="table table-bordered" style="background:#eee;">
  <tbody>
    <tr>
    <td>Url</td>
    <td id=""> ${url}</td>
  </tr>
  <tr>
    <td>Username</td>
    <td id="tusername"> ${name}</td>
  </tr>
  <tr>
    <td>Password</td>
    <td id="tpassword"> ${pw}</td>
  </tr>

  <tr>
    <td></td>
    <td><a href="#" onclick="copy()" class="btn btn-sm btn-primary">copy</a></td>
  </tr>
 </tbody>
  </table>
  `
            });
        @elseif (session()->has('error'))
            Swal.fire({
                icon: 'error',
                title: errorMessage,
                background: 'hsl(230, 40%, 10%)',
                showConfirmButton: false,
                timer: 1500
            })
        @endif
        function copy() {
            var username = $('#tusername').text();
            var password = $('#tpassword').text();
            var copy = "url : " + url + "\nusername : " + username + "\npw : " + password;
            copyToClipboard(copy)
        }

        function copyToClipboard(v) {
            var $temp = $("<textarea>");
            $("body").append($temp);
            var html = v;
            $temp.val(html).select();
            document.execCommand("copy");
            $temp.remove();
        }
    </script>
@endsection
