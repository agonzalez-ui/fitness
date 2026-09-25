<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PaymentMethod: string
{
    use HasOptions;

    case Sinpe = 'sinpe';
    case Transfer = 'transfer';
    case Cash = 'cash';
    case Card = 'card';

    public function label(): string
    {
        return match ($this) {
            self::Sinpe => 'SINPE Móvil',
            self::Transfer => 'Transferencia',
            self::Cash => 'Efectivo',
            self::Card => 'Tarjeta',
        };
    }

    public static function fromLegacy(?string $value): self
    {
        return match (mb_strtolower(trim((string) $value))) {
            'efectivo', 'cash' => self::Cash,
            'tarjeta', 'card' => self::Card,
            'sinpe', 'sinpe movil', 'sinpe móvil' => self::Sinpe,
            default => self::Transfer,
        };
    }
}
