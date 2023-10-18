<?php

namespace Flooris\FileMakerDataApi;

use Flooris\FileMakerDataApi\Commands\FileMakerDataApiCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;


class FileMakerDataApiServiceProvider extends PackageServiceProvider
{
    public function
    configurePackage(Package $package): void
    {
        /*
         * This class is a Package ServiceProvider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name('filemaker-data-api')
            ->hasConfigFile('filemaker')
            ->hasCommand(FileMakerDataApiCommand::class);
    }
}
