<?php

namespace Zaichaopan\Permission;

use Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Zaichaopan\Permission\Models\Permission;

class RoleAndPermissionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        Permission::get()->map(function ($permission) {
            Gate::define($permission->name, function ($user) use ($permission) {
                return $user->hasPermission($permission);
            });
        });

        Blade::directive('role', function ($role) {
            return "<?php if (auth()->check() && auth()->user()->hasRole($role)); ?>";
        });

        Blade::directive('endrole', function ($role) {
            return '<?php endif; ?>';
        });
    }

    /**
    * Register the application services.
    *
    * @return void
    */
    public function register()
    {
        //
    }
}
