@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sub-Agent Permissions: {{ $subAgent->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.subacc.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.subacc.permissions.update', $subAgent->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            @foreach($permission_groups as $group => $groupName)
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title mb-0">{{ $groupName }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                @foreach($permissions->where('group', $group) as $permission)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div class="form-check">
                                                            <input type="checkbox" 
                                                                class="form-check-input" 
                                                                name="permissions[]" 
                                                                value="{{ $permission->id }}" 
                                                                id="perm_{{ $permission->id }}"
                                                                {{ in_array($permission->id, $subAgentPermissions) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                                {{ ucwords(str_replace('_', ' ', $permission->title)) }}
                                                            </label>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success float-right">
                                    <i class="fas fa-save"></i> Update Permissions
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Add any JavaScript for handling permission updates if needed
    $(document).ready(function() {
        // Example: Add confirmation before submitting
        $('form').on('submit', function(e) {
            if (!confirm('Are you sure you want to update the permissions?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
@endsection 