<?php

declare(strict_types=1);

namespace InterwalNet\CronBuilder\Tests\Fixtures;

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\MessageBag;
use InterwalNet\CronBuilder\CronBuilder;
use Livewire\Component;

class TabsFormComponent extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public array $data = [];

    public function mount(array $data = []): void
    {
        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                CronBuilder::make('schedule')->layout('tabs'),
            ])
            ->statePath('data');
    }

    public function render(): View
    {
        return view('cron-builder-tests::form-component');
    }

    // Testbench does not seed a Livewire error bag; render() crashes without one.
    public function getErrorBag()
    {
        return new MessageBag;
    }
}
