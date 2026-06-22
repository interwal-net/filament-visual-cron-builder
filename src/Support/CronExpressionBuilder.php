<?php

declare(strict_types=1);

namespace InterwalNet\CronBuilder\Support;

use Cron\CronExpression;

/**
 * Pure compose/parse logic for a 5-field cron expression (m h dom mon dow).
 *
 * State model - one entry per position, each:
 *   [
 *     'mode'   => 'every'|'specific'|'range'|'step',
 *     'values' => string[],   // specific:  ['1','15','30']
 *     'from'   => ?string,    // range:     '1'
 *     'to'     => ?string,    // range:     '5'
 *     'step'   => ?string,    // step:      '15'
 *     'base'   => string,     // step base: '*' (default) or e.g. '1-30'
 *   ]
 *
 * compose(parse($cron)) is a stable round-trip for every expression the UI can generate.
 * Tokens outside the UI model (a mixed list like "1-5,10") survive as a raw 'specific' value list.
 */
class CronExpressionBuilder
{
    /** Order matters: it is the cron field order. */
    public const POSITIONS = ['minute', 'hour', 'day', 'month', 'weekday'];

    public const MODES = ['every', 'specific', 'range', 'step'];

    /**
     * Canonical empty field state. Both compose() input and parse() output use this
     * shape so parse(compose($fields)) === $fields holds for UI-generable states.
     *
     * @return array{mode:string,values:array<int,string>,from:?string,to:?string,step:?string,base:string}
     */
    public static function defaultField(): array
    {
        return [
            'mode' => 'every',
            'values' => [],
            'from' => null,
            'to' => null,
            'step' => null,
            'base' => '*',
        ];
    }

    /**
     * One field state -> one cron token.
     *
     * @param  array<string,mixed>  $field
     */
    public static function composeField(array $field): string
    {
        $field = [...self::defaultField(), ...$field];

        return match ($field['mode']) {
            'specific' => self::composeSpecific($field['values']),
            'range' => self::composeRange($field['from'], $field['to']),
            'step' => self::composeStep($field['base'], $field['step']),
            default => '*',
        };
    }

    /**
     * Five field states -> "m h dom mon dow".
     *
     * @param  array<int,array<string,mixed>>  $fields
     */
    public static function compose(array $fields): string
    {
        $tokens = [];

        foreach (self::POSITIONS as $i => $position) {
            $field = $fields[$i] ?? $fields[$position] ?? self::defaultField();
            $tokens[] = self::composeField($field);
        }

        return implode(' ', $tokens);
    }

    /**
     * One cron token -> field state.
     *
     * @return array{mode:string,values:array<int,string>,from:?string,to:?string,step:?string,base:string}
     */
    public static function parseField(string $token): array
    {
        $token = trim($token);
        $field = self::defaultField();

        if ($token === '' || $token === '*') {
            return $field;
        }

        // Step: base/step (base may be '*', a number, or a range).
        if (str_contains($token, '/')) {
            [$base, $step] = explode('/', $token, 2);
            $field['mode'] = 'step';
            $field['base'] = $base === '' ? '*' : $base;
            $field['step'] = $step;

            return $field;
        }

        // Pure range: a-b (no list).
        if (str_contains($token, '-') && ! str_contains($token, ',')) {
            [$from, $to] = explode('-', $token, 2);
            $field['mode'] = 'range';
            $field['from'] = $from;
            $field['to'] = $to;

            return $field;
        }

        // Everything else (single value, list, or mixed list) -> specific value list.
        $field['mode'] = 'specific';
        $field['values'] = array_values(array_filter(
            array_map('trim', explode(',', $token)),
            static fn (string $v): bool => $v !== '',
        ));

        return $field;
    }

    /**
     * "m h dom mon dow" -> five field states keyed 0..4.
     *
     * @return array<int,array{mode:string,values:array<int,string>,from:?string,to:?string,step:?string,base:string}>
     */
    public static function parse(string $cron): array
    {
        $tokens = preg_split('/\s+/', trim($cron)) ?: [];
        $fields = [];

        foreach (self::POSITIONS as $i => $position) {
            $fields[$i] = self::parseField($tokens[$i] ?? '*');
        }

        return $fields;
    }

    /** @param array<int,string> $values */
    private static function composeSpecific(array $values): string
    {
        $values = array_values(array_filter(
            array_map(static fn ($v): string => trim((string) $v), $values),
            static fn (string $v): bool => $v !== '',
        ));

        return $values === [] ? '*' : implode(',', $values);
    }

    private static function composeRange(?string $from, ?string $to): string
    {
        if ($from === null || $from === '' || $to === null || $to === '') {
            return '*';
        }

        return "{$from}-{$to}";
    }

    private static function composeStep(string $base, ?string $step): string
    {
        if ($step === null || $step === '') {
            return $base === '' ? '*' : $base;
        }

        $base = $base === '' ? '*' : $base;

        return "{$base}/{$step}";
    }

    /**
     * Best-effort English description of a cron expression.
     * Falls back to the raw expression if it cannot be parsed.
     */
    public static function humanReadable(string $cron): string
    {
        $cron = trim($cron);

        if (! self::isValid($cron)) {
            return $cron;
        }

        [$minute, $hour, $dom, $month, $dow] = preg_split('/\s+/', $cron);

        $time = self::describeTime($minute, $hour);
        $day = self::describeDay($dom, $month, $dow);

        return trim($time.' '.$day);
    }

    public static function isValid(string $cron): bool
    {
        return CronExpression::isValidExpression(trim($cron));
    }

    private static function describeTime(string $minute, string $hour): string
    {
        if ($minute === '*' && $hour === '*') {
            return 'Every minute';
        }

        // */N in minute, hour is *
        if ($hour === '*' && str_starts_with($minute, '*/')) {
            return 'Every '.substr($minute, 2).' minutes';
        }

        if ($hour === '*') {
            return "Every hour at minute {$minute}";
        }

        if ($minute === '*') {
            return "Every minute past hour {$hour}";
        }

        // Concrete times: pad single minute/hour into HH:MM when both are plain numbers.
        if (ctype_digit($minute) && ctype_digit($hour)) {
            return 'At '.str_pad($hour, 2, '0', STR_PAD_LEFT).':'.str_pad($minute, 2, '0', STR_PAD_LEFT);
        }

        return "At minute {$minute} past hour {$hour}";
    }

    private static function describeDay(string $dom, string $month, string $dow): string
    {
        $parts = [];

        if ($dom !== '*') {
            $parts[] = "on day-of-month {$dom}";
        }

        if ($month !== '*') {
            $parts[] = 'in '.self::describeMonth($month);
        }

        if ($dow !== '*') {
            $parts[] = 'on '.self::describeWeekday($dow);
        }

        if ($parts === []) {
            return 'every day';
        }

        return implode(' ', $parts);
    }

    private static function describeMonth(string $token): string
    {
        return self::describeNamed($token, [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ]);
    }

    private static function describeWeekday(string $token): string
    {
        return self::describeNamed($token, [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
        ]);
    }

    /** @param array<int,string> $names */
    private static function describeNamed(string $token, array $names): string
    {
        // Range a-b
        if (str_contains($token, '-') && ! str_contains($token, ',') && ! str_contains($token, '/')) {
            [$from, $to] = explode('-', $token, 2);
            if (ctype_digit($from) && ctype_digit($to)) {
                return ($names[(int) $from] ?? $from).'-'.($names[(int) $to] ?? $to);
            }
        }

        // List a,b,c
        if (str_contains($token, ',')) {
            $labels = array_map(
                static fn (string $v): string => ctype_digit(trim($v)) ? ($names[(int) trim($v)] ?? $v) : $v,
                explode(',', $token),
            );

            return implode(', ', $labels);
        }

        if (ctype_digit($token)) {
            return $names[(int) $token] ?? $token;
        }

        return $token;
    }
}
