<?php

declare(strict_types=1);

use InterwalNet\CronBuilder\CronBuilder;
use InterwalNet\CronBuilder\Tests\Fixtures\AfterStateUpdatedFormComponent;
use InterwalNet\CronBuilder\Tests\Fixtures\FormComponent;
use InterwalNet\CronBuilder\Tests\Fixtures\TabsFormComponent;
use InterwalNet\CronBuilder\Tests\TestCase;
use Livewire\Livewire;

uses(TestCase::class);

it('hydrates an existing cron string into column state', function () {
    $component = Livewire::test(FormComponent::class, ['data' => ['schedule' => '*/15 4,12,20 * * 1-5']]);

    $data = $component->get('data');

    expect($data['schedule']['minute'])->toMatchArray(['mode' => 'step', 'step' => '15'])
        ->and($data['schedule']['hour'])->toMatchArray(['mode' => 'specific', 'values' => ['4', '12', '20']])
        ->and($data['schedule']['day']['mode'])->toBe('every')
        ->and($data['schedule']['month']['mode'])->toBe('every')
        ->and($data['schedule']['weekday'])->toMatchArray(['mode' => 'range', 'ranges' => [['from' => '1', 'to' => '5']]]);
});

it('dehydrates column state back to the same cron string', function () {
    $state = Livewire::test(FormComponent::class, ['data' => ['schedule' => '*/15 4,12,20 * * 1-5']])
        ->instance()
        ->form
        ->getState();

    expect($state['schedule'])->toBe('*/15 4,12,20 * * 1-5');
});

it('defaults an empty field to every-minute', function () {
    $state = Livewire::test(FormComponent::class)
        ->instance()
        ->form
        ->getState();

    expect($state['schedule'])->toBe('* * * * *');
});

it('renders the builder columns and the live preview', function () {
    Livewire::test(FormComponent::class, ['data' => ['schedule' => '*/15 4,12,20 * * 1-5']])
        ->assertOk()
        ->assertSee('schedule.minute.mode')
        ->assertSee('*/15 4,12,20 * * 1-5');
});

it('loads the package translations (namespace resolves)', function () {
    expect(trans('cron-builder::cron-builder.modes.every'))->toBe('Every')
        ->and(trans('cron-builder::cron-builder.positions.minute'))->toBe('Minute');
});

it('recomposes the cron string after a column is changed', function () {
    $component = Livewire::test(FormComponent::class, ['data' => ['schedule' => '* * * * *']])
        ->set('data.schedule.minute.mode', 'step')
        ->set('data.schedule.minute.step', '15');

    $state = $component->instance()->form->getState();

    expect($state['schedule'])->toBe('*/15 * * * *');
});

it('is live by default and honours live() overrides', function () {
    // blur/debounce modifier syntax differs between Livewire 3 (Filament v4)
    // and Livewire 4 (Filament v5), so only assert the parts we control.
    expect(CronBuilder::make('schedule')->applyStateBindingModifiers('wire:model'))
        ->toBe('wire:model.live')
        ->and(CronBuilder::make('schedule')->live(condition: false)->applyStateBindingModifiers('wire:model'))
        ->toBe('wire:model')
        ->and(CronBuilder::make('schedule')->live(onBlur: true)->applyStateBindingModifiers('wire:model'))
        ->toStartWith('wire:model')->toContain('.blur')
        ->and(CronBuilder::make('schedule')->live(debounce: 500)->applyStateBindingModifiers('wire:model'))
        ->toStartWith('wire:model')->toContain('.debounce.500');
});

it('renders live wire:model bindings by default', function () {
    Livewire::test(FormComponent::class, ['data' => ['schedule' => '* * * * *']])
        ->assertOk()
        ->assertSee('wire:model.live="data.schedule.minute.mode"', escape: false);
});

it('defaults to the grid layout and accepts layout overrides', function () {
    expect(CronBuilder::make('schedule')->getLayout())->toBe('grid')
        ->and(CronBuilder::make('schedule')->layout('tabs')->getLayout())->toBe('tabs')
        ->and(CronBuilder::make('schedule')->layout('nonsense')->getLayout())->toBe('grid');
});

it('reads the default layout from config', function () {
    config()->set('cron-builder.layout', 'tabs');

    expect(CronBuilder::make('schedule')->getLayout())->toBe('tabs');
});

it('renders the tab bar in tabs layout', function () {
    Livewire::test(TabsFormComponent::class, ['data' => ['schedule' => '*/15 4,12,20 * * 1-5']])
        ->assertOk()
        ->assertSee('cb-tabbar')
        ->assertSee('cb-tab-token');
});

it('fires afterStateUpdated when a nested column changes', function () {
    $component = Livewire::test(AfterStateUpdatedFormComponent::class, ['data' => ['schedule' => '* * * * *']])
        ->set('data.schedule.minute.mode', 'step')
        ->set('data.schedule.minute.step', '15');

    expect($component->get('captured'))->toBe('*/15 * * * *');
});
