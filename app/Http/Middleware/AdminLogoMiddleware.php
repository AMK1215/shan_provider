<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class AdminLogoMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $logoFilename = null;
            $siteName = null;

            try {
                $current = $user;
                // Traverse up the user tree until Owner or no parent
                while ($current) {
                    // For Owner, use their own logo
                    if ($current->type === UserType::Owner->value) {
                        $logoFilename = $current->agent_logo;
                        $siteName = $current->site_name;
                        break;
                    }

                    // For other roles, check their own logo first
                    if ($current->agent_logo && ! $logoFilename) {
                        $logoFilename = $current->agent_logo;
                    }
                    if ($current->site_name && ! $siteName) {
                        $siteName = $current->site_name;
                    }

                    // Go up the tree
                    if ($current->agent_id) {
                        $current = User::find($current->agent_id);
                    } else {
                        break;
                    }
                }

                // Fallback to user's own if still not found
                if (! $logoFilename) {
                    $logoFilename = $user->agent_logo;
                }
                if (! $siteName) {
                    $siteName = $user->site_name;
                }

                $adminLogo = $logoFilename
                    ? asset('assets/img/logo/'.$logoFilename)
                    : asset('assets/img/logo/slot_maker.png');

                View::share([
                    'adminLogo' => $adminLogo,
                    'siteName' => $siteName ?? 'GSCPLUS',
                ]);
            } catch (\Exception $e) {
                Log::error('Error in AdminLogoMiddleware: '.$e->getMessage());
                // Fallback to default values
                View::share([
                    'adminLogo' => asset('assets/img/logo/slot_maker.png'),
                    'siteName' => 'GSCPLUSSlotGameSite',
                ]);
            }
        }

        return $next($request);
    }

    // public function handle($request, Closure $next)
    // {
    //     if (Auth::check()) {
    //          $logoFilename = Auth::user()->agent_logo;
    // Log::info('Auth User Logo:', ['logo' => $logoFilename]);
    //         $adminLogo = Auth::user()->agent_logo ? asset('assets/img/logo/' . Auth::user()->agent_logo) : asset('assets/img/logo/default-logo.jpg');
    // Log::info('Admin Logo Path:', ['path' => $adminLogo]);
    //         View::share('adminLogo', $adminLogo);
    //     }

    //     return $next($request);
    // }
}
