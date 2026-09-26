<x-layouts.admin title="Ejercicios">
    @include('admin.catalog._tabs')

    <div x-data="{ create: {{ request('nuevo') !== null || $errors->create->any() ? 'true' : 'false' }} }" class="mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" class="flex flex-1 flex-wrap gap-2" role="search">
                <label for="q" class="sr-only">Buscar</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar ejercicio…" class="min-h-11 min-w-0 flex-1 rounded-xl border-0 px-4 text-sm ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600 sm:max-w-xs">
                <label for="grupo" class="sr-only">Grupo muscular</label>
                <select id="grupo" name="grupo" onchange="this.form.submit()" class="min-h-11 rounded-xl border-0 pl-4 pr-9 text-sm ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600">
                    <option value="">Todos los grupos</option>
                    @foreach (\App\Enums\MuscleGroup::options() as $value => $label)
                        <option value="{{ $value }}" @selected(request('grupo') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <x-button type="button" icon="plus" x-on:click="create = !create">Nuevo ejercicio</x-button>
        </div>

        <form x-cloak x-show="create" x-transition method="POST" action="{{ route('admin.catalog.exercises.store') }}" class="mt-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
            @csrf
            <h2 class="mb-4 font-semibold text-slate-900">Nuevo ejercicio</h2>
            @include('admin.catalog._exercise-fields', ['bag' => 'create', 'useOld' => true, 'exercise' => null, 'prefix' => 'new-'])
            <div class="mt-5 flex justify-end gap-2">
                <x-button type="button" variant="ghost" x-on:click="create = false">Cancelar</x-button>
                <x-button icon="check">Agregar al catálogo</x-button>
            </div>
        </form>
    </div>

    <p class="mb-3 text-sm text-slate-500">{{ $exercises->count() }} ejercicios</p>
    <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
        @forelse ($exercises as $exercise)
            @php($editing = old('_row') == $exercise->id && $errors->edit->any())
            <div x-data="{ edit: {{ $editing ? 'true' : 'false' }} }" @class(['p-4', 'opacity-60' => ! $exercise->active])>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-slate-900">{{ $exercise->name }} @unless ($exercise->active)<x-badge>Oculto</x-badge>@endunless</p>
                        <p class="text-sm text-slate-500">{{ $exercise->muscle_group->label() }} · {{ $exercise->equipment->label() }} · {{ $exercise->default_sets }}×{{ $exercise->default_reps }} · {{ $exercise->default_rest_seconds }} s</p>
                    </div>
                    <a href="{{ $exercise->video_link }}" target="_blank" rel="noopener" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Ver video" aria-label="Ver video de {{ $exercise->name }}"><x-icon name="play" class="size-4" /></a>
                    <x-button type="button" size="sm" variant="ghost" icon="pencil" x-on:click="edit = !edit">Editar</x-button>
                    <form method="POST" action="{{ route('admin.catalog.exercises.toggle', $exercise) }}">
                        @csrf
                        <x-button size="sm" variant="ghost">{{ $exercise->active ? 'Ocultar' : 'Mostrar' }}</x-button>
                    </form>
                </div>
                <form x-cloak x-show="edit" x-transition method="POST" action="{{ route('admin.catalog.exercises.update', $exercise) }}" class="mt-4 rounded-xl bg-slate-50 p-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="_row" value="{{ $exercise->id }}">
                    @include('admin.catalog._exercise-fields', ['bag' => $editing ? 'edit' : 'none', 'useOld' => $editing, 'exercise' => $exercise, 'prefix' => 'ex'.$exercise->id.'-'])
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button type="button" variant="ghost" x-on:click="edit = false">Cancelar</x-button>
                        <x-button icon="check">Guardar</x-button>
                    </div>
                </form>
            </div>
        @empty
            <div class="p-6"><x-empty-state icon="dumbbell" title="Sin resultados" description="Prueba con otra búsqueda o crea el ejercicio." /></div>
        @endforelse
    </div>
    <datalist id="reps-presets">
        @foreach (['5', '6-8', '8-10', '10-12', '12-15', '15-20', '30 s', '45 s', '1 min', '20 min'] as $preset)<option value="{{ $preset }}"></option>@endforeach
    </datalist>
</x-layouts.admin>
