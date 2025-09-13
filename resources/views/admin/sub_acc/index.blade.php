@extends('layouts.master')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Sub Account List</li>
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
                        <a href="{{ route('admin.subacc.create') }}" class="btn btn-success " style="width: 100px;"><i
                                class="fas fa-plus text-white  mr-2"></i>Create</a>
                    </div>
                    <div class="card " style="border-radius: 20px;">
                        <div class="card-header">
                            <h3>Sub Account Lists</h3>
                        </div>
                        <div class="card-body">
                            <table id="mytable" class="table table-bordered table-hover">
                                <thead>
                                    <th>#</th>
                                    <th>SubAccId</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>CreatedAt</th>
                                    <th>Action</th>
                                </thead>
                                <tbody>
                                    @if (isset($users))
                                        @if (count($users) > 0)
                                            @foreach ($users as $user)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <span class="d-block">{{ $user->user_name }}</span>

                                                    </td>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->phone }}</td>
                                                    <td>
                                                        <small
                                                            class="badge bg-gradient-{{ $user->status == 1 ? 'success' : 'danger' }}">{{ $user->status == 1 ? 'active' : 'inactive' }}</small>
                                                    </td>
                                                    <td>{{ $user->created_at->setTimezone('Asia/Yangon')->format('d-m-Y H:i:s') }}
                                                    </td>
                                                    <td>
                                                        @if ($user->status == 1)
                                                            <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $user->id }}').submit();"
                                                                class="me-2" href="#" data-bs-toggle="tooltip"
                                                                data-bs-original-title="Active Player">
                                                                <i class="fas fa-user-check text-success"
                                                                    style="font-size: 20px;"></i>
                                                            </a>
                                                        @else
                                                            <a onclick="event.preventDefault(); document.getElementById('banUser-{{ $user->id }}').submit();"
                                                                class="me-2" href="#" data-bs-toggle="tooltip"
                                                                data-bs-original-title="InActive Player">
                                                                <i class="fas fa-user-slash text-danger"
                                                                    style="font-size: 20px;"></i>
                                                            </a>
                                                        @endif
                                                        <form class="d-none" id="banUser-{{ $user->id }}"
                                                            action="{{ route('admin.subacc.ban', $user->id) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('PUT')
                                                        </form>
                                                        <a class="me-1"
                                                            href="{{ route('admin.subacc.getChangePassword', $user->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="Change Password">
                                                            <i class="fas fa-lock text-info" style="font-size: 20px;"></i>
                                                        </a>
                                                        <a class="me-1" href="{{ route('admin.subacc.edit', $user->id) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="Edit Agent">
                                                            <i class="fas fa-edit text-info" style="font-size: 20px;"></i>
                                                        </a>
                                                        <!-- allow permission -->
                                                         <a class="me-1" href="{{ route('admin.subacc.permissions.view', $user->id) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="AllowPermission">
                                                            <i class="fas fa-user-shield text-info" style="font-size: 20px;"></i>
                                                        </a>

                                                    </td>
                                                    
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td col-span=8>
                                                    There was no Players.
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
