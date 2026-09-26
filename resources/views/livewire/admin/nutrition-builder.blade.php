<div x-data="{ library: false, toast: null, timer: null }"
    x-on:saved.window="toast = $event.detail.message; clearTimeout(timer); timer = setTimeout(() => toast = null, 1800)">
    @php
        $plan = $this->plan;
        $meal = $this->activeMeal;
        $totals = $plan->totals;
        $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
        $servingLabel = fn ($s) => match (true) { abs($s - 0.25) < .01 => '¼', abs($s - 0.5) < .01 => '½', abs($s - 0.75) < .01 => '¾', default => $fmt($s) };
    @endphp

    <div x-cloak x-show="toast" x-transition.opacity role="status"
        class="fixed bottom-6 left-1/2 z-50 flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-lg lg:left-[calc(50%+9rem)]">
        <x-icon name="check" class="size-4 text-emerald-400" /> <span x-text="toast"></span>
    </div>

    {{-- Encabezado con totales del día --}}
    <div class="mb-6 grid gap-4 lg:grid-cols-[1fr_22rem]">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-500">
                @if ($plan->user)
                    Plan de <a href="{{ route('admin.clients.show', $plan->user) }}" class="text-brand-700 hover:underline">{{ $plan->user->name }}</a>
                @else
                    Plantilla
                @endif
            </p>
            <label for="title" class="sr-only">Nombre del plan</label>
            <input id="title" wire:model.blur="title" class="-mx-2 w-full rounded-lg border-0 bg-transparent px-2 py-1 text-2xl font-bold text-slate-900 hover:bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-600">
            <x-input-error name="title" />

            <p class="mb-2 mt-4 text-xs font-medium text-slate-500">Objetivo</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach ($goals as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="goal" value="{{ $value }}" class="peer sr-only">
                        <span class="inline-flex min-h-9 items-center rounded-full px-3 text-sm font-medium ring-1 ring-slate-300 peer-checked:bg-slate-900 peer-checked:text-white peer-checked:ring-slate-900 peer-focus-visible:outline-2 peer-focus-visible:outline-brand-600">{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <details class="mt-4" @if ($notes || $plan->file_path) open @endif>
                <summary class="cursor-pointer select-none text-sm font-medium text-slate-600">Indicaciones y archivo adjunto</summary>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <textarea wire:model.blur="notes" rows="4" placeholder="Ej.: tomar 2-3 litros de agua, un día libre por semana…"
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

        <div class="rounded-2xl bg-slate-900 p-5 text-white shadow-sm">
            <p class="text-sm text-slate-300">Total del día</p>
            <p class="text-4xl font-extrabold tabular-nums">{{ number_format($totals['calories']) }} <span class="text-lg font-medium text-slate-400">kcal</span></p>
            <div class="mt-4 rounded-xl bg-white p-3">
                <x-macro-bar :totals="$totals" size="sm" />
            </div>
            <p class="mt-3 text-xs text-slate-400">Se calcula solo a partir de los alimentos y porciones.</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
        <div class="min-w-0">
            {{-- Pestañas de comidas --}}
            <div class="mb-4 flex items-center gap-2 overflow-x-auto pb-1" wire:sort="sortMeals">
                @foreach ($plan->meals as $m)
                    @php $mealKcal = (int) round($m->items->sum(fn ($i) => $i->food->calories * $i->servings)); @endphp
                    <button type="button" wire:key="meal-tab-{{ $m->id }}" wire:sort:item="{{ $m->id }}" wire:click="selectMeal({{ $m->id }})"
                        @class(['flex min-h-12 shrink-0 flex-col items-start justify-center rounded-xl px-4 text-left transition',
                            'bg-slate-900 text-white shadow' => $meal?->id === $m->id,
                            'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50' => $meal?->id !== $m->id])>
                        <span class="text-sm font-semibold">{{ $m->name }}</span>
                        <span @class(['text-xs tabular-nums', 'text-slate-300' => $meal?->id === $m->id, 'text-slate-500' => $meal?->id !== $m->id])>{{ $m->time ? substr($m->time, 0, 5).' · ' : '' }}{{ $mealKcal }} kcal</span>
                    </button>
                @endforeach
                <button type="button" wire:click="addMeal" wire:sort:ignore class="inline-flex min-h-12 shrink-0 items-center gap-1.5 rounded-xl px-4 text-sm font-semibold text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50">
                    <x-icon name="plus" class="size-4" /> Comida
                </button>
            </div>

            @if (! $meal)
                <x-empty-state icon="apple" title="Agrega las comidas del día" description="Empieza con desayuno, almuerzo y cena; luego toca alimentos del catálogo." class="bg-white">
                    @foreach (array_keys(\App\Models\NutritionMeal::SUGGESTED) as $suggested)
                        <x-button type="button" size="sm" variant="secondary" wire:click="addMeal('{{ $suggested }}')">+ {{ $suggested }}</x-button>
                    @endforeach
                </x-empty-state>
            @else
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70" wire:key="meal-panel-{{ $meal->id }}">
                    <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
                        <label for="meal-name" class="sr-only">Nombre de la comida</label>
                        <input id="meal-name" value="{{ $meal->name }}" wire:change="updateMeal({{ $meal->id }}, 'name', $event.target.value)"
                            class="min-w-0 flex-1 rounded-lg border-0 px-2 py-1 text-lg font-semibold ring-1 ring-transparent hover:ring-slate-200 focus:ring-2 focus:ring-brand-600">
                        <label for="meal-time" class="sr-only">Hora</label>
                        <input id="meal-time" type="time" value="{{ $meal->time ? substr($meal->time, 0, 5) : '' }}" wire:change="updateMeal({{ $meal->id }}, 'time', $event.target.value)"
                            class="min-h-10 rounded-lg border-0 px-3 text-sm ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600">
                        <button type="button" wire:click="removeMeal({{ $meal->id }})" wire:confirm="¿Eliminar {{ $meal->name }} y sus alimentos?" class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-700" aria-label="Eliminar comida"><x-icon name="trash" class="size-5" /></button>
                    </div>

                    @if ($meal->items->isEmpty())
                        <div class="p-8 text-center text-sm text-slate-500">
                            <p>Toca <span class="font-semibold text-brand-700">+</span> en un alimento del catálogo para agregarlo.</p>
                            <x-button type="button" variant="soft" icon="plus" class="mt-4 lg:hidden" x-on:click="library = true">Agregar alimento</x-button>
                        </div>
                    @else
                        <ul class="divide-y divide-slate-100" wire:sort="sortItems">
                            @foreach ($meal->items as $item)
                                @php $food = $item->food; @endphp
                                <li wire:key="food-item-{{ $item->id }}" wire:sort:item="{{ $item->id }}" class="flex items-center gap-3 bg-white px-4 py-3">
                                    <button type="button" wire:sort:handle class="cursor-grab touch-none rounded p-1 text-slate-300 hover:text-slate-500" aria-label="Arrastrar para reordenar">
                                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><circle cx="7" cy="5" r="1.5"/><circle cx="13" cy="5" r="1.5"/><circle cx="7" cy="10" r="1.5"/><circle cx="13" cy="10" r="1.5"/><circle cx="7" cy="15" r="1.5"/><circle cx="13" cy="15" r="1.5"/></svg>
                                    </button>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-medium text-slate-900">{{ $food->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $servingLabel($item->servings) }} × {{ $food->portion_label }} · {{ (int) round($food->calories * $item->servings) }} kcal · P {{ $fmt(round($food->protein_g * $item->servings, 1)) }} g</p>
                                    </div>
                                    <div class="flex items-center rounded-lg ring-1 ring-slate-300">
                                        <button type="button" wire:click="stepServings({{ $item->id }}, -1)" class="flex size-9 items-center justify-center rounded-l-lg text-lg text-slate-600 hover:bg-slate-100" aria-label="Menos cantidad">−</button>
                                        <span class="w-9 text-center text-sm font-semibold tabular-nums">{{ $servingLabel($item->servings) }}</span>
                                        <button type="button" wire:click="stepServings({{ $item->id }}, 1)" class="flex size-9 items-center justify-center rounded-r-lg text-lg text-slate-600 hover:bg-slate-100" aria-label="Más cantidad">+</button>
                                    </div>
                                    <button type="button" wire:click="removeItem({{ $item->id }})" class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-700" aria-label="Quitar {{ $food->name }}"><x-icon name="x" class="size-4" /></button>
                                </li>
                            @endforeach
                        </ul>
                        <div class="border-t border-slate-100 p-3 lg:hidden">
                            <x-button type="button" variant="soft" icon="plus" class="w-full" x-on:click="library = true">Agregar alimento</x-button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Catálogo de alimentos --}}
        <div x-cloak x-show="library" x-transition.opacity x-on:click="library = false" class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"></div>
        <aside :class="library ? 'translate-y-0' : 'translate-y-full lg:translate-y-0'"
            class="fixed inset-x-0 bottom-0 z-50 flex max-h-[80vh] flex-col rounded-t-3xl bg-white shadow-2xl transition-transform lg:sticky lg:top-6 lg:z-auto lg:max-h-[calc(100vh-3rem)] lg:rounded-2xl lg:shadow-sm lg:ring-1 lg:ring-slate-200/70">
            <div class="border-b border-slate-100 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <p class="font-semibold text-slate-900">Catálogo de alimentos</p>
                    <button type="button" x-on:click="library = false" class="rounded-lg p-1 text-slate-400 lg:hidden" aria-label="Cerrar"><x-icon name="x" /></button>
                </div>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <label for="food-search" class="sr-only">Buscar alimento</label>
                    <input id="food-search" type="search" wire:model.live.debounce.250ms="search" placeholder="Buscar alimento…"
                        class="block min-h-10 w-full rounded-xl border-0 pl-9 pr-3 text-sm ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600">
                </div>
                <div class="-mx-1 mt-3 flex gap-1.5 overflow-x-auto px-1 pb-1">
                    <button type="button" wire:click="$set('category', null)" @class(['shrink-0 rounded-full px-3 py-1.5 text-xs font-medium', 'bg-slate-900 text-white' => ! $category, 'bg-slate-100 text-slate-600' => $category])>Todos</button>
                    @foreach ($categories as $value => $label)
                        <button type="button" wire:click="$set('category', '{{ $value }}')" @class(['shrink-0 rounded-full px-3 py-1.5 text-xs font-medium', 'bg-slate-900 text-white' => $category === $value, 'bg-slate-100 text-slate-600 hover:bg-slate-200' => $category !== $value])>{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <ul class="flex-1 divide-y divide-slate-100 overflow-y-auto">
                @forelse ($this->library as $food)
                    <li wire:key="lib-food-{{ $food->id }}" class="flex items-center gap-3 px-4 py-2.5">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $food->name }}</p>
                            <p class="text-xs text-slate-500">{{ $food->portion_label }} · {{ $food->calories }} kcal · P {{ $fmt($food->protein_g) }} g</p>
                        </div>
                        <button type="button" wire:click="addFood({{ $food->id }})" wire:loading.attr="disabled"
                            class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700 hover:bg-brand-100" aria-label="Agregar {{ $food->name }}">
                            <x-icon name="plus" class="size-5" />
                        </button>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-slate-500">
                        No encontramos «{{ $search }}».
                        @if (Route::has('admin.catalog.foods'))
                            <a href="{{ route('admin.catalog.foods', ['nuevo' => $search]) }}" class="mt-1 block font-semibold text-brand-700">Crear este alimento</a>
                        @endif
                    </li>
                @endforelse
            </ul>
        </aside>
    </div>
</div>
