<?php

declare(strict_types=1);

use InterwalNet\CronBuilder\Support\CronExpressionBuilder as Builder;

function field(array $overrides = []): array
{
    return [...Builder::defaultField(), ...$overrides];
}

// --- composeField: one field state -> one token --------------------------------

it('composes every -> *', function () {
    expect(Builder::composeField(field(['mode' => 'every'])))->toBe('*');
});

it('composes specific -> comma list', function () {
    expect(Builder::composeField(field(['mode' => 'specific', 'values' => ['1', '15', '30']])))
        ->toBe('1,15,30');
});

it('composes range -> from-to', function () {
    expect(Builder::composeField(field(['mode' => 'range', 'from' => '1', 'to' => '5'])))
        ->toBe('1-5');
});

it('composes step -> base/step', function () {
    expect(Builder::composeField(field(['mode' => 'step', 'step' => '15'])))->toBe('*/15');
    expect(Builder::composeField(field(['mode' => 'step', 'base' => '1-30', 'step' => '5'])))
        ->toBe('1-30/5');
});

it('falls back to * for empty/partial states', function (array $f) {
    expect(Builder::composeField(field($f)))->toBe('*');
})->with([
    'empty specific' => [['mode' => 'specific', 'values' => []]],
    'partial range' => [['mode' => 'range', 'from' => '1', 'to' => null]],
    'step without step' => [['mode' => 'step', 'step' => null]],
]);

// --- compose: five fields -> full expression -----------------------------------

it('joins five fields into m h dom mon dow', function () {
    $fields = [
        field(['mode' => 'step', 'step' => '15']),       // */15
        field(['mode' => 'specific', 'values' => ['4', '12', '20']]), // 4,12,20
        field(['mode' => 'every']),                       // *
        field(['mode' => 'every']),                       // *
        field(['mode' => 'range', 'from' => '1', 'to' => '5']), // 1-5
    ];

    expect(Builder::compose($fields))->toBe('*/15 4,12,20 * * 1-5');
});

it('defaults missing positions to every', function () {
    expect(Builder::compose([]))->toBe('* * * * *');
});

// --- parseField ----------------------------------------------------------------

it('parses tokens back into modes', function (string $token, string $mode) {
    expect(Builder::parseField($token)['mode'])->toBe($mode);
})->with([
    ['*', 'every'],
    ['', 'every'],
    ['1,15,30', 'specific'],
    ['30', 'specific'],
    ['1-5', 'range'],
    ['*/15', 'step'],
    ['1-30/5', 'step'],
]);

it('keeps a mixed token as a specific value list without throwing', function () {
    $parsed = Builder::parseField('1-5,10');

    expect($parsed['mode'])->toBe('specific')
        ->and($parsed['values'])->toBe(['1-5', '10']);
});

it('round-trips a mixed token through compose', function () {
    expect(Builder::composeField(Builder::parseField('1-5,10')))->toBe('1-5,10');
});

// --- round-trip: parse(compose(fields)) === fields for UI-generable states ------

it('round-trips every UI-generable field state', function (array $f) {
    $state = field($f);

    expect(Builder::parseField(Builder::composeField($state)))->toBe($state);
})->with([
    'every' => [['mode' => 'every']],
    'specific single' => [['mode' => 'specific', 'values' => ['30']]],
    'specific list' => [['mode' => 'specific', 'values' => ['1', '15', '30']]],
    'range' => [['mode' => 'range', 'from' => '1', 'to' => '5']],
    'step' => [['mode' => 'step', 'step' => '15']],
    'step with base' => [['mode' => 'step', 'base' => '1-30', 'step' => '5']],
]);

it('round-trips a full expression', function (string $cron) {
    expect(Builder::compose(Builder::parse($cron)))->toBe($cron);
})->with([
    '* * * * *',
    '*/15 4,12,20 * * 1-5',
    '30 4 * * 1-5',
    '0 0 1 1 *',
    '5 0 * 8 *',
    '0 22 * * 1-5',
]);

// --- humanReadable -------------------------------------------------------------

it('describes common expressions', function (string $cron, string $contains) {
    expect(Builder::humanReadable($cron))->toContain($contains);
})->with([
    ['* * * * *', 'Every minute'],
    ['*/15 * * * *', 'Every 15 minutes'],
    ['30 4 * * *', '04:30'],
    ['30 4 * * 1-5', 'Monday-Friday'],
    ['0 0 1 1 *', 'January'],
    ['*/15 4,12,20 * * 1-5', 'Monday-Friday'],
]);

it('returns the raw string for an unparseable expression', function () {
    expect(Builder::humanReadable('not a cron'))->toBe('not a cron');
});
