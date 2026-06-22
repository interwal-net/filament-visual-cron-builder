<?php

declare(strict_types=1);

namespace InterwalNet\CronBuilder\Rules;

use Closure;
use Cron\CronExpression;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCronExpression implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! CronExpression::isValidExpression(trim($value))) {
            $fail('The :attribute is not a valid cron expression.');
        }
    }
}
