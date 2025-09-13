<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();
        // Log::info('Permission check', [
        //     'user_id' => $user->id,
        //     'roles' => $user->roles->pluck('title')->toArray(),
        //     'permissions' => $user->permissions->pluck('title')->toArray(),
        //     'checking_for' => $permission,
        // ]);

        if ($user->hasRole('Owner')) {
            return $next($request);
        }

        // If user is a parent agent, they have all permissions
        if ($user->hasRole('Agent')) {
            return $next($request);
        }

        // If user is a sub-agent, check their specific permissions
        if ($user->hasRole('SubAgent')) {
            $requiredPermissions = explode('|', $permission);
            foreach ($requiredPermissions as $p) {
                if ($user->hasPermission($p)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Unauthorized action. || ဤလုပ်ဆောင်ချက်အား သင့်မှာ လုပ်ဆောင်ပိုင်ခွင့်မရှိပါ, ကျေးဇူးပြု၍ သက်ဆိုင်ရာ Agent များထံ ဆက်သွယ်ပါ');
    }
}
