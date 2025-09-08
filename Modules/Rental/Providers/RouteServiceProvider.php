<?php

namespace Modules\Rental\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Rental\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        $adminWeb1 = base_path('Modules/Rental/Routes/web/admin/admin.php');
        $adminWeb2 = base_path('Modules/Modules/Rental/Routes/web/admin/admin.php');
        $adminRouteFile = file_exists($adminWeb1) ? $adminWeb1 : $adminWeb2;

        $vendorWeb1 = base_path('Modules/Rental/Routes/web/vendor/routes.php');
        $vendorWeb2 = base_path('Modules/Modules/Rental/Routes/web/vendor/routes.php');
        $vendorRouteFile = file_exists($vendorWeb1) ? $vendorWeb1 : $vendorWeb2;

        if ($adminRouteFile && file_exists($adminRouteFile)) {
            Route::middleware('web')
                ->prefix('admin')
                ->as('admin.')
                ->namespace($this->moduleNamespace)
                ->group($adminRouteFile);
        }

        if ($vendorRouteFile && file_exists($vendorRouteFile)) {
            Route::middleware('web')
                ->prefix('vendor-panel')
                ->as('vendor.')
                ->namespace($this->moduleNamespace)
                ->group($vendorRouteFile);
        }
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        $api1 = base_path('Modules/Rental/Routes/api/v1/provider/api.php');
        $api2 = base_path('Modules/Modules/Rental/Routes/api/v1/provider/api.php');
        $apiRouteFile = file_exists($api1) ? $api1 : $api2;

        if ($apiRouteFile && file_exists($apiRouteFile)) {
            Route::prefix('api/v1')
                ->middleware('api')
                ->namespace($this->moduleNamespace)
                ->group($apiRouteFile);
        }
    }
}
