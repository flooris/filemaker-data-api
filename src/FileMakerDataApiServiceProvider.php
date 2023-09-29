<?php

namespace Flooris\FileMakerDataApi;

use Illuminate\Support\ServiceProvider;

class FileMakerDataApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/filemaker.php' => config_path('filemaker.php'),
        ], 'filemaker-data-api');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filemaker.php', 'filemaker'
        );
    }
}