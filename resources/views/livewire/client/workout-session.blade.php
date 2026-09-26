<div x-data="{
        remaining: null, duration: 1, label: '', interval: null, confetti: false,
        start(seconds, label) {
            this.stop();
            this.duration = seconds; this.remaining = seconds; this.label = label;
            this.interval = setInterval(() => {
                this.remaining--;
                if (this.remaining <= 0) { this.stop(); navigator.vibrate?.([200, 100, 200]); }
            }, 1000);
        },
        add(seconds) { this.remaining += seconds; this.duration += seconds; },
        stop() { clearInterval(this.interval); this.remaining = null; },
        format(s) { return s === null ? '' : Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0'); },
    }" x-on:workout-finished.window="confetti = true; setTimeout(() => confetti = false, 2500)">
    @php
        $plan = $this->plan;
        $day = $this->day;
        $log = $this->log;
        $done = collect($log?->completed_exercise_ids ?? []);
        $total = $day?->items->count() ?? 0;
        $count = $day ? $day->items->filter(fn ($i) => $done->contains($i->id))->count() : 0;
        $finished = (bool) $log?->finished_at;
    @endphp

    @if (! $plan)
        <x-empty-state icon="dumbbell" title="Tu rutina viene en camino" description="Tu entrenador está preparando tu plan." class="bg-white" />
    @else
        <div class="mb-4">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $plan->title }}</h1>
            @if ($plan->description)<p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $plan->description }}</p>@endif
            @if ($plan->file_path)
                <a href="{{ $plan->file_url }}" target="_blank" class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-medium text-brand-700 ring-1 ring-slate-200"><x-icon name="document" class="size-4" /> {{ $plan->file_name }}</a>
            @endif
        </div>

        {{-- Días --}}
        @if ($plan->days->count() > 1)
            <div class="-mx-4 mb-4 flex gap-2 overflow-x-auto px-4 pb-1">
                @foreach ($plan->days as $d)
                    <button type="button" wire:click="selectDay({{ $d->id }})" wire:key="d-{{ $d->id }}"
                        @class(['flex min-h-12 shrink-0 flex-col items-start justify-center rounded-xl px-4 text-left',
                            'bg-slate-900 text-white' => $day?->id === $d->id,
                            'bg-white text-slate-700 ring-1 ring-slate-200' => $day?->id !== $d->id])>
                        <span class="text-sm font-semibold">{{ $d->name }}</span>
                        @if ($d->weekday_name)
                            <span @class(['text-xs', 'text-slate-300' => $day?->id === $d->id, 'text-slate-500' => $day?->id !== $d->id])>{{ $d->weekday === today()->dayOfWeekIso ? 'Hoy' : $d->weekday_name }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif

        @if ($day)
            {{-- Progreso del día --}}
            <div class="sticky top-[3.75rem] z-20 -mx-4 mb-4 bg-slate-50/95 px-4 py-3 backdrop-blur">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-semibold text-slate-900">{{ $finished ? '¡Entrenamiento completado! 🎉' : $count.' de '.$total.' ejercicios' }}</span>
                    <span class="tabular-nums text-slate-500">{{ $total ? round($count / $total * 100) : 0 }}%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $count }}" aria-label="Ejercicios completados">
                    <div class="h-full rounded-full bg-brand-600 transition-all duration-500" style="width: {{ $total ? $count / $total * 100 : 0 }}%"></div>
                </div>
            </div>

            <ol class="space-y-3">
                @foreach ($day->items as $item)
                    @php $checked = $done->contains($item->id); @endphp
                    <li wire:key="it-{{ $item->id }}" x-data="{ open: false }"
                        @class(['rounded-2xl bg-white shadow-sm ring-1 transition', 'ring-emerald-300 bg-emerald-50/40' => $checked, 'ring-slate-200/70' => ! $checked])>
                        <div class="flex items-start gap-3 p-4">
                            <button type="button" wire:click="toggle({{ $item->id }})" @disabled($finished)
                                @class(['mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full ring-2 transition',
                                    'bg-emerald-600 text-white ring-emerald-600' => $checked,
                                    'text-transparent ring-slate-300 hover:ring-brand-500' => ! $checked])
                                role="checkbox" aria-checked="{{ $checked ? 'true' : 'false' }}" aria-label="Marcar {{ $item->exercise->name }} como hecho">
                                <x-icon name="check" class="size-5" />
                            </button>
                            <div class="min-w-0 flex-1">
                                <p @class(['font-semibold', 'text-slate-500 line-through decoration-slate-300' => $checked, 'text-slate-900' => ! $checked])>{{ $item->exercise->name }}</p>
                                <p class="mt-0.5 text-sm text-slate-600">
                                    <span class="font-semibold text-slate-900">{{ $item->sets }} × {{ $item->reps }}</span>
                                    @if ($item->rest_seconds) · descanso {{ rest_label($item->rest_seconds) }}@endif
                                </p>
                                @if ($item->notes)<p class="mt-1 rounded-lg bg-amber-50 px-2 py-1 text-sm text-amber-900">📝 {{ $item->notes }}</p>@endif
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @if ($item->rest_seconds)
                                        <button type="button" x-on:click="start({{ $item->rest_seconds }}, @js($item->exercise->name))" class="inline-flex min-h-9 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-sm font-medium text-slate-700 hover:bg-slate-200">
                                            <x-icon name="clock" class="size-4" /> Descanso
                                        </button>
                                    @endif
                                    @if ($item->exercise->instructions)
                                        <button type="button" x-on:click="open = !open" class="inline-flex min-h-9 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-sm font-medium text-slate-700 hover:bg-slate-200" ::aria-expanded="open">
                                            <x-icon name="book" class="size-4" /> Técnica
                                        </button>
                                    @endif
                                    <a href="{{ $item->exercise->video_link }}" target="_blank" rel="noopener" class="inline-flex min-h-9 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-sm font-medium text-slate-700 hover:bg-slate-200">
                                        <x-icon name="play" class="size-4" /> Video
                                    </a>
                                </div>
                                <p x-cloak x-show="open" x-transition class="mt-3 text-sm leading-relaxed text-slate-600">{{ $item->exercise->instructions }}</p>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>

            {{-- Cierre --}}
            <div class="mt-6 rounded-2xl bg-white p-5 text-center shadow-sm ring-1 ring-slate-200/70">
                @if ($finished)
                    <p class="text-4xl" aria-hidden="true">🏆</p>
                    <p class="mt-2 font-semibold text-slate-900">¡Buen trabajo! Registramos tu entrenamiento de hoy.</p>
                    <button type="button" wire:click="reopen" class="mt-3 text-sm font-medium text-slate-500 underline">Deshacer</button>
                @else
                    <p class="font-semibold text-slate-900">¿Cómo te fue hoy?</p>
                    <p class="text-sm text-slate-500">Toca una carita para terminar el entrenamiento.</p>
                    <div class="mt-4 flex justify-center gap-2">
                        @foreach ([1 => ['😫', 'Muy duro'], 2 => ['😓', 'Duro'], 3 => ['🙂', 'Bien'], 4 => ['😄', 'Muy bien'], 5 => ['🔥', 'Excelente']] as $value => [$emoji, $label])
                            <button type="button" wire:click="finish({{ $value }})" class="flex flex-col items-center gap-1 rounded-xl p-2 hover:bg-slate-100" aria-label="{{ $label }}">
                                <span class="text-3xl" aria-hidden="true">{{ $emoji }}</span>
                                <span class="text-[11px] text-slate-500">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    @endif

    {{-- Cronómetro de descanso --}}
    <div x-cloak x-show="remaining !== null" x-transition class="fixed inset-x-0 bottom-20 z-40 px-4">
        <div class="mx-auto flex max-w-md items-center gap-4 rounded-2xl bg-slate-900 p-4 text-white shadow-2xl">
            <div class="relative size-16 shrink-0">
                <svg viewBox="0 0 36 36" class="size-16 -rotate-90" aria-hidden="true">
                    <circle cx="18" cy="18" r="16" fill="none" class="stroke-slate-700" stroke-width="3" />
                    <circle cx="18" cy="18" r="16" fill="none" class="stroke-brand-500 transition-all duration-1000 ease-linear" stroke-width="3" stroke-linecap="round"
                        stroke-dasharray="100.5" :stroke-dashoffset="100.5 * (1 - remaining / duration)" />
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-lg font-bold tabular-nums" x-text="format(remaining)" role="timer" aria-live="off"></span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs uppercase tracking-wider text-slate-400">Descansando</p>
                <p class="truncate font-medium" x-text="label"></p>
                <div class="mt-1 flex gap-3 text-sm">
                    <button type="button" x-on:click="add(15)" class="font-semibold text-brand-300">+15 s</button>
                    <button type="button" x-on:click="stop()" class="font-semibold text-slate-300">Saltar</button>
                </div>
            </div>
        </div>
    </div>
    <p x-cloak x-show="confetti" x-transition.opacity class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center text-8xl" aria-hidden="true">🎉</p>
</div>

