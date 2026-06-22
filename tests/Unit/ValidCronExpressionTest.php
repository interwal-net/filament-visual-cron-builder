<?php

declare(strict_types=1);

use InterwalNet\CronBuilder\Rules\ValidCronExpression;

function validate(mixed $value): array
{
    $errors = [];
    (new ValidCronExpression)->validate('schedule', $value, function (string $message) use (&$errors) {
        $errors[] = $message;
    });

    return $errors;
}

it('accepts valid cron expressions', function (string $cron) {
    expect(validate($cron))->toBe([]);
})->with([
    '30 4 * * 1-5',
    '*/15 4,12,20 * * 1-5',
    '* * * * *',
    '0 0 1 1 *',
]);

it('rejects garbage', function (mixed $value) {
    expect(validate($value))->not->toBe([]);
})->with([
    'plain text' => ['not a cron'],
    'too few fields' => ['* * *'],
    'out of range' => ['99 * * * *'],
    'non-string' => [12345],
]);
