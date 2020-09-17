<?php

namespace Flooris\FileMakerDataApi;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class FileMakerDataApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/filemaker.php' => config_path('filemaker.php'),
        ], 'filemaker-data-api');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filemaker.php', 'filemaker'
        );
    }
}