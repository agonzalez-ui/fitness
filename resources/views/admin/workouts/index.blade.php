<x-layouts.admin title="Rutinas">
    <x-page-header title="Rutinas" subtitle="Arma plantillas una vez y asígnalas a tus clientes con un clic.">
        <x-slot:actions>
            <div x-data="{ open: {{ $errors->has('title') ? 'true' : 'false' }} }">
                <x-button type="button" icon="plus" x-on:click="open = true">Nueva plantilla</x-button>
                <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-4 sm:items-center" x-on:keydown.escape.window="open = false">
                    <form method="POST" action="{{ route('admin.workouts.store') }}" x-on:click.outside="open = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                        @csrf
                        <h2 class="text-lg font-semibold text-slate-900">Nueva plantilla de rutina</h2>
                        <x-input name="title" label="Nombre" placeholder="Ej.: Fuerza 4 días" class="mt-4" x-ref="title" />
                        <x-choice-chips name="level" label="Nivel" :options="\App\Enums\Level::options()" value="beginner" class="mt-4" />
                        <div class="mt-6 flex justify-end gap-2">
                            <x-button type="button" variant="ghost" x-on:click="open = false">Cancelar</x-button>
                            <x-button icon="arrow-right">Crear y armar</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </x-slot:actions>
    </x-page-header>

    <nav class="mb-6 flex gap-1" aria-label="Tipo de rutina">
        @foreach (['templates' => ['Plantillas', route('admin.workouts.index')], 'clients' => ['Asignadas a clientes', route('admin.workouts.index', ['ver' => 'clientes'])]] as $key => [$label, $url])
            <a href="{{ $url }}" @if ($tab === $key) aria-current="page" @endif
                @class(['inline-flex min-h-10 items-center rounded-full px-4 text-sm font-medium', 'bg-slate-900 text-white' => $tab === $key, 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $tab !== $key])>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($plans->isEmpty())
        <x-empty-state icon="dumbbell" :title="$tab === 'templates' ? 'Sin plantillas' : 'Ningún cliente tiene rutina todavía'"
            :description="$tab === 'templates' ? 'Crea tu primera plantilla.' : 'Asígnalas desde la ficha de cada cliente.'" class="bg-white" />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($plans as $plan)
                <article class="flex flex-col rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="font-semibold text-slate-900">{{ $plan->title }}</h2>
                        <x-badge :color="$plan->level->color()">{{ $plan->level->label() }}</x-badge>
                    </div>
                    @if ($plan->user)
                        <p class="mt-1 text-sm text-slate-500">Para <a href="{{ route('admin.clients.show', $plan->user) }}" class="font-medium text-brand-700 hover:underline">{{ $plan->user->name }}</a></p>
                    @elseif ($plan->goal)
                        <p class="mt-1 text-sm text-slate-500">{{ $plan->goal->icon() }} {{ $plan->goal->label() }}</p>
                    @endif
                    <p class="mt-3 text-sm text-slate-600">
                        {{ $plan->days->count() }} {{ str('día')->plural($plan->days->count()) }} ·
                        {{ $plan->days->sum(fn ($d) => $d->items->count()) }} ejercicios
                        @if ($plan->file_path) · <x-icon name="document" class="inline size-4" /> archivo @endif
                    </p>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($plan->days as $day)
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $day->name }}</span>
                        @endforeach
                    </div>
                    <div class="mt-auto flex flex-wrap gap-2 border-t border-slate-100 pt-4 [&:not(:first-child)]:mt-4">
                        <x-button :href="route('admin.workouts.edit', $plan)" size="sm" variant="soft" icon="pencil">Editar</x-button>
                        <form method="POST" action="{{ route('admin.workouts.duplicate', $plan) }}">
                            @csrf
                            <x-button size="sm" variant="ghost" icon="copy">{{ $plan->isTemplate() ? 'Duplicar' : 'Guardar como plantilla' }}</x-button>
                        </form>
                        @if ($plan->isTemplate())
                            <form method="POST" action="{{ route('admin.workouts.destroy', $plan) }}" class="ml-auto" onsubmit="return confirm('¿Eliminar la plantilla «{{ $plan->title }}»? Las rutinas ya asignadas no cambian.')">
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
