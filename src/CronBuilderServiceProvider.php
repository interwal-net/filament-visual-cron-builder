<?php

declare(strict_types=1);

namespace InterwalNet\CronBuilder;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CronBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        // Short name drives the views/translations/config namespace -> "cron-builder::".
        $package
            ->name('cron-builder')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews();
    }
}
