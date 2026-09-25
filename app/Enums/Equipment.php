<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Equipment: string
{
    use HasOptions;

    case Bodyweight = 'bodyweight';
    case Dumbbell = 'dumbbell';
    case Barbell = 'barbell';
    case Machine = 'machine';
    case Cable = 'cable';
    case Kettlebell = 'kettlebell';
    case Band = 'band';

    public function label(): string
    {
        return match ($this) {
            self::Bodyweight => 'Peso corporal',
            self::Dumbbell => 'Mancuernas',
            self::Barbell => 'Barra',
            self::Machine => 'Máquina',
            self::Cable => 'Polea',
            self::Kettlebell => 'Pesa rusa',
            self::Band => 'Banda elástica',
        };
    }
}
