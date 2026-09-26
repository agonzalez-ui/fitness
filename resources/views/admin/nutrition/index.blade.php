<x-layouts.admin title="Nutrición">
    <x-page-header title="Planes de alimentación" subtitle="Arma el plan eligiendo alimentos: calorías y macros se calculan solos.">
        <x-slot:actions>
            <div x-data="{ open: {{ $errors->has('title') ? 'true' : 'false' }} }">
                <x-button type="button" icon="plus" x-on:click="open = true">Nueva plantilla</x-button>
                <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-4 sm:items-center" x-on:keydown.escape.window="open = false">
                    <form method="POST" action="{{ route('admin.nutrition.store') }}" x-on:click.outside="open = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                        @csrf
                        <h2 class="text-lg font-semibold text-slate-900">Nueva plantilla de alimentación</h2>
                        <x-input name="title" label="Nombre" placeholder="Ej.: Déficit 1800 kcal" class="mt-4" />
                        <div class="mt-6 flex justify-end gap-2">
                            <x-button type="button" variant="ghost" x-on:click="open = false">Cancelar</x-button>
                            <x-button icon="arrow-right">Crear y armar</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </x-slot:actions>
    </x-page-header>

    <nav class="mb-6 flex gap-1" aria-label="Tipo de plan">
        @foreach (['templates' => ['Plantillas', route('admin.nutrition.index')], 'clients' => ['Asignados a clientes', route('admin.nutrition.index', ['ver' => 'clientes'])]] as $key => [$label, $url])
            <a href="{{ $url }}" @if ($tab === $key) aria-current="page" @endif
                @class(['inline-flex min-h-10 items-center rounded-full px-4 text-sm font-medium', 'bg-slate-900 text-white' => $tab === $key, 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $tab !== $key])>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($plans->isEmpty())
        <x-empty-state icon="apple" :title="$tab === 'templates' ? 'Sin plantillas' : 'Ningún cliente tiene plan todavía'"
            :description="$tab === 'templates' ? 'Crea tu primera plantilla.' : 'Asígnalos desde la ficha de cada cliente.'" class="bg-white" />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($plans as $plan)
                @php $totals = $plan->totals; @endphp
                <article class="flex flex-col rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                    <h2 class="font-semibold text-slate-900">{{ $plan->title }}</h2>
                    @if ($plan->user)
                        <p class="mt-1 text-sm text-slate-500">Para <a href="{{ route('admin.clients.show', $plan->user) }}" class="font-medium text-brand-700 hover:underline">{{ $plan->user->name }}</a></p>
                    @elseif ($plan->goal)
                        <p class="mt-1 text-sm text-slate-500">{{ $plan->goal->icon() }} {{ $plan->goal->label() }}</p>
                    @endif
                    @if ($totals['calories'])
                        <p class="mt-3 text-2xl font-bold tabular-nums text-slate-900">{{ number_format($totals['calories']) }} <span class="text-sm font-medium text-slate-500">kcal · {{ $plan->meals->count() }} comidas</span></p>
                        <x-macro-bar :totals="$totals" size="sm" class="mt-3" />
                    @elseif ($plan->file_path)
                        <p class="mt-3 flex items-center gap-1.5 text-sm text-slate-600"><x-icon name="document" class="size-4" /> {{ $plan->file_name }}</p>
                    @else
                        <p class="mt-3 text-sm text-slate-500">Sin alimentos todavía.</p>
                    @endif
                    <div class="mt-auto flex flex-wrap gap-2 border-t border-slate-100 pt-4 [&:not(:first-child)]:mt-4">
                        <x-button :href="route('admin.nutrition.edit', $plan)" size="sm" variant="soft" icon="pencil">Editar</x-button>
                        <form method="POST" action="{{ route('admin.nutrition.duplicate', $plan) }}">
                            @csrf
                            <x-button size="sm" variant="ghost" icon="copy">{{ $plan->isTemplate() ? 'Duplicar' : 'Guardar como plantilla' }}</x-button>
                        </form>
                        @if ($plan->isTemplate())
                            <form method="POST" action="{{ route('admin.nutrition.destroy', $plan) }}" class="ml-auto" onsubmit="return confirm('¿Eliminar la plantilla «{{ $plan->title }}»?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-700" aria-label="Eliminar plantilla"><x-icon name="trash" class="size-4" /></button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
