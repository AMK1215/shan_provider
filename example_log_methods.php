<?php

// Example methods you can add to any existing controller

class ExampleController extends Controller
{
    /**
     * Simple method to display logs in any existing view
     * Add this to any controller and call from a route
     */
    public function showRecentLogs()
    {
        $logs = $this->getRecentLogs(50); // Get last 50 logs
        
        // You can pass logs to any existing view
        return view('admin.dashboard', compact('logs'));
        // Or return as JSON for AJAX
        // return response()->json($logs);
    }

    /**
     * Get recent logs - reusable method
     */
    private function getRecentLogs($limit = 50)
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];
        
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $recentLines = array_slice($lines, -$limit);
            
            foreach ($recentLines as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'environment' => $matches[2],
                        'level' => strtoupper($matches[3]),
                        'message' => $matches[4],
                        'time_ago' => \Carbon\Carbon::parse($matches[1])->diffForHumans(),
                        'is_error' => in_array(strtolower($matches[3]), ['error', 'critical', 'emergency', 'alert'])
                    ];
                }
            }
            
            // Reverse to show newest first
            $logs = array_reverse($logs);
        }
        
        return $logs;
    }

    /**
     * Get error logs only
     */
    public function getErrorLogs($limit = 20)
    {
        $allLogs = $this->getRecentLogs(200); // Get more to filter
        
        $errorLogs = array_filter($allLogs, function($log) {
            return $log['is_error'];
        });
        
        return array_slice($errorLogs, 0, $limit);
    }

    /**
     * Check if there are recent errors (useful for dashboard widgets)
     */
    public function hasRecentErrors($hours = 24)
    {
        $cutoff = now()->subHours($hours);
        $recentLogs = $this->getRecentLogs(100);
        
        foreach ($recentLogs as $log) {
            $logTime = \Carbon\Carbon::parse($log['timestamp']);
            if ($logTime >= $cutoff && $log['is_error']) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get log statistics for dashboard
     */
    public function getLogStats()
    {
        $logs = $this->getRecentLogs(500);
        
        $stats = [
            'total' => count($logs),
            'errors' => 0,
            'warnings' => 0,
            'info' => 0,
            'recent_errors' => 0 // Last 1 hour
        ];
        
        $oneHourAgo = now()->subHour();
        
        foreach ($logs as $log) {
            $level = strtolower($log['level']);
            $logTime = \Carbon\Carbon::parse($log['timestamp']);
            
            switch ($level) {
                case 'error':
                case 'critical':
                case 'emergency':
                case 'alert':
                    $stats['errors']++;
                    if ($logTime >= $oneHourAgo) {
                        $stats['recent_errors']++;
                    }
                    break;
                case 'warning':
                    $stats['warnings']++;
                    break;
                case 'info':
                    $stats['info']++;
                    break;
            }
        }
        
        return $stats;
    }
}

/*
USAGE EXAMPLES:

1. In any route:
Route::get('/admin/recent-logs', [YourController::class, 'showRecentLogs']);

2. In any Blade view:
@if(isset($logs))
    <div class="recent-logs">
        <h5>Recent Logs</h5>
        @foreach($logs as $log)
            <div class="alert alert-{{ $log['is_error'] ? 'danger' : 'info' }} alert-sm">
                <small>{{ $log['time_ago'] }}</small> - 
                <strong>[{{ $log['level'] }}]</strong> 
                {{ Str::limit($log['message'], 100) }}
            </div>
        @endforeach
    </div>
@endif

3. For dashboard widget:
$hasErrors = $this->hasRecentErrors(24); // Check last 24 hours
$logStats = $this->getLogStats();

return view('admin.dashboard', compact('hasErrors', 'logStats'));

4. AJAX endpoint for real-time updates:
Route::get('/api/recent-logs', function() {
    $controller = new YourController();
    return response()->json($controller->getRecentLogs(10));
});

5. Dashboard widget in Blade:
<div class="card">
    <div class="card-header">
        <h6>System Status</h6>
    </div>
    <div class="card-body">
        @if(isset($logStats))
            <div class="row text-center">
                <div class="col">
                    <h4 class="text-danger">{{ $logStats['errors'] }}</h4>
                    <small>Errors</small>
                </div>
                <div class="col">
                    <h4 class="text-warning">{{ $logStats['warnings'] }}</h4>
                    <small>Warnings</small>
                </div>
                <div class="col">
                    <h4 class="text-info">{{ $logStats['info'] }}</h4>
                    <small>Info</small>
                </div>
            </div>
            @if($logStats['recent_errors'] > 0)
                <div class="alert alert-warning mt-2">
                    <small>{{ $logStats['recent_errors'] }} error(s) in the last hour</small>
                </div>
            @endif
        @endif
    </div>
</div>
*/
