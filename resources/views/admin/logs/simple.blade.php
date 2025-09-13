@extends('layouts.master')

@section('title', 'Simple Log Viewer')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 fw-bold">Simple Laravel Log Viewer</h4>
                <p class="text-muted mb-0">Direct log file reading example</p>
            </div>
            <div class="card-body">
                @php
                    $logFile = storage_path('logs/laravel.log');
                    $logs = [];
                    
                    if (file_exists($logFile)) {
                        // Read the last 100 lines of the log file
                        $lines = array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -100);
                        
                        foreach ($lines as $line) {
                            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)/', $line, $matches)) {
                                $logs[] = [
                                    'timestamp' => $matches[1],
                                    'environment' => $matches[2],
                                    'level' => strtoupper($matches[3]),
                                    'message' => $matches[4],
                                    'full_line' => $line
                                ];
                            }
                        }
                        
                        // Reverse to show newest first
                        $logs = array_reverse($logs);
                    }
                @endphp

                @if(file_exists($logFile))
                    <div class="alert alert-info mb-3">
                        <strong>Log File Info:</strong><br>
                        <small>
                            File: {{ $logFile }}<br>
                            Size: {{ round(filesize($logFile) / 1024 / 1024, 2) }} MB<br>
                            Last Modified: {{ date('Y-m-d H:i:s', filemtime($logFile)) }}<br>
                            Showing last {{ count($logs) }} log entries
                        </small>
                    </div>

                    @if(count($logs) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="150">Timestamp</th>
                                        <th width="80">Level</th>
                                        <th width="80">Environment</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                    <tr>
                                        <td class="small">{{ $log['timestamp'] }}</td>
                                        <td>
                                            @switch(strtolower($log['level']))
                                                @case('error')
                                                @case('critical')
                                                @case('emergency')
                                                    <span class="badge bg-danger">{{ $log['level'] }}</span>
                                                    @break
                                                @case('warning')
                                                    <span class="badge bg-warning text-dark">{{ $log['level'] }}</span>
                                                    @break
                                                @case('info')
                                                    <span class="badge bg-primary">{{ $log['level'] }}</span>
                                                    @break
                                                @case('debug')
                                                    <span class="badge bg-secondary">{{ $log['level'] }}</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $log['level'] }}</span>
                                            @endswitch
                                        </td>
                                        <td><small class="text-muted">{{ $log['environment'] }}</small></td>
                                        <td>
                                            <div class="log-message">
                                                {{ Str::limit($log['message'], 100) }}
                                                @if(strlen($log['message']) > 100)
                                                    <button class="btn btn-sm btn-outline-secondary ms-2" type="button" 
                                                            data-bs-toggle="collapse" data-bs-target="#full-log-{{ $loop->index }}" 
                                                            aria-expanded="false">
                                                        Show Full
                                                    </button>
                                                    <div class="collapse mt-2" id="full-log-{{ $loop->index }}">
                                                        <div class="p-2 bg-light rounded">
                                                            <pre class="mb-0 small">{{ $log['full_line'] }}</pre>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            No log entries found in the last 100 lines.
                        </div>
                    @endif
                @else
                    <div class="alert alert-danger">
                        <strong>Log file not found!</strong><br>
                        Expected location: <code>{{ $logFile }}</code>
                    </div>
                @endif

                <div class="mt-3">
                    <button onclick="location.reload()" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Refresh Logs
                    </button>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-eye"></i> Advanced Log Viewer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.log-message {
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 0.9rem;
}

pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    font-size: 0.8rem;
}
</style>
@endsection
