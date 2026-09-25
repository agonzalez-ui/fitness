<?php

namespace App\Enums\Concerns;

trait HasOptions
{
    /** @return array<string, string> valor => etiqueta, listo para selects y botones. */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
