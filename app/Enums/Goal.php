<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Goal: string
{
    use HasOptions;

    case WeightLoss = 'weight_loss';
    case MuscleGain = 'muscle_gain';
    case Toning = 'toning';
    case Maintenance = 'maintenance';
    case Endurance = 'endurance';
    case Flexibility = 'flexibility';
    case GeneralFitness = 'general_fitness';

    public function label(): string
    {
        return match ($this) {
            self::WeightLoss => 'Perder peso',
            self::MuscleGain => 'Ganar masa muscular',
            self::Toning => 'Tonificar',
            self::Maintenance => 'Mantenerme',
            self::Endurance => 'Resistencia',
            self::Flexibility => 'Flexibilidad',
            self::GeneralFitness => 'Salud general',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WeightLoss => '🔥',
            self::MuscleGain => '💪',
            self::Toning => '✨',
            self::Maintenance => '⚖️',
            self::Endurance => '🏃',
            self::Flexibility => '🧘',
            self::GeneralFitness => '❤️',
        };
    }

    /** Traduce los valores de texto libre de la BD anterior. */
    public static function fromLegacy(?string $value): ?self
    {
        $value = mb_strtolower(trim((string) $value));

        return self::tryFrom($value) ?? match (true) {
            $value === '' => null,
            str_contains($value, 'perder') || str_contains($value, 'peso') => self::WeightLoss,
            str_contains($value, 'masa') || str_contains($value, 'músculo') => self::MuscleGain,
            str_contains($value, 'tonific') => self::Toning,
            default => self::GeneralFitness,
        };
    }
}
