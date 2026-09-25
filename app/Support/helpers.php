<?php

use Illuminate\Support\Number;

if (! function_exists('money')) {
    function money(float|int|string|null $amount): string
    {
        return Number::currency((float) $amount, in: config('fitness.currency'), locale: config('fitness.locale'), precision: 0);
    }
}
