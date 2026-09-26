<x-layouts.client title="Comidas">
    @if (! $plan)
        <x-empty-state icon="apple" title="Tu plan de comidas viene en camino" description="Tu entrenador te lo asignará pronto." class="bg-white" />
    @else
        @php
            $totals = $plan->totals;
            $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
            $servingLabel = fn ($s) => match (true) { abs($s - 0.25) < .01 => '¼', abs($s - 0.5) < .01 => '½', abs($s - 0.75) < .01 => '¾', abs($s - 1.5) < .01 => '1½', default => $fmt($s) };
            $now = now()->format('H:i:s');
            $nextMeal = $plan->meals->first(fn ($m) => $m->time && $m->time >= $now);
        @endphp

        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $plan->title }}</h1>

        @if ($totals['calories'])
            <section class="mt-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70" aria-label="Total diario">
                <p class="text-sm text-slate-500">Tu meta diaria</p>
                <p class="text-3xl font-extrabold tabular-nums text-slate-900">{{ number_format($totals['calories']) }} <span class="text-base font-medium text-slate-500">kcal</span></p>
                <x-macro-bar :totals="$totals" class="mt-4" />
            </section>
        @endif

        @if ($plan->file_path)
            <a href="{{ $plan->file_url }}" target="_blank" class="mt-4 flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70">
                <x-icon name="document" class="size-6 text-brand-700" />
                <span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-slate-900">Ver plan en archivo</span><span class="block truncate text-xs text-slate-500">{{ $plan->file_name }}</span></span>
                <x-icon name="chevron-right" class="size-5 text-slate-400" />
            </a>
        @endif

        <div class="mt-5 space-y-4">
            @foreach ($plan->meals as $meal)
                @php $mealKcal = (int) round($meal->items->sum(fn ($i) => $i->food->calories * $i->servings)); @endphp
                <section @class(['overflow-hidden rounded-2xl bg-white shadow-sm ring-1', 'ring-2 ring-brand-500' => $nextMeal?->id === $meal->id, 'ring-slate-200/70' => $nextMeal?->id !== $meal->id])>
                    <header class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                        <div>
                            <h2 class="font-semibold text-slate-900">{{ $meal->name }}</h2>
                            <p class="text-xs text-slate-500">{{ $meal->time ? \Illuminate\Support\Carbon::parse($meal->time)->format('g:i a') : '' }}{{ $nextMeal?->id === $meal->id ? ' · Tu próxima comida' : '' }}</p>
                        </div>
                        <span class="text-sm font-semibold tabular-nums text-slate-700">{{ $mealKcal }} kcal</span>
                    </header>
                    <ul class="divide-y divide-slate-100">
                        @foreach ($meal->items as $item)
                            <li class="flex items-center gap-3 px-4 py-2.5">
                                <span class="w-10 shrink-0 text-center text-sm font-bold text-brand-700">{{ $servingLabel($item->servings) }}×</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900">{{ $item->food->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->food->portion_label }}</p>
                                </div>
                                <span class="text-xs tabular-nums text-slate-500">{{ (int) round($item->food->calories * $item->servings) }} kcal</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        @if ($plan->notes)
            <section class="mt-5 rounded-2xl bg-amber-50 p-4 text-sm text-amber-950 ring-1 ring-amber-200">
                <h2 class="mb-1 font-semibold">Indicaciones de tu entrenador</h2>
                <p class="whitespace-pre-line">{{ $plan->notes }}</p>
            </section>
        @endif

        <p class="mt-6 text-center text-sm text-slate-500">¿Quieres cambiar algo? <a href="{{ route('client.messages') }}" class="font-semibold text-brand-700">Escríbele a tu entrenador</a></p>
    @endif
</x-layouts.client>
