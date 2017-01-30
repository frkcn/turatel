<?php

namespace Frkcn\Turatel;

use Illuminate\Support\ServiceProvider;

class TuratelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        include __DIR__.'/routes.php';

        $this->publishes([
            __DIR__.'/Config/turatel.php' => config_path('turatel.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/Config/turatel.php', 'turatel');
        //
        $this->app->make('Frkcn\Turatel\TuratelController');
    }
}
