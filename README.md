# Filament Visual Cron Builder

A reusable [Filament v4/v5](https://filamentphp.com) form field that builds cron
expressions visually with native selects. No cron syntax knowledge required, no
JS bundle - the compose/parse logic lives in PHP.

`CronBuilder::make('schedule')` is a drop-in replacement for a `TextInput` bound
to a cron column: its saved state is a standard 5-field cron string.

```
*/15 4,12,20 * * 1-5   ->   "Every 15 minutes At 04:00... on Monday-Friday"
```

## Features

- 5 columns - minute, hour, day-of-month, month, day-of-week.
- Each column has a mode: **Every** (`*`), **Specific** (`1,15,30`), **Range**
  (`1-5`), **Step** (`*/15`).
- Live human-readable preview + raw expression + optional next-run date.
- Round-trips: editing a record parses the existing string back into the columns.
- All compose/parse/validation logic is plain, unit-tested PHP.

## Compatibility

| Package | Filament  | Livewire            | Laravel        | PHP                          |
|---------|-----------|---------------------|----------------|------------------------------|
| 1.1+    | 4.x / 5.x | 3.x (F4) / 4.x (F5) | 11.28+, 12, 13 | 8.2+ (Laravel 13 needs 8.3+) |
| 1.0     | 4.x       | 3.x                 | 11, 12         | 8.2+                         |

Livewire is not a direct dependency - the correct major is pulled in by the
Filament version you install.

## Installation

```bash
composer require interwal-net/filament-visual-cron-builder
```

The service provider is auto-discovered. Optionally publish config and views:

```bash
php artisan vendor:publish --tag=cron-builder-config
php artisan vendor:publish --tag=cron-builder-views
```

> Custom panel theme: add the package views to your theme's `@source` paths so
> Tailwind keeps the field's classes:
> `@source '../../../../vendor/interwal-net/filament-visual-cron-builder/resources/views/**/*';`

## Usage

```php
use InterwalNet\CronBuilder\CronBuilder;

CronBuilder::make('schedule')
    ->showNextRun()      // toggle the next-run preview (default: from config)
    ->required();
```

The field is `live()` by default so the preview recomputes on every change.
`->live()`, `->afterStateUpdated()` and the other state binding modifiers work
like on any Filament field:

```php
CronBuilder::make('schedule')
    ->live(debounce: 500)   // or ->live(onBlur: true), ->live(condition: false)
    ->afterStateUpdated(function (CronBuilder $component) {
        // While editing, the state is the 5-column array; use the helper to
        // get the composed cron string:
        $cron = $component->getComposedExpression();
    });
```

Validate the saved string anywhere with the bundled rule:

```php
use InterwalNet\CronBuilder\Rules\ValidCronExpression;

$request->validate([
    'schedule' => ['required', new ValidCronExpression],
]);
```

## The core helper

`InterwalNet\CronBuilder\Support\CronExpressionBuilder` is standalone and testable:

```php
use InterwalNet\CronBuilder\Support\CronExpressionBuilder as Cron;

Cron::parse('*/15 4,12,20 * * 1-5');        // -> 5 column states
Cron::compose($columns);                     // -> '*/15 4,12,20 * * 1-5'
Cron::humanReadable('30 4 * * 1-5');         // -> 'At 04:30 on Monday-Friday'
Cron::isValid('30 4 * * 1-5');               // -> true
```

## Demo

A bootable Filament panel lives in `workbench/` (via Testbench). It exposes a
`ScheduleResource` using `CronBuilder`, seeded with sample expressions.

```bash
composer serve
```

Then open <http://127.0.0.1:8000/admin> and log in with:

- email: `demo@example.com`
- password: `password`

Edit the "Every 15 min, business hours" record (`*/15 4,12,20 * * 1-5`) to see the
columns parse back from an existing string.

## Testing

```bash
composer test     # vendor/bin/pest
composer format   # vendor/bin/pint
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes.

## License

MIT. See [LICENSE](LICENSE).
