<?php

namespace MstGhi\Larapool;

use Illuminate\Support\ServiceProvider;

class LarapoolServiceProvider extends ServiceProvider
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
        $migrations = __DIR__ . '/../migrations/';
        $config = __DIR__ . '/../config/larapool.php';

        $this->loadMigrationsFrom($migrations);

        //php artisan vendor:publish --provider=MstGhi\Larapool\LarapoolServiceProvider --tag=config
        $this->publishes([
            $config => config_path('gateway.php'),
        ], 'config')
        ;

        // php artisan vendor:publish --provider=MstGhi\Larapool\LarapoolServiceProvider --tag=migrations
        $this->publishes([
            $migrations => base_path('database/migrations')
        ], 'migrations');
    }
}
