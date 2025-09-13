@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Banner Text</li>
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
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('admin.adsbanners.create') }}" class="btn bg-gradient-success btn-sm mb-0">+&nbsp;
                            New
                            Banner Ads</a>
                    </div>
                    <div class="card " style="border-radius: 20px;">
                        <div class="card-header">
                            <h3>Banner Ads Lists </h3>
                        </div>
                        <div class="card-body">
                            <table id="mytable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ads Image</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($banners as $key => $banner)
                                        <tr>
                                            <td class="text-sm font-weight-normal">{{ ++$key }}</td>
                                            <td>
                                                <img width="100px" class="img-thumbnail" src="{{ $banner->img_url }}"
                                                    alt="">
                                            </td>
                                            <td class="text-sm font-weight-normal">
                                                {{ $banner->created_at->format('F j, Y') }}</td>
                                            <td class="text-sm font-weight-normal">
                                                {{ $banner->updated_at->format('F j, Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.adsbanners.edit', $banner->id) }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="Edit Banner"><i class="fas fa-edit text-info mr-2"
                                                    style="font-size: 20px;"></i></a>
                                                <a href="{{ route('admin.adsbanners.show', $banner->id) }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="Preview Banner Detail">
                                                    <i class="fa-solid fa-eye  text-success " style="font-size: 20px;"></i>
                                                </a>
                                                <form class="d-inline"
                                                    action="{{ route('admin.adsbanners.destroy', $banner->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn transparent-btn" data-bs-toggle="tooltip"
                                                        data-bs-original-title="Delete Banner">
                                                        <i class="fa fa-trash  text-danger"  style="font-size: 20px;"></i>
                                                    </button>
                                                </form>
                                            </td>
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
@section('script')
    <script>
        $(document).ready(function() {
            $('.transparent-btn').on('click', function(e) {
                e.preventDefault();
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
