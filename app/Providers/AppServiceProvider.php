<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Config;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') != 'local') {
            URL::forceScheme('http');
        }

         view()->composer('*', function ($view)
        {
              $title = Config::get('constants.CDN_URL');

            $view->with('CDN_URL', $title);
        });
    }
}
