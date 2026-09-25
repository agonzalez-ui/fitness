<x-layouts.client title="Hoy">
    <div class="mb-5">
        <p class="text-sm font-medium capitalize text-slate-500">{{ now()->translatedFormat('l j \d\e F') }}</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Hola, {{ $user->first_name }} 👋</h1>
    </div>

    {{-- Aviso de membresía solo cuando hace falta actuar --}}
    @php($status = $user->membership_status)
    @if ($status !== \App\Enums\MembershipStatus::Active)
        <div @class([
            'mb-5 flex items-start gap-3 rounded-2xl p-4 text-sm ring-1',
            'bg-amber-50 text-amber-900 ring-amber-200' => $status === \App\Enums\MembershipStatus::ExpiringSoon,
            'bg-red-50 text-red-900 ring-red-200' => $status === \App\Enums\MembershipStatus::Expired,
            'bg-slate-100 text-slate-700 ring-slate-200' => $status === \App\Enums\MembershipStatus::None,
        ])>
            <x-icon name="card" class="size-5 shrink-0" />
            <p class="flex-1">
                @switch($status)
                    @case(\App\Enums\MembershipStatus::ExpiringSoon)
                        Tu membresía vence {{ $membership->ends_on->isToday() ? 'hoy' : 'en '.$membership->days_left.' '.str('día')->plural($membership->days_left) }}. Habla con tu entrenador para renovarla.
                        @break
                    @case(\App\Enums\MembershipStatus::Expired)
                        Tu membresía venció el {{ $membership->ends_on->translatedFormat('j \d\e F') }}. Renuévala para seguir con tu plan.
                        @break
                    @default
                        Aún no tienes una membresía activa. Tu entrenador la activará pronto.
                @endswitch
            </p>
        </div>
    @endif

    {{-- Tarjeta principal: el entrenamiento de hoy --}}
    @if (! $plan)
        <x-empty-state icon="dumbbell" title="Tu rutina viene en camino" description="Tu entrenador está preparando tu plan. Te avisaremos aquí cuando esté listo." class="bg-white">
            @if (Route::has('client.messages'))
                <x-button :href="route('client.messages')" variant="secondary" icon="chat">Escribir a mi entrenador</x-button>
            @endif
        </x-empty-state>
    @elseif ($today)
        <section class="overflow-hidden rounded-3xl bg-slate-900 text-white shadow-lg">
            <div class="relative p-6">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,var(--color-brand-600)_0%,transparent_60%)] opacity-50"></div>
                <div class="relative">
                    <p class="text-sm font-semibold uppercase tracking-wider text-brand-300">{{ $todayDone ? '¡Completado hoy!' : 'Hoy te toca' }}</p>
                    <h2 class="mt-1 text-3xl font-extrabold">{{ $today->name }}</h2>
                    <p class="mt-2 text-sm text-slate-300">
                        {{ $today->items->count() }} ejercicios ·
                        aprox. {{ (int) ceil($today->items->sum(fn ($item) => $item->sets * (45 + $item->rest_seconds)) / 60) }} min
                    </p>
                    <ul class="mt-5 space-y-2">
                        @foreach ($today->items->take(4) as $item)
                            <li class="flex items-center gap-3 text-sm">
                                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs font-bold">{{ $loop->iteration }}</span>
                                <span class="flex-1 truncate">{{ $item->exercise->name }}</span>
                                <span class="shrink-0 text-slate-400">{{ $item->sets }}×{{ $item->reps }}</span>
                            </li>
                        @endforeach
                        @if ($today->items->count() > 4)
                            <li class="pl-9 text-sm text-slate-400">y {{ $today->items->count() - 4 }} más…</li>
                        @endif
                    </ul>
                    @if (Route::has('client.workout'))
                        <x-button :href="route('client.workout')" size="lg" :icon="$todayDone ? 'check-circle' : 'play'" class="mt-6 w-full" :variant="$todayDone ? 'secondary' : 'primary'">
                            {{ $todayDone ? 'Ver entrenamiento' : 'Empezar entrenamiento' }}
                        </x-button>
                    @endif
                </div>
            </div>
        </section>
    @else
        <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70">
            <p class="text-sm font-semibold uppercase tracking-wider text-emerald-700">Día de descanso</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900">Recupera energía 😴</h2>
            <p class="mt-2 text-slate-600">El descanso también es parte del progreso: hidrátate y duerme bien.</p>
            @if ($nextDay)
                <p class="mt-4 rounded-xl bg-slate-50 p-3 text-sm text-slate-600">Próximo entrenamiento: <span class="font-semibold text-slate-900">{{ $nextDay->name }}</span> el {{ mb_strtolower($nextDay->weekday_name) }}</p>
            @endif
        </section>
    @endif

    {{-- Resumen rápido --}}
    <div class="mt-5 grid grid-cols-2 gap-3">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70">
            <p class="flex items-center gap-1.5 text-xs font-medium text-slate-500"><x-icon name="fire" class="size-4 text-brand-600" /> Esta semana</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $weekLogs }}<span class="text-base font-medium text-slate-400">/{{ $weekTarget ?: '–' }}</span></p>
            <p class="text-xs text-slate-500">entrenamientos</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70">
            <p class="flex items-center gap-1.5 text-xs font-medium text-slate-500"><x-icon name="scale" class="size-4 text-brand-600" /> Peso actual</p>
            @if ($latestProgress?->weight_kg)
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ rtrim(rtrim(number_format($latestProgress->weight_kg, 1), '0'), '.') }}<span class="text-base font-medium text-slate-400"> kg</span></p>
                @if ($firstProgress && $firstProgress->id !== $latestProgress->id)
                    @php($diff = round($latestProgress->weight_kg - $firstProgress->weight_kg, 1))
                    <p class="text-xs {{ $diff <= 0 ? 'text-emerald-700' : 'text-slate-500' }}">{{ $diff > 0 ? '+' : '' }}{{ $diff }} kg desde el inicio</p>
                @else
                    <p class="text-xs text-slate-500">{{ $latestProgress->recorded_on->diffForHumans() }}</p>
                @endif
            @else
                <p class="mt-1 text-2xl font-bold text-slate-300">—</p>
                <p class="text-xs text-slate-500">Sin registro</p>
            @endif
        </div>
    </div>

    {{-- Registro rápido de peso --}}
    @if (Route::has('client.progress.store'))
        <form method="POST" action="{{ route('client.progress.store') }}" class="mt-5 flex items-end gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70">
            @csrf
            <input type="hidden" name="quick" value="1">
            <x-input name="weight_kg" type="number" step="0.1" min="20" max="400" inputmode="decimal" label="¿Cuánto pesas hoy?" suffix="kg" :value="$latestProgress?->weight_kg" class="flex-1" />
            <x-button icon="check">Guardar</x-button>
        </form>
    @endif

    {{-- Nutrición del día --}}
    @if ($nutrition)
        @php($totals = $nutrition->totals)
        <a href="{{ Route::has('client.nutrition') ? route('client.nutrition') : '#' }}" class="mt-5 block rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70 transition hover:ring-brand-300">
            <div class="flex items-center justify-between">
                <p class="flex items-center gap-1.5 text-sm font-semibold text-slate-900"><x-icon name="apple" class="size-5 text-brand-600" /> {{ $nutrition->title }}</p>
                <x-icon name="chevron-right" class="size-5 text-slate-400" />
            </div>
            @if ($totals['calories'])
                <div class="mt-3 grid grid-cols-4 gap-2 text-center">
                    @foreach ([['kcal', $totals['calories'], 'text-slate-900'], ['Proteína', $totals['protein_g'].'g', 'text-sky-700'], ['Carbos', $totals['carbs_g'].'g', 'text-amber-700'], ['Grasa', $totals['fats_g'].'g', 'text-rose-700']] as [$label, $value, $color])
                        <div class="rounded-xl bg-slate-50 py-2">
                            <p class="text-base font-bold {{ $color }}">{{ $value }}</p>
                            <p class="text-[11px] text-slate-500">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-2 text-sm text-slate-500">Toca para ver tu plan de comidas.</p>
            @endif
        </a>
    @endif

    @if ($unread && Route::has('client.messages'))
        <a href="{{ route('client.messages') }}" class="mt-5 flex items-center gap-3 rounded-2xl bg-brand-50 p-4 text-brand-900 ring-1 ring-brand-200">
            <x-icon name="chat" class="size-5" />
            <p class="flex-1 text-sm font-medium">Tienes {{ $unread }} {{ str('mensaje')->plural($unread) }} de tu entrenador</p>
            <x-icon name="chevron-right" class="size-5" />
        </a>
    @endif
</x-layouts.client>
