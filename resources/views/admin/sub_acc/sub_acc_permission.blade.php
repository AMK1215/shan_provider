@extends('layouts.master')

@section('content')
<div class="container">
    <h3>Manage Permissions for SubAgent: {{ $subAgent->name }}</h3>
    <input type="text" id="perm-search" class="form-control mb-3" placeholder="Search permissions...">

    <form action="{{ route('admin.subacc.permission.update', $subAgent->id) }}" method="POST">
        @csrf
        <div class="accordion" id="permAccordion">
            @foreach($permissions as $group => $groupPermissions)
                <div class="card mb-2">
                    <div class="card-header" id="heading-{{ $group }}">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse-{{ $group }}">
                                {{ ucfirst($group) }}
                            </button>
                        </h5>
                    </div>
                    <div id="collapse-{{ $group }}" class="collapse show" data-parent="#permAccordion">
                        <div class="card-body">
                            <div class="row">
                                @foreach($groupPermissions as $permission)
                                    <div class="col-md-4 perm-item">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                readonly="readonly"
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->id }}"
                                                id="perm_{{ $permission->id }}"
                                                {{ in_array($permission->id, $subAgentPermissions) ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $permission->title }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary mt-3" disabled>Update Permissions</button>
    </form>
</div>

<script>
document.getElementById('perm-search').addEventListener('input', function() {
    let search = this.value.toLowerCase();
    document.querySelectorAll('.perm-item').forEach(function(item) {
        let label = item.querySelector('label').innerText.toLowerCase();
        item.style.display = label.includes(search) ? '' : 'none';
    });
});
</script>
@endsection