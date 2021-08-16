<?php

namespace MstGhi\Larapool;

use Illuminate\Support\ServiceProvider;

class LarapoolServiceProvider extends ServiceProvider
{
    protected $defer = false;

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
        $migrations = __DIR__ . '/../database/migrations/create_larapool_transactions_table.php';
        $config = __DIR__ . '/../config/larapool.php';

        $this->loadMigrationsFrom($migrations);

        $this->publishes([
            $config => config_path('larapool.php'),
        ]);

        $this->publishes([
            $migrations => database_path(
                'migrations/' . date('Y_m_d_His', time()) . '_create_larapool_transactions_table.php'
            )
        ]);
    }
}
