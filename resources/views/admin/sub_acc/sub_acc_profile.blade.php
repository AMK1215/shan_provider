@extends('layouts.master')

@section('content')
<div class="container">
    <h2 class="mb-4">SubAgent Profile: <span class="text-primary">{{ $subAgent->name }}</span></h2>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong>Profile Details</strong>
                </div>
                <div class="card-body">
                    @if($subAgent->profile)
                        <div class="text-center mb-3">
                            <img src="{{ asset('storage/' . $subAgent->profile) }}" alt="Profile Picture" class="rounded-circle" width="100" height="100">
                        </div>
                    @endif
                    <div class="row mb-2">
                        <div class="col-6"><strong>ID:</strong></div>
                        <div class="col-6">{{ $subAgent->id }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Username:</strong></div>
                        <div class="col-6">{{ $subAgent->user_name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Email:</strong></div>
                        <div class="col-6">{{ $subAgent->email ?: '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Phone:</strong></div>
                        <div class="col-6">{{ $subAgent->phone ?: '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Status:</strong></div>
                        <div class="col-6">
                            @if($subAgent->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Created At:</strong></div>
                        <div class="col-6">{{ $subAgent->created_at }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Updated At:</strong></div>
                        <div class="col-6">{{ $subAgent->updated_at }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Last Login:</strong></div>
                        <div class="col-6">{{ $subAgent->last_login_at ?? '-' }}</div>
                    </div>
                    <hr>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Agent:</strong></div>
                        <div class="col-6">{{ $subAgent->agent->name ?? '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Agent ID:</strong></div>
                        <div class="col-6">{{ $subAgent->agent_id }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Agent Phone:</strong></div>
                        <div class="col-6">{{ $subAgent->agent->phone ?? '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Agent Balance:</strong></div>
                        <div class="col-6">
                            <span class="badge bg-info text-dark">
                                {{ $subAgent->agent ? number_format($subAgent->agent->balanceFloat, 2) : 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Role:</strong></div>
                        <div class="col-6">
                            @foreach($subAgent->roles as $role)
                                <span class="badge bg-secondary">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Permissions:</strong></div>
                        <div class="col-6">
                            @foreach ($subAgent->permissions as $permission)
                                <span class="badge bg-light text-dark border mb-1">{{ $permission->title }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4">
                        @can('subagent_edit')
                            <a href="{{ route('admin.subacc.edit', $subAgent->id) }}" class="btn btn-warning me-2">Edit Profile</a>
                        @endcan
                        @can('subagent_edit')
                            <a href="{{ route('admin.subacc.resetPassword', $subAgent->id) }}" class="btn btn-danger me-2">Reset Password</a>
                        @endcan
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection