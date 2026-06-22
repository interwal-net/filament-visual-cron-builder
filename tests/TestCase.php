<?php

declare(strict_types=1);

namespace InterwalNet\CronBuilder\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use InterwalNet\CronBuilder\CronBuilderServiceProvider;
use InterwalNet\CronBuilder\Tests\Fixtures\FormComponent;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        View::addNamespace('cron-builder-tests', __DIR__.'/Fixtures/views');
        View::share('errors', new ViewErrorBag);
        Livewire::component('form-component', FormComponent::class);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            SchemasServiceProvider::class,
            NotificationsServiceProvider::class,
            CronBuilderServiceProvider::class,
        ];
    }
}
