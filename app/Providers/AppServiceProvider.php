<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // {client} en las rutas solo resuelve usuarios con rol cliente.
        Route::bind('client', fn (string $id) => User::clients()->findOrFail($id));

        Paginator::defaultView('pagination');
        Model::preventLazyLoading(! $this->app->isProduction());
        Carbon::setLocale(config('app.locale'));
        //
    }
}
