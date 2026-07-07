<?php

declare(strict_types=1);

namespace InterwalNet\CronBuilder;

use Cron\CronExpression;
use Filament\Forms\Components\Field;
use InterwalNet\CronBuilder\Support\CronExpressionBuilder as Builder;

/**
 * Visual cron builder field. State while editing is a 5-position array
 * (minute/hour/day/month/weekday), each: {mode, values, from, to, step, base}.
 * It dehydrates to a standard cron string, so it is a drop-in replacement for a
 * TextInput bound to a cron column.
 *
 * The columns render as plain selects bound with wire:model to nested state
 * paths (live by default); every change re-renders server-side so the preview
 * is computed in PHP. ->live(), ->afterStateUpdated() and the other state
 * binding modifiers behave like on any other Filament field.
 */
class CronBuilder extends Field
{
    protected string $view = 'cron-builder::cron-builder';

    /** State keys, in cron field order. */
    public const POSITION_KEYS = ['minute', 'hour', 'day', 'month', 'weekday'];

    protected bool $showNextRun = true;

    protected string $layout = 'grid';

    protected bool $showTabTokens = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->showNextRun = (bool) config('cron-builder.show_next_run', true);
        $this->layout((string) config('cron-builder.layout', 'grid'));
        $this->showTabTokens = (bool) config('cron-builder.show_tab_tokens', false);

        // Live by default so the preview recomputes on every change. Callers can
        // override with ->live(onBlur:/debounce:) or ->live(condition: false).
        $this->live();

        // Empty value -> "every minute", never a blank field.
        $this->default('* * * * *');

        // Saved cron string -> working column array.
        $this->afterStateHydrated(static function (CronBuilder $component, mixed $state): void {
            $component->state($component->stringToColumns(is_string($state) && $state !== '' ? $state : '* * * * *'));
        });

        // Working column array -> saved cron string.
        $this->dehydrateStateUsing(static function (mixed $state): string {
            return is_array($state) ? Builder::compose($state) : (string) $state;
        });
    }

    public function showNextRun(bool $condition = true): static
    {
        $this->showNextRun = $condition;

        return $this;
    }

    /** @param 'grid'|'tabs' $layout */
    public function layout(string $layout): static
    {
        $this->layout = in_array($layout, ['grid', 'tabs'], true) ? $layout : 'grid';

        return $this;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function showTabTokens(bool $condition = true): static
    {
        $this->showTabTokens = $condition;

        return $this;
    }

    public function shouldShowTabTokens(): bool
    {
        return $this->showTabTokens;
    }

    public function shouldShowNextRun(): bool
    {
        return $this->showNextRun;
    }

    /** @return array<string,array<string,mixed>> */
    public function stringToColumns(string $cron): array
    {
        $parsed = Builder::parse($cron);
        $columns = [];

        foreach (self::POSITION_KEYS as $i => $key) {
            $columns[$key] = $parsed[$i];
        }

        return $columns;
    }

    /** Current state coerced to the 5-column shape (for the view). @return array<string,array<string,mixed>> */
    public function getCronColumns(): array
    {
        $state = $this->getState();

        if (! is_array($state)) {
            $state = $this->stringToColumns(is_string($state) ? $state : '* * * * *');
        }

        $columns = [];

        foreach (self::POSITION_KEYS as $key) {
            $column = is_array($state[$key] ?? null)
                ? [...Builder::defaultField(), ...$state[$key]]
                : Builder::defaultField();

            // Drop range rows the user cleared out completely, keep half-filled ones.
            $column['ranges'] = array_values(array_filter(
                is_array($column['ranges']) ? $column['ranges'] : [],
                static fn ($range): bool => is_array($range)
                    && (trim((string) ($range['from'] ?? '')) !== '' || trim((string) ($range['to'] ?? '')) !== ''),
            ));

            $columns[$key] = $column;
        }

        return $columns;
    }

    public function getComposedExpression(): string
    {
        return Builder::compose($this->getCronColumns());
    }

    public function getHumanReadable(): string
    {
        return Builder::humanReadable($this->getComposedExpression());
    }

    public function getNextRunDate(): ?string
    {
        $cron = $this->getComposedExpression();

        if (! Builder::isValid($cron)) {
            return null;
        }

        try {
            return (new CronExpression($cron))->getNextRunDate()->format('Y-m-d H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string,string> mode value => translated label */
    public function getModeOptions(): array
    {
        return [
            'every' => __('cron-builder::cron-builder.modes.every'),
            'specific' => __('cron-builder::cron-builder.modes.specific'),
            'range' => __('cron-builder::cron-builder.modes.range'),
            'step' => __('cron-builder::cron-builder.modes.step'),
        ];
    }

    /** @return array<string,string> position key => translated label */
    public function getPositionLabels(): array
    {
        $labels = [];

        foreach (self::POSITION_KEYS as $key) {
            $labels[$key] = __("cron-builder::cron-builder.positions.{$key}");
        }

        return $labels;
    }

    /** Value options for a position's specific/range selects. @return array<string,string> */
    public function getValueOptions(string $key): array
    {
        return match ($key) {
            'minute' => self::numericOptions(0, 59),
            'hour' => self::numericOptions(0, 23),
            'day' => self::numericOptions(1, 31),
            'month' => [
                '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
            ],
            'weekday' => [
                '0' => 'Sunday', '1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday',
                '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday',
            ],
            default => self::numericOptions(0, 59),
        };
    }

    /** Compact labels for the chip grid (short month/weekday names). @return array<string,string> */
    public function getChipOptions(string $key): array
    {
        $options = $this->getValueOptions($key);

        if (in_array($key, ['month', 'weekday'], true)) {
            return array_map(static fn (string $label): string => substr($label, 0, 3), $options);
        }

        return $options;
    }

    /** Step options for a position. @return array<string,string> */
    public function getStepOptions(string $key): array
    {
        $max = match ($key) {
            'minute' => 30,
            'hour' => 12,
            'day' => 15,
            'month' => 6,
            'weekday' => 3,
            default => 30,
        };

        return self::numericOptions(1, $max);
    }

    /** @return array<string,string> */
    protected static function numericOptions(int $from, int $to): array
    {
        $options = [];

        for ($i = $from; $i <= $to; $i++) {
            $options[(string) $i] = (string) $i;
        }

        return $options;
    }
}
