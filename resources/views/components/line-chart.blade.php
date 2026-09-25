@props(['points', 'unit' => '', 'label' => 'Valor', 'height' => 180])
{{--
    Gráfico de línea de una sola serie (sin leyenda: el título de la tarjeta la nombra).
    $points: lista de ['date' => 'Y-m-d', 'value' => float]. Línea de 2px en color de marca, cuadrícula tenue,
    etiqueta directa solo en el primer y último punto, y tooltip con guía vertical al pasar el cursor o el dedo.
--}}
@php
    $points = collect($points)->filter(fn ($p) => $p['value'] !== null)->values();
    $w = 600; $h = $height; $padL = 40; $padR = 16; $padT = 16; $padB = 26;
    $min = $points->min('value'); $max = $points->max('value');
    $range = max($max - $min, 1);
    $min -= $range * 0.15; $max += $range * 0.15;
    $n = $points->count();
    $x = fn ($i) => $n > 1 ? $padL + $i * ($w - $padL - $padR) / ($n - 1) : $w / 2;
    $y = fn ($v) => $padT + ($max - $v) * ($h - $padT - $padB) / ($max - $min);
    $coords = $points->map(fn ($p, $i) => ['x' => round($x($i), 1), 'y' => round($y($p['value']), 1), 'value' => $p['value'], 'date' => \Illuminate\Support\Carbon::parse($p['date'])->translatedFormat('j M Y')]);
    $path = $coords->map(fn ($c, $i) => ($i ? 'L' : 'M').$c['x'].' '.$c['y'])->implode(' ');
    $ticks = collect(range(0, 3))->map(fn ($i) => $min + ($max - $min) * $i / 3);
    $fmt = fn ($v) => rtrim(rtrim(number_format($v, 1), '0'), '.');
@endphp
@if ($n < 2)
    <p class="rounded-xl bg-slate-50 p-4 text-center text-sm text-slate-500">
        {{ $n === 1 ? 'Con un registro más verás aquí la gráfica de '.mb_strtolower($label).'.' : 'Aún no hay registros de '.mb_strtolower($label).'.' }}
    </p>
@else
    <div x-data="{ active: null, points: @js($coords->values()) }" class="relative" {{ $attributes }}>
        <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full overflow-visible" role="img" aria-label="Gráfica de {{ mb_strtolower($label) }}: de {{ $fmt($points->first()['value']) }} a {{ $fmt($points->last()['value']) }} {{ $unit }}"
            x-on:pointermove="
                const r = $el.getBoundingClientRect();
                const px = ($event.clientX - r.left) * {{ $w }} / r.width;
                active = points.reduce((best, p, i) => Math.abs(p.x - px) < Math.abs(points[best].x - px) ? i : best, 0);"
            x-on:pointerleave="active = null">
            @foreach ($ticks as $tick)
                <line x1="{{ $padL }}" x2="{{ $w - $padR }}" y1="{{ $y($tick) }}" y2="{{ $y($tick) }}" class="stroke-slate-100" stroke-width="1" />
                <text x="{{ $padL - 8 }}" y="{{ $y($tick) + 4 }}" text-anchor="end" class="fill-slate-400 text-[11px]">{{ $fmt($tick) }}</text>
            @endforeach
            <text x="{{ $padL }}" y="{{ $h - 6 }}" class="fill-slate-400 text-[11px]">{{ $coords->first()['date'] }}</text>
            <text x="{{ $w - $padR }}" y="{{ $h - 6 }}" text-anchor="end" class="fill-slate-400 text-[11px]">{{ $coords->last()['date'] }}</text>

            <path d="{{ $path }}" fill="none" class="stroke-brand-600" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />

            {{-- Etiquetas directas solo en los extremos --}}
            @foreach ([$coords->first(), $coords->last()] as $c)
                <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="4" class="fill-brand-600 stroke-white" stroke-width="2" />
            @endforeach
            <text x="{{ $coords->last()['x'] }}" y="{{ $coords->last()['y'] - 10 }}" text-anchor="end" class="fill-slate-900 text-[12px] font-semibold">{{ $fmt($coords->last()['value']) }} {{ $unit }}</text>

            {{-- Capa de interacción --}}
            <template x-if="active !== null">
                <g>
                    <line :x1="points[active].x" :x2="points[active].x" y1="{{ $padT }}" y2="{{ $h - $padB }}" class="stroke-slate-300" stroke-width="1" stroke-dasharray="3 3" />
                    <circle :cx="points[active].x" :cy="points[active].y" r="5" class="fill-brand-600 stroke-white" stroke-width="2" />
                </g>
            </template>
        </svg>
        <div x-cloak x-show="active !== null" class="pointer-events-none absolute top-0 -translate-x-1/2 rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs text-white shadow"
            :style="active !== null && `left: ${points[active].x / {{ $w }} * 100}%`">
            <span class="font-semibold" x-text="active !== null && (Math.round(points[active].value * 10) / 10) + ' {{ $unit }}'"></span>
            <span class="text-slate-300" x-text="active !== null && points[active].date"></span>
        </div>
    </div>
@endif
