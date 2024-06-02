<?php

namespace Homeful\Borrower;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Homeful\Borrower\Commands\BorrowerCommand;

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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_borrower_table')
            ->hasCommand(BorrowerCommand::class);
    }
}
