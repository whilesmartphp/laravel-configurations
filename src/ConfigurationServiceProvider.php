<?php

namespace Whilesmart\ModelConfiguration;

use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['model-configuration', 'model-configuration-migrations']);

        if (config('model-configuration.register_routes', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/model-configuration.php');
        }

        $this->publishes([
            __DIR__.'/../routes/model-configuration.php' => base_path('routes/model-configuration.php'),
        ], ['model-configuration', 'model-configuration-routes', 'model-configuration-controllers']);

        $this->publishes([
            __DIR__.'/Http/Controllers' => app_path('Http/Controllers'),
        ], ['model-configuration', 'model-configuration-controllers']);

        // Publish config
        $this->publishes([
            __DIR__.'/../config/model-configuration.php' => config_path('model-configuration.php'),
        ], ['model-configuration', 'model-configuration-config']);
    }
}
