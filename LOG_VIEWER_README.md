# Laravel Log Viewer

This implementation provides a comprehensive log viewing system for your Laravel application.

## Features

✅ **Advanced Log Viewer** (`/admin/logs`)
- Full log parsing with structured display
- Filtering by log level (Error, Warning, Info, Debug, etc.)
- Date-based filtering
- Search functionality across all log content
- Pagination for better performance
- Download log files
- Clear log files with confirmation
- Real-time auto-refresh on first page
- Responsive design with collapsible detailed view

✅ **Simple Log Viewer** (`/admin/logs/simple`)
- Direct file reading without complex parsing
- Shows last 100 log entries
- Quick refresh functionality
- Lightweight and fast loading
- Color-coded log levels

## Routes

All routes are protected by auth and checkBanned middleware under `/admin/logs`:

- `GET /admin/logs` - Advanced log viewer (main interface)
- `GET /admin/logs/simple` - Simple log viewer
- `GET /admin/logs/download` - Download log file
- `DELETE /admin/logs/clear` - Clear log file
- `GET /admin/logs/stats` - Get log statistics (JSON API)

## Usage Examples

### 1. Advanced Log Viewer
```php
// Access via: /admin/logs
// Features:
// - Filter by level: ?level=error
// - Filter by date: ?date=2025-07-30
// - Search: ?search=exception
// - Pagination: ?page=2&per_page=50
```

### 2. Simple Blade Implementation
```blade
@php
    $logFile = storage_path('logs/laravel.log');
    $logs = [];
    
    if (file_exists($logFile)) {
        $lines = array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -50);
        
        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'level' => strtoupper($matches[3]),
                    'message' => $matches[4]
                ];
            }
        }
    }
@endphp

@foreach($logs as $log)
    <div class="alert alert-{{ $log['level'] == 'ERROR' ? 'danger' : 'info' }}">
        <strong>{{ $log['timestamp'] }}</strong> [{{ $log['level'] }}]: {{ $log['message'] }}
    </div>
@endforeach
```

### 3. Custom Controller Method
```php
public function showLogs()
{
    $logPath = storage_path('logs/laravel.log');
    $logs = [];
    
    if (File::exists($logPath)) {
        $content = File::get($logPath);
        $lines = explode("\n", $content);
        
        foreach (array_slice($lines, -100) as $line) {
            if (preg_match('/^\[(.+?)\] (\w+)\.(\w+): (.*)/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => $matches[3],
                    'message' => $matches[4]
                ];
            }
        }
    }
    
    return view('logs', compact('logs'));
}
```

## Log Level Colors

The system uses Bootstrap color classes for different log levels:

- **Emergency/Alert/Critical** → `bg-danger` (Red)
- **Error** → `bg-danger` (Red)  
- **Warning** → `bg-warning` (Yellow)
- **Notice** → `bg-info` (Light Blue)
- **Info** → `bg-primary` (Blue)
- **Debug** → `bg-secondary` (Gray)

## File Structure

```
app/Http/Controllers/Admin/
└── LogController.php          # Main controller with parsing logic

resources/views/admin/logs/
├── index.blade.php           # Advanced log viewer
└── simple.blade.php          # Simple log viewer

routes/
└── admin.php                 # Routes definition
```

## Security Considerations

- All routes are protected by authentication middleware
- Only authenticated admin users can access logs
- Clear logs functionality requires confirmation
- Log file paths are validated to prevent directory traversal

## Customization

### Adding Custom Log Levels
Edit the `getLevelColor()` function in the view or controller:

```php
function getLevelColor($level) {
    return match(strtolower($level)) {
        'emergency', 'alert', 'critical' => 'danger',
        'error' => 'danger',
        'warning' => 'warning',
        'custom_level' => 'success',  // Add custom levels
        default => 'secondary'
    };
}
```

### Performance Optimization

For large log files, consider:

1. **Pagination** - Already implemented in advanced viewer
2. **Log Rotation** - Configure in `config/logging.php`
3. **Caching** - Cache parsed logs for repeated requests
4. **Background Processing** - Process large files in queues

## Browser Compatibility

- Modern browsers with Bootstrap 5 support
- Mobile responsive design
- JavaScript required for advanced features (collapsible content, modals)

## Troubleshooting

### Log File Not Found
- Check if `storage/logs/laravel.log` exists
- Verify file permissions (readable by web server)
- Ensure logging is enabled in `config/logging.php`

### Performance Issues
- Large log files may cause memory issues
- Use pagination and filtering to limit displayed entries
- Consider log rotation for production environments

### Permission Errors
- Ensure web server has read access to storage/logs
- Check Laravel file permissions (755 for directories, 644 for files)

## Example Output

The log viewer will display entries like:

```
[2025-07-30 10:15:32] ERROR - Class "App\Http\Controllers\Admin\Str" not found
[2025-07-30 10:10:15] INFO - User authentication successful
[2025-07-30 10:05:42] WARNING - No users found for type: 30
[2025-07-30 09:52:03] DEBUG - Database query executed in 45ms
```

This implementation provides both simple and advanced options for viewing Laravel logs directly in your Blade templates!
