<div x-data="{ library: false, toast: null, timer: null }"
    x-on:saved.window="toast = $event.detail.message; clearTimeout(timer); timer = setTimeout(() => toast = null, 1800)">
    @php $plan = $this->plan; @endphp
    @php $day = $this->activeDay; @endphp

    {{-- Aviso de guardado automático --}}
    <div x-cloak x-show="toast" x-transition.opacity role="status"
        class="fixed bottom-6 left-1/2 z-50 flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-lg lg:left-[calc(50%+9rem)]">
        <x-icon name="check" class="size-4 text-emerald-400" /> <span x-text="toast"></span>
    </div>

    {{-- Encabezado del plan --}}
    <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <div class="flex flex-wrap items-start gap-4">
            <div class="min-w-0 flex-1">
                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    @if ($plan->user)
                        Rutina de <a href="{{ route('admin.clients.show', $plan->user) }}" class="text-brand-700 hover:underline">{{ $plan->user->name }}</a>
                    @else
                        Plantilla
                    @endif
                </p>
                <label for="title" class="sr-only">Nombre de la rutina</label>
                <input id="title" wire:model.blur="title" class="-mx-2 w-full rounded-lg border-0 bg-transparent px-2 py-1 text-2xl font-bold text-slate-900 hover:bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-600">
                <x-input-error name="title" />
            </div>
            <div wire:loading.delay class="text-sm text-slate-400">Guardando…</div>
        </div>

        <div class="mt-4 flex flex-wrap gap-x-8 gap-y-4">
            <div>
                <p class="mb-2 text-xs font-medium text-slate-500">Nivel</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($levels as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="level" value="{{ $value }}" class="peer sr-only">
                            <span class="inline-flex min-h-9 items-center rounded-full px-3 text-sm font-medium ring-1 ring-slate-300 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:ring-slate-900 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-600">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="min-w-0">
                <p class="mb-2 text-xs font-medium text-slate-500">Objetivo</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($goals as $value => $label)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="goal" value="{{ $value }}" class="peer sr-only">
                            <span class="inline-flex min-h-9 items-center rounded-full px-3 text-sm font-medium ring-1 ring-slate-300 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:ring-slate-900 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-600">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <details class="mt-4" @if ($description || $plan->file_path) open @endif>
            <summary class="cursor-pointer select-none text-sm font-medium text-slate-600">Descripción y archivo adjunto</summary>
            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                <textarea wire:model.blur="description" rows="3" placeholder="Indicaciones generales para el cliente (opcional)"
                    class="block w-full rounded-xl border-0 px-4 py-3 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-brand-600"></textarea>
                <div class="rounded-xl border-2 border-dashed border-slate-200 p-4 text-sm">
                    @if ($plan->file_path)
                        <div class="flex items-center gap-2">
                            <x-icon name="document" class="size-5 text-brand-700" />
                            <a href="{{ $plan->file_url }}" target="_blank" class="flex-1 truncate font-medium text-brand-700 hover:underline">{{ $plan->file_name }}</a>
                            <button type="button" wire:click="removeFile" wire:confirm="¿Quitar el archivo adjunto?" class="rounded-lg p-1 text-slate-400 hover:text-red-700" aria-label="Quitar archivo"><x-icon name="trash" class="size-4" /></button>
                        </div>
                    @else
                        <label class="flex cursor-pointer flex-col items-center gap-1 text-center text-slate-500">
                            <x-icon name="document" class="size-6" />
                            <span><span class="font-semibold text-brand-700">Adjuntar PDF o imagen</span> (opcional)</span>
                            <input type="file" wire:model="file" class="sr-only" accept=".pdf,.doc,.docx,image/*">
                        </label>
                        <div wire:loading wire:target="file" class="mt-2 text-center text-slate-400">Subiendo…</div>
                    @endif
                    <x-input-error name="file" />
                </div>
            </div>
        </details>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
        {{-- Días y ejercicios --}}
        <div class="min-w-0">
            <div class="mb-4 flex items-center gap-2 overflow-x-auto pb-1" wire:sort="sortDays">
                @foreach ($plan->days as $d)
                    <button type="button" wire:key="day-tab-{{ $d->id }}" wire:sort:item="{{ $d->id }}" wire:click="selectDay({{ $d->id }})"
                        @class(['flex min-h-12 shrink-0 flex-col items-start justify-center rounded-xl px-4 text-left transition',
                            'bg-slate-900 text-white shadow' => $day?->id === $d->id,
                            'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' => $day?->id !== $d->id])>
                        <span class="text-sm font-semibold">{{ $d->name }}</span>
                        <span @class(['text-xs', 'text-slate-300' => $day?->id === $d->id, 'text-slate-500' => $day?->id !== $d->id])>
                            {{ $d->weekday_name ?? 'Sin día fijo' }} · {{ $d->items->count() }}
                        </span>
                    </button>
                @endforeach
                <button type="button" wire:click="addDay" wire:sort:ignore class="inline-flex min-h-12 shrink-0 items-center gap-1.5 rounded-xl px-4 text-sm font-semibold text-brand-700 ring-1 ring-dashed ring-brand-300 hover:bg-brand-50">
                    <x-icon name="plus" class="size-4" /> Día
                </button>
            </div>

            @if (! $day)
                <x-empty-state icon="calendar" title="Agrega el primer día" description="Crea los días de entrenamiento y luego toca ejercicios del catálogo para agregarlos." class="bg-white">
                    <x-button type="button" wire:click="addDay" icon="plus">Agregar día</x-button>
                </x-empty-state>
            @else
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70" wire:key="day-panel-{{ $day->id }}">
                    <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
                        <label for="day-name" class="sr-only">Nombre del día</label>
                        <input id="day-name" value="{{ $day->name }}" wire:change="renameDay({{ $day->id }}, $event.target.value)"
                            class="min-w-0 flex-1 rounded-lg border-0 px-2 py-1 text-lg font-semibold ring-1 ring-transparent hover:ring-slate-200 focus:ring-2 focus:ring-brand-600">
                        <label for="day-weekday" class="sr-only">Día de la semana</label>
                        <select id="day-weekday" wire:change="setWeekday({{ $day->id }}, $event.target.value)" class="min-h-10 rounded-lg border-0 py-1 pl-3 pr-8 text-sm ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600">
                            <option value="" @selected(! $day->weekday)>Sin día fijo</option>
                            @foreach (\App\Models\WorkoutDay::WEEKDAYS as $n => $name)
                                <option value="{{ $n }}" @selected($day->weekday === $n)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="flex gap-1">
                            <button type="button" wire:click="duplicateDay({{ $day->id }})" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900" title="Duplicar día" aria-label="Duplicar día"><x-icon name="copy" class="size-5" /></button>
                            <button type="button" wire:click="removeDay({{ $day->id }})" wire:confirm="¿Eliminar {{ $day->name }} y sus ejercicios?" class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-700" title="Eliminar día" aria-label="Eliminar día"><x-icon name="trash" class="size-5" /></button>
                        </div>
                    </div>

                    @if ($day->items->isEmpty())
                        <div class="p-8 text-center text-sm text-slate-500">
                            <p>Toca <span class="font-semibold text-brand-700">+</span> en un ejercicio del catálogo para agregarlo a este día.</p>
                            <x-button type="button" variant="soft" icon="plus" class="mt-4 lg:hidden" x-on:click="library = true">Agregar ejercicio</x-button>
                        </div>
                    @else
                        <ol class="divide-y divide-slate-100" wire:sort="sortItems">
                            @foreach ($day->items as $item)
                                <li wire:key="item-{{ $item->id }}" wire:sort:item="{{ $item->id }}" class="flex gap-3 bg-white p-4">
                                    <button type="button" wire:sort:handle class="mt-1 cursor-grab touch-none self-start rounded p-1 text-slate-300 hover:text-slate-500 active:cursor-grabbing" aria-label="Arrastrar para reordenar">
                                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><circle cx="7" cy="5" r="1.5"/><circle cx="13" cy="5" r="1.5"/><circle cx="7" cy="10" r="1.5"/><circle cx="13" cy="10" r="1.5"/><circle cx="7" cy="15" r="1.5"/><circle cx="13" cy="15" r="1.5"/></svg>
                                    </button>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start gap-2">
                                            <p class="flex-1 font-semibold text-slate-900"><span class="mr-1 text-slate-400">{{ $loop->iteration }}.</span> {{ $item->exercise->name }}</p>
                                            <button type="button" wire:click="removeItem({{ $item->id }})" class="-mr-1 -mt-1 rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-700" aria-label="Quitar {{ $item->exercise->name }}"><x-icon name="x" class="size-4" /></button>
                                        </div>
                                        <p class="text-xs text-slate-500">{{ $item->exercise->muscle_group->label() }} · {{ $item->exercise->equipment->label() }}</p>

                                        <div class="mt-3 flex flex-wrap items-end gap-3">
                                            <div>
                                                <p class="mb-1 text-xs font-medium text-slate-500">Series</p>
                                                <div class="flex items-center rounded-lg ring-1 ring-slate-300">
                                                    <button type="button" wire:click="stepSets({{ $item->id }}, -1)" class="flex size-9 items-center justify-center rounded-l-lg text-lg text-slate-600 hover:bg-slate-100" aria-label="Menos series">−</button>
                                                    <span class="w-8 text-center font-semibold tabular-nums">{{ $item->sets }}</span>
                                                    <button type="button" wire:click="stepSets({{ $item->id }}, 1)" class="flex size-9 items-center justify-center rounded-r-lg text-lg text-slate-600 hover:bg-slate-100" aria-label="Más series">+</button>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="reps-{{ $item->id }}" class="mb-1 block text-xs font-medium text-slate-500">Reps</label>
                                                <input id="reps-{{ $item->id }}" value="{{ $item->reps }}" list="reps-presets" wire:change="updateItem({{ $item->id }}, 'reps', $event.target.value)"
                                                    class="h-9 w-24 rounded-lg border-0 px-3 text-sm font-semibold ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600">
                                            </div>
                                            <div>
                                                <label for="rest-{{ $item->id }}" class="mb-1 block text-xs font-medium text-slate-500">Descanso</label>
                                                <select id="rest-{{ $item->id }}" wire:change="updateItem({{ $item->id }}, 'rest_seconds', $event.target.value)"
                                                    class="h-9 rounded-lg border-0 py-0 pl-3 pr-8 text-sm font-semibold ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600">
                                                    @foreach (collect([0, 30, 45, 60, 75, 90, 120, 180])->push($item->rest_seconds)->unique()->sort() as $seconds)
                                                        <option value="{{ $seconds }}" @selected($item->rest_seconds === $seconds)>{{ rest_label($seconds) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="min-w-40 flex-1">
                                                <label for="notes-{{ $item->id }}" class="mb-1 block text-xs font-medium text-slate-500">Nota (opcional)</label>
                                                <input id="notes-{{ $item->id }}" value="{{ $item->notes }}" placeholder="Ej.: subir peso cada semana" wire:change="updateItem({{ $item->id }}, 'notes', $event.target.value)"
                                                    class="h-9 w-full rounded-lg border-0 px-3 text-sm ring-1 ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-600">
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                        <div class="border-t border-slate-100 p-3 lg:hidden">
                            <x-button type="button" variant="soft" icon="plus" class="w-full" x-on:click="library = true">Agregar ejercicio</x-button>
                        </div>
                    @endif
                </div>
            @endif
            <datalist id="reps-presets">
                @foreach (['5', '6-8', '8-10', '10-12', '12-15', '15-20', '20', 'Al fallo', '30 s', '45 s', '1 min'] as $preset)<option value="{{ $preset }}"></option>@endforeach
            </datalist>
        </div>

        {{-- Catálogo de ejercicios: panel lateral en escritorio, hoja deslizable en móvil --}}
        <div x-cloak x-show="library" x-transition.opacity x-on:click="library = false" class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"></div>
        <aside :class="library ? 'translate-y-0' : 'translate-y-full lg:translate-y-0'"
            class="fixed inset-x-0 bottom-0 z-50 flex max-h-[80vh] flex-col rounded-t-3xl bg-white shadow-2xl transition-transform lg:sticky lg:top-6 lg:z-auto lg:max-h-[calc(100vh-3rem)] lg:rounded-2xl lg:shadow-sm lg:ring-1 lg:ring-slate-200/70">
            <div class="border-b border-slate-100 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <p class="font-semibold text-slate-900">Catálogo de ejercicios</p>
                    <button type="button" x-on:click="library = false" class="rounded-lg p-1 text-slate-400 lg:hidden" aria-label="Cerrar"><x-icon name="x" /></button>
                </div>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <label for="exercise-search" class="sr-only">Buscar ejercicio</label>
                    <input id="exercise-search" type="search" wire:model.live.debounce.250ms="search" placeholder="Buscar ejercicio…"
                        class="block min-h-10 w-full rounded-xl border-0 pl-9 pr-3 text-sm ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600">
                </div>
                <div class="-mx-1 mt-3 flex gap-1.5 overflow-x-auto px-1 pb-1">
                    <button type="button" wire:click="$set('muscle', null)" @class(['shrink-0 rounded-full px-3 py-1.5 text-xs font-medium', 'bg-slate-900 text-white' => ! $muscle, 'bg-slate-100 text-slate-600' => $muscle])>Todos</button>
                    @foreach ($muscles as $value => $label)
                        <button type="button" wire:click="$set('muscle', '{{ $value }}')" @class(['shrink-0 rounded-full px-3 py-1.5 text-xs font-medium', 'bg-slate-900 text-white' => $muscle === $value, 'bg-slate-100 text-slate-600 hover:bg-slate-200' => $muscle !== $value])>{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <ul class="flex-1 divide-y divide-slate-100 overflow-y-auto">
                @php $inDay = $day?->items->pluck('exercise_id')->all() ?? []; @endphp
                @forelse ($this->library as $exercise)
                    <li wire:key="lib-{{ $exercise->id }}" class="flex items-center gap-3 px-4 py-2.5">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $exercise->name }}</p>
                            <p class="text-xs text-slate-500">{{ $exercise->muscle_group->label() }} · {{ $exercise->default_sets }}×{{ $exercise->default_reps }}</p>
                        </div>
                        @if (in_array($exercise->id, $inDay, true))
                            <span class="text-emerald-600" title="Ya está en este día"><x-icon name="check" class="size-5" /></span>
                        @endif
                        <button type="button" wire:click="addExercise({{ $exercise->id }})" wire:loading.attr="disabled"
                            class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700 hover:bg-brand-100" aria-label="Agregar {{ $exercise->name }}">
                            <x-icon name="plus" class="size-5" />
                        </button>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-slate-500">
                        No encontramos «{{ $search }}».
                        @if (Route::has('admin.catalog.exercises'))
                            <a href="{{ route('admin.catalog.exercises', ['nuevo' => $search]) }}" class="mt-1 block font-semibold text-brand-700">Crear este ejercicio</a>
                        @endif
                    </li>
                @endforelse
            </ul>
        </aside>
    </div>
</div>
