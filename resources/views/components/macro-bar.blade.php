@props(['totals', 'size' => 'md'])
{{--
    Reparto de calorías por macronutriente (proteína y carbos 4 kcal/g, grasa 9 kcal/g) en una barra apilada
    con 2px de separación entre segmentos. Cada segmento lleva su etiqueta en texto, así el color no es la única pista.
--}}
@php
    $kcal = ['protein' => $totals['protein_g'] * 4, 'carbs' => $totals['carbs_g'] * 4, 'fats' => $totals['fats_g'] * 9];
    $sum = array_sum($kcal) ?: 1;
    $parts = [
        ['Proteína', $totals['protein_g'], $kcal['protein'] / $sum, 'bg-sky-600'],
        ['Carbohidratos', $totals['carbs_g'], $kcal['carbs'] / $sum, 'bg-amber-500'],
        ['Grasa', $totals['fats_g'], $kcal['fats'] / $sum, 'bg-rose-600'],
    ];
@endphp
<div {{ $attributes }}>
    <div class="flex h-2.5 gap-0.5 overflow-hidden rounded-full bg-slate-100" role="img"
        aria-label="{{ collect($parts)->map(fn ($p) => $p[0].' '.round($p[2] * 100).'%')->implode(', ') }}">
        @foreach ($parts as [$label, $grams, $share, $color])
            @if ($share > 0)<div class="{{ $color }} h-full first:rounded-l-full last:rounded-r-full" style="width: {{ round($share * 100, 1) }}%"></div>@endif
        @endforeach
    </div>
    <dl @class(['mt-2 grid grid-cols-3 gap-2', 'text-xs' => $size === 'sm', 'text-sm' => $size !== 'sm'])>
        @foreach ($parts as [$label, $grams, $share, $color])
            <div>
                <dt class="flex items-center gap-1.5 text-slate-500"><span class="size-2 shrink-0 rounded-full {{ $color }}"></span>{{ $label }}</dt>
                <dd class="font-semibold tabular-nums text-slate-900">{{ round($grams) }} g <span class="font-normal text-slate-400">· {{ round($share * 100) }}%</span></dd>
            </div>
        @endforeach
    </dl>
</div>
