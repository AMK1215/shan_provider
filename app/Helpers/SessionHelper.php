<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SessionHelper
{
    /**
     * Determine the current session based on the time of day.
     *
     * @return string
     */
    public static function getCurrentSession()
    {
        // Create a Carbon instance and set the desired time zone
        $currentTime = Carbon::now()->setTimezone('Asia/Yangon')->format('H:i:s'); // Replace with your actual time zone
        Log::info("Current time is: {$currentTime}");

        // Determine the session based on the current time
        if ($currentTime >= '00:00:00' && $currentTime <= '12:00:00') {
            Log::info('Session is morning');

            return 'morning';
        } elseif ($currentTime > '12:00:00' && $currentTime <= '16:30:00') {
            Log::info('Session is evening');

            return 'evening';
        } else {
            Log::info('Session is closed');

            return 'closed';
        }
    }
}
