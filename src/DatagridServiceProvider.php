<?php

namespace Datagrid;

use Illuminate\Support\ServiceProvider;

class DatagridServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'datagrid');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->mergeConfigFrom(__DIR__.'/config/datagrid.php', 'datagrid');
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'datagrid');
        $this->publishes([
            __DIR__.'/config/datagrid.php' => config_path('datagrid.php'),
        ]);
        $this->publishes([
            __DIR__.'/public' => public_path('data-grid'),
        ]);
    }

    public function register()
    {
        $this->app->bind('datagrid', function () {
            return new DataGrid();
        });
    }
}
