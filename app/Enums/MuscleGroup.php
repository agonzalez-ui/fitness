<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum MuscleGroup: string
{
    use HasOptions;

    case Chest = 'chest';
    case Back = 'back';
    case Shoulders = 'shoulders';
    case Biceps = 'biceps';
    case Triceps = 'triceps';
    case Legs = 'legs';
    case Glutes = 'glutes';
    case Core = 'core';
    case Cardio = 'cardio';
    case FullBody = 'full_body';

    public function label(): string
    {
        return match ($this) {
            self::Chest => 'Pecho',
            self::Back => 'Espalda',
            self::Shoulders => 'Hombros',
            self::Biceps => 'Bíceps',
            self::Triceps => 'Tríceps',
            self::Legs => 'Piernas',
            self::Glutes => 'Glúteos',
            self::Core => 'Abdomen',
            self::Cardio => 'Cardio',
            self::FullBody => 'Cuerpo completo',
        };
    }
}
