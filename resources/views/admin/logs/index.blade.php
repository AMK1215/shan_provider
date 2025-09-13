@extends('layouts.master')

@section('title', 'Laravel Logs')

@php
function getLevelColor($level) {
    return match(strtolower($level)) {
        'emergency', 'alert', 'critical' => 'danger',
        'error' => 'danger',
        'warning' => 'warning',
        'notice' => 'info',
        'info' => 'primary',
        'debug' => 'secondary',
        default => 'secondary'
    };
}
@endphp

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <!-- Card header with filters -->
            <div class="card-header pb-0">
                <div class="d-lg-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0 fw-bold">Laravel Application Logs</h4>
                        @if(isset($fileSize) && isset($lastModified))
                        <p class="text-sm text-muted mb-0">
                            File Size: {{ $fileSize }} | Last Modified: {{ $lastModified }}
                        </p>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.logs.download') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearLogs()">
                            <i class="fas fa-trash"></i> Clear Logs
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="refreshLogs()">
                            <i class="fas fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-body pt-2">
                <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="level" class="form-label">Log Level</label>
                        <select name="level" id="level" class="form-select">
                            <option value="">All Levels</option>
                            <option value="emergency" {{ request('level') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                            <option value="alert" {{ request('level') == 'alert' ? 'selected' : '' }}>Alert</option>
                            <option value="critical" {{ request('level') == 'critical' ? 'selected' : '' }}>Critical</option>
                            <option value="error" {{ request('level') == 'error' ? 'selected' : '' }}>Error</option>
                            <option value="warning" {{ request('level') == 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="notice" {{ request('level') == 'notice' ? 'selected' : '' }}>Notice</option>
                            <option value="info" {{ request('level') == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="debug" {{ request('level') == 'debug' ? 'selected' : '' }}>Debug</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search in logs..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="per_page" class="form-label">Per Page</label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                @if(isset($error))
                    <div class="alert alert-danger">
                        <strong>Error:</strong> {{ $error }}
                        @if(isset($logPath))
                            <br><small>Log path: {{ $logPath }}</small>
                        @endif
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Pagination info -->
                @if(isset($pagination))
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <small class="text-muted">
                            Showing {{ $pagination['from'] }} to {{ $pagination['to'] }} of {{ $pagination['total'] }} entries
                        </small>
                    </div>
                    @if($pagination['last_page'] > 1)
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            @if($pagination['current_page'] > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->all(), ['page' => $pagination['current_page'] - 1])) }}">Previous</a>
                                </li>
                            @endif
                            
                            @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++)
                                <li class="page-item {{ $i == $pagination['current_page'] ? 'active' : '' }}">
                                    <a class="page-link" href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->all(), ['page' => $i])) }}">{{ $i }}</a>
                                </li>
                            @endfor
                            
                            @if($pagination['current_page'] < $pagination['last_page'])
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->url() }}?{{ http_build_query(array_merge(request()->all(), ['page' => $pagination['current_page'] + 1])) }}">Next</a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                    @endif
                </div>
                @endif

                <!-- Logs display -->
                @if(!empty($logs))
                <div class="logs-container">
                    @foreach($logs as $index => $log)
                    <div class="log-entry mb-3 border-start border-3 border-{{ getLevelColor($log['level']) }} ps-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-{{ getLevelColor($log['level']) }}">{{ $log['level'] }}</span>
                                    <small class="text-muted">{{ Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}</small>
                                    <small class="text-muted">{{ $log['timestamp'] }}</small>
                                    <small class="text-muted">{{ $log['environment'] }}</small>
                                </div>
                                <div class="log-message">
                                    <p class="mb-1 fw-medium">{{ Str::limit($log['message'], 150) }}</p>
                                </div>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#logContent{{ $index }}" aria-expanded="false">
                                View Full
                            </button>
                        </div>
                        <div class="collapse" id="logContent{{ $index }}">
                            <div class="mt-2 p-3 bg-light rounded">
                                <pre class="mb-0 small" style="white-space: pre-wrap; word-wrap: break-word;">{{ $log['content'] }}</pre>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No log entries found</h5>
                        <p class="text-muted">Try adjusting your filters or check if the log file exists.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Clear logs confirmation modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to clear all log entries? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.logs.clear') }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Clear Logs</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function clearLogs() {
    var modal = new bootstrap.Modal(document.getElementById('clearLogsModal'));
    modal.show();
}

function refreshLogs() {
    window.location.reload();
}

// Auto-refresh every 30 seconds if on first page
@if(request('page', 1) == 1 && !request('search') && !request('level') && !request('date'))
setInterval(function() {
    if (document.visibilityState === 'visible') {
        window.location.reload();
    }
}, 30000);
@endif
</script>

<style>
.log-entry {
    background: #fff;
    border-radius: 0.375rem;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.log-entry:hover {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s ease;
}

.logs-container {
    max-height: 70vh;
    overflow-y: auto;
}

pre {
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    line-height: 1.4;
}

.badge {
    font-size: 0.7rem;
}
</style>
@endsection
