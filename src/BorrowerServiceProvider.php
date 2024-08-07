<?php

namespace Homeful\Borrower;

use Homeful\Borrower\Commands\BorrowerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasViews()
            ->hasMigration('create_borrower_table')
            ->hasCommand(BorrowerCommand::class);
    }
}
