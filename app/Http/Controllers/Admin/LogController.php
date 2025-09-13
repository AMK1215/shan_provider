<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class LogController extends Controller
{
    /**
     * Display Laravel logs
     */
    public function index(Request $request)
    {
        // Get log file path
        $logPath = storage_path('logs/laravel.log');
        
        // Check if log file exists
        if (!File::exists($logPath)) {
            return view('admin.logs.index', [
                'logs' => [],
                'error' => 'Log file not found',
                'logPath' => $logPath
            ]);
        }

        try {
            // Read log file content
            $logContent = File::get($logPath);
            
            // Parse logs
            $logs = $this->parseLogs($logContent);
            
            // Apply filters
            $logs = $this->applyFilters($logs, $request);
            
            // Pagination
            $perPage = $request->get('per_page', 50);
            $page = $request->get('page', 1);
            $total = count($logs);
            $offset = ($page - 1) * $perPage;
            
            $paginatedLogs = array_slice($logs, $offset, $perPage);
            
            // Pagination data
            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ];
            
            return view('admin.logs.index', [
                'logs' => $paginatedLogs,
                'pagination' => $pagination,
                'filters' => $request->all(),
                'logPath' => $logPath,
                'fileSize' => $this->formatBytes(File::size($logPath)),
                'lastModified' => Carbon::createFromTimestamp(File::lastModified($logPath))->format('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return view('admin.logs.index', [
                'logs' => [],
                'error' => 'Error reading log file: ' . $e->getMessage(),
                'logPath' => $logPath
            ]);
        }
    }

    /**
     * Parse log content into structured array
     */
    private function parseLogs($content)
    {
        $logs = [];
        $lines = explode("\n", $content);
        $currentLog = null;
        
        foreach ($lines as $line) {
            // Check if line starts with date pattern [YYYY-MM-DD HH:MM:SS]
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)/', $line, $matches)) {
                // Save previous log if exists
                if ($currentLog) {
                    $logs[] = $currentLog;
                }
                
                // Start new log entry
                $currentLog = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => strtoupper($matches[3]),
                    'message' => $matches[4],
                    'content' => $line,
                    'carbon' => Carbon::parse($matches[1])
                ];
            } elseif ($currentLog && !empty(trim($line))) {
                // Append to current log (for multi-line logs like stack traces)
                $currentLog['content'] .= "\n" . $line;
                if (strlen($currentLog['message']) < 200) {
                    $currentLog['message'] .= ' ' . trim($line);
                }
            }
        }
        
        // Add last log
        if ($currentLog) {
            $logs[] = $currentLog;
        }
        
        // Sort by timestamp (newest first)
        usort($logs, function($a, $b) {
            return $b['carbon']->timestamp - $a['carbon']->timestamp;
        });
        
        return $logs;
    }

    /**
     * Apply filters to logs
     */
    private function applyFilters($logs, Request $request)
    {
        // Filter by level
        if ($request->has('level') && $request->level !== '') {
            $logs = array_filter($logs, function($log) use ($request) {
                return strtolower($log['level']) === strtolower($request->level);
            });
        }

        // Filter by date
        if ($request->has('date') && $request->date !== '') {
            $filterDate = Carbon::parse($request->date);
            $logs = array_filter($logs, function($log) use ($filterDate) {
                return $log['carbon']->format('Y-m-d') === $filterDate->format('Y-m-d');
            });
        }

        // Filter by search term
        if ($request->has('search') && $request->search !== '') {
            $search = strtolower($request->search);
            $logs = array_filter($logs, function($log) use ($search) {
                return strpos(strtolower($log['content']), $search) !== false;
            });
        }

        return array_values($logs); // Re-index array
    }

    /**
     * Download log file
     */
    public function download()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logPath)) {
            abort(404, 'Log file not found');
        }
        
        return response()->download($logPath);
    }

    /**
     * Clear log file
     */
    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (File::exists($logPath)) {
            File::put($logPath, '');
            return redirect()->back()->with('success', 'Log file cleared successfully');
        }
        
        return redirect()->back()->with('error', 'Log file not found');
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Get log statistics
     */
    public function stats()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logPath)) {
            return response()->json(['error' => 'Log file not found'], 404);
        }

        $logContent = File::get($logPath);
        $logs = $this->parseLogs($logContent);
        
        $stats = [
            'total_logs' => count($logs),
            'file_size' => $this->formatBytes(File::size($logPath)),
            'last_modified' => Carbon::createFromTimestamp(File::lastModified($logPath))->format('Y-m-d H:i:s'),
            'levels' => []
        ];
        
        // Count by level
        foreach ($logs as $log) {
            $level = strtolower($log['level']);
            $stats['levels'][$level] = ($stats['levels'][$level] ?? 0) + 1;
        }
        
        return response()->json($stats);
    }
}
