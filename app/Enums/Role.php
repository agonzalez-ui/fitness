<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Entrenador',
            self::Client => 'Cliente',
        };
    }

    public function home(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Client => 'client.dashboard',
        };
    }
}
