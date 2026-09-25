<?php

namespace App\Enums;

use Carbon\CarbonInterface;

/** Nunca se guarda en la BD: se calcula a partir de las fechas de la membresía. */
enum MembershipStatus: string
{
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';
    case None = 'none';

    public const EXPIRING_DAYS = 7;

    public static function fromEndDate(?CarbonInterface $endsOn): self
    {
        return match (true) {
            $endsOn === null => self::None,
            $endsOn->isBefore(today()) => self::Expired,
            $endsOn->lte(today()->addDays(self::EXPIRING_DAYS)) => self::ExpiringSoon,
            default => self::Active,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::ExpiringSoon => 'Por vencer',
            self::Expired => 'Vencida',
            self::None => 'Sin membresía',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::ExpiringSoon => 'amber',
            self::Expired => 'red',
            self::None => 'slate',
        };
    }
}
