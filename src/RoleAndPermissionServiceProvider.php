<?php

namespace Zaichaopan\Permission;

use Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
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

        // Permission::get()->map(function ($permission) {
        //     Gate::define($permission->name, function ($user) use ($permission) {
        //         return $user->hasPermission($permission);
        //     });
        // });

        $this->registerBladeExtensions();
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

    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('role', function ($role) {
                return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
            });
            
            $bladeCompiler->directive('endrole', function () {
                return '<?php endif; ?>';
            });
        });
    }
}
