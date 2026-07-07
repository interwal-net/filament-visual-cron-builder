<?php

declare(strict_types=1);

namespace InterwalNet\CronBuilder;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
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

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('cron-builder', __DIR__.'/../resources/css/cron-builder.css'),
        ], package: 'interwal-net/filament-visual-cron-builder');
    }
}
