<?php

namespace Zaichaopan\AccessGranted;

use Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class AccessGrantedServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        $this->registerPermissions();
    }

    /**
    * Register the application services.
    *
    * @return void
    */
    public function register()
    {
        $this->registerBladeExtensions();
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

    protected function registerPermissions()
    {
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability);
            }
            throw new \Exception('Method hasPermission does not exist!');
        });
    }
}
