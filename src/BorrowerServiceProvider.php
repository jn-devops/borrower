<?php

namespace Homeful\Borrower;

use Spatie\LaravelPackageTools\PackageServiceProvider;
use Homeful\Borrower\Commands\BorrowerCommand;
use Spatie\LaravelPackageTools\Package;

class BorrowerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('borrower')
            ->hasConfigFile(['borrower'])
            ->hasCommand(BorrowerCommand::class);
    }
}
