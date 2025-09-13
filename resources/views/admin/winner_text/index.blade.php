@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Winner Text</li>
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
                        <a href="{{ route('admin.winner_text.create') }}" class="btn bg-gradient-success btn-sm mb-0">+&nbsp; New</a>
                    </div>
                    <div class="card " style="border-radius: 20px;">
                        <div class="card-header">
                            <h3>Winner Text Lists </h3>
                        </div>
                        <div class="card-body">
                            <table id="mytable" class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Text</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($texts as $key => $text)
                                        <tr>
                                            <td class="text-sm font-weight-normal">{{ ++$key }}</td>
                                            <td>{{ $text->text }}</td>
                                            <td class="text-sm font-weight-normal">{{ $text->created_at->format('M j, Y') }}
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.winner_text.edit', $text->id) }}" data-bs-toggle="tooltip"
                                                    data-bs-original-title="Edit Banner">  <i class="fas fa-edit text-info mr-2"
                                                    style="font-size: 20px;"></i></a>
                                                <a href="{{ route('admin.winner_text.show', $text->id) }}" data-bs-toggle="tooltip"
                                                    data-bs-original-title="Preview Banner Detail">
                                                    <i class="fa-solid fa-eye  text-success " style="font-size: 20px;"></i>
                                                </a>
                                                <form class="d-inline" action="{{ route('admin.winner_text.destroy', $text->id) }}"
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
                    background: 'hsl(230, 40%, 10%)',
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
    @if (session()->has('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '{{ session('success ') }}',
                showConfirmButton: false,
                background: 'hsl(230, 40%, 10%)',
                timer: 1500
            })
        </script>
    @endif
@endsection
