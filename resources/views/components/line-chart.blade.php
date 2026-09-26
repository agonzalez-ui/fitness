@props(['points', 'unit' => '', 'label' => 'Valor', 'height' => 180])
{{--
    Gráfico de línea de una sola serie (sin leyenda: el título de la tarjeta la nombra).
    $points: lista de ['date' => 'Y-m-d', 'value' => float].
    El SVG solo dibuja la línea (se estira al ancho); ejes, etiquetas y puntos son HTML posicionado en %,
    así el texto conserva su tamaño en celular y en escritorio. Línea de 2px en color de marca, cuadrícula tenue,
    etiqueta directa solo en el último punto y tooltip con guía vertical al pasar el cursor o el dedo.
--}}
@php
    $points = collect($points)->filter(fn ($p) => $p['value'] !== null)->values();
    $n = $points->count();
    $min = $points->min('value'); $max = $points->max('value');
    $range = max($max - $min, 1);
    $min -= $range * 0.15; $max += $range * 0.15;
    $fmt = fn ($v) => rtrim(rtrim(number_format($v, 1), '0'), '.');
    // Coordenadas en porcentaje del área de trazado.
    $coords = $points->map(fn ($p, $i) => [
        'x' => round($n > 1 ? $i * 100 / ($n - 1) : 50, 3),
        'y' => round(($max - $p['value']) * 100 / ($max - $min), 3),
        'value' => $fmt($p['value']),
        'date' => \Illuminate\Support\Carbon::parse($p['date'])->translatedFormat('j M Y'),
    ]);
    $ticks = collect(range(0, 3))->map(fn ($i) => ['y' => 100 - $i * 100 / 3, 'label' => $fmt($min + ($max - $min) * $i / 3)]);
    $last = $coords->last();
@endphp
@if ($n < 2)
    <p class="rounded-xl bg-slate-50 p-4 text-center text-sm text-slate-500">
        {{ $n === 1 ? 'Con un registro más verás aquí la gráfica de '.mb_strtolower($label).'.' : 'Aún no hay registros de '.mb_strtolower($label).'.' }}
    </p>
@else
    <figure {{ $attributes->merge(['class' => 'relative select-none']) }} x-data="{ active: null, points: @js($coords->values()) }"
        role="img" aria-label="Gráfica de {{ mb_strtolower($label) }}: de {{ $coords->first()['value'] }} a {{ $last['value'] }} {{ $unit }}, entre {{ $coords->first()['date'] }} y {{ $last['date'] }}">
        <div class="flex" style="height: {{ $height }}px">
            {{-- Eje Y --}}
            <div class="relative w-9 shrink-0 text-[11px] tabular-nums text-slate-400" aria-hidden="true">
                @foreach ($ticks as $tick)
                    <span class="absolute right-2 -translate-y-1/2" style="top: {{ $tick['y'] }}%">{{ $tick['label'] }}</span>
                @endforeach
            </div>

            {{-- Área de trazado --}}
            <div class="relative flex-1 touch-pan-y"
                x-on:pointermove="
                    const r = $el.getBoundingClientRect();
                    const px = ($event.clientX - r.left) / r.width * 100;
                    active = points.reduce((best, p, i) => Math.abs(p.x - px) < Math.abs(points[best].x - px) ? i : best, 0);"
                x-on:pointerleave="active = null">
                @foreach ($ticks as $tick)
                    <div class="absolute inset-x-0 border-t border-slate-100" style="top: {{ $tick['y'] }}%" aria-hidden="true"></div>
                @endforeach

                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-0 size-full overflow-visible" aria-hidden="true">
                    <polyline points="{{ $coords->map(fn ($c) => $c['x'].','.$c['y'])->implode(' ') }}" fill="none" class="stroke-brand-600"
                        stroke-width="2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                </svg>

                {{-- Guía vertical y punto activo --}}
                <div x-cloak x-show="active !== null" class="pointer-events-none absolute inset-y-0 border-l border-dashed border-slate-300"
                    :style="active !== null && `left: ${points[active].x}%`" aria-hidden="true"></div>

                {{-- Marcadores: extremos siempre visibles, el activo resaltado --}}
                @foreach ($coords as $i => $c)
                    <span @class(['pointer-events-none absolute size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand-600 ring-2 ring-white', 'opacity-0' => $i !== 0 && $i !== $n - 1])
                        :class="active === {{ $i }} && '!opacity-100 scale-125'" style="left: {{ $c['x'] }}%; top: {{ $c['y'] }}%" aria-hidden="true"></span>
                @endforeach

                {{-- Etiqueta directa del último valor --}}
                <span x-show="active === null" class="pointer-events-none absolute -translate-x-full -translate-y-full whitespace-nowrap pb-1.5 pr-1 text-xs font-semibold text-slate-900"
                    style="left: {{ $last['x'] }}%; top: {{ $last['y'] }}%">{{ $last['value'] }} {{ $unit }}</span>

                {{-- Tooltip --}}
                <div x-cloak x-show="active !== null" class="pointer-events-none absolute -top-2 z-10 -translate-y-full whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs text-white shadow"
                    :style="active !== null && `left: ${points[active].x}%; transform: translate(${points[active].x > 70 ? '-100%' : (points[active].x < 30 ? '0' : '-50%')}, -100%)`">
                    <span class="font-semibold" x-text="active !== null && points[active].value + ' {{ $unit }}'"></span>
                    <span class="text-slate-300" x-text="active !== null && points[active].date"></span>
                </div>
            </div>
        </div>

        {{-- Eje X: primera y última fecha --}}
        <figcaption class="ml-9 mt-1.5 flex justify-between text-[11px] text-slate-400" aria-hidden="true">
            <span>{{ $coords->first()['date'] }}</span>
            <span>{{ $last['date'] }}</span>
        </figcaption>
    </figure>
@endif
