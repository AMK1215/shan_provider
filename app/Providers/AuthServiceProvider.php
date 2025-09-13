<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Admin\Permission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        try {
            foreach (Permission::all() as $permission) {
                Gate::define($permission->title, function ($user) use ($permission) {
                    return $user->hasPermission($permission->title);
                });
            }
        } catch (\Exception $e) {
            // Table might not exist yet, ignore
        }
    }
}
