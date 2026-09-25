<?php

namespace App\Support;

use Illuminate\Support\Str;

class TemporaryPassword
{
    /** Contraseña fácil de dictar o mandar por WhatsApp: "Fit-7K3P9Q". Sin 0/O ni 1/I. */
    public static function generate(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return 'Fit-'.collect(range(1, 6))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');
    }

    public static function whatsappLink(?string $phone, string $message): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        // Números de Costa Rica de 8 dígitos sin código de país.
        if (strlen($digits) === 8) {
            $digits = '506'.$digits;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }

    public static function welcomeMessage(string $name, string $email, string $password): string
    {
        return 'Hola '.Str::before($name, ' ').'! 💪 Ya tienes tu cuenta en FZ FITCR: '.route('login')
            ."\nCorreo: $email\nContraseña: $password\nPuedes cambiarla desde tu perfil.";
    }
}
