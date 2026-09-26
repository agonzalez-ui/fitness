<?php

if (! function_exists('rest_label')) {
    /** 45 → "45 s" · 60 → "1 min" · 75 → "1 min 15 s" · 0 → "Sin descanso" */
    function rest_label(int $seconds): string
    {
        return match (true) {
            $seconds <= 0 => 'Sin descanso',
            $seconds < 60 => $seconds.' s',
            $seconds % 60 === 0 => intdiv($seconds, 60).' min',
            default => intdiv($seconds, 60).' min '.($seconds % 60).' s',
        };
    }
}

if (! function_exists('money')) {
    /** $290 · $1,250.50 · ₡25,000 — símbolo según FITNESS_CURRENCY; sin decimales si el monto es entero. */
    function money(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $symbol = ['USD' => '$', 'CRC' => '₡', 'EUR' => '€'][config('fitness.currency')] ?? config('fitness.currency').' ';
        $decimals = floor($amount) == $amount ? 0 : 2;

        return ($amount < 0 ? '-' : '').$symbol.number_format(abs($amount), $decimals);
    }
}
