<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Level: string
{
    use HasOptions;

    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    public function label(): string
    {
        return match ($this) {
            self::Beginner => 'Principiante',
            self::Intermediate => 'Intermedio',
            self::Advanced => 'Avanzado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Beginner => 'green',
            self::Intermediate => 'amber',
            self::Advanced => 'red',
        };
    }
}
