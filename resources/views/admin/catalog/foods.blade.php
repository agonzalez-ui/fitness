<x-layouts.admin title="Alimentos">
    @include('admin.catalog._tabs')

    <div x-data="{ create: {{ request('nuevo') !== null || $errors->create->any() ? 'true' : 'false' }} }" class="mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" class="flex flex-1 flex-wrap gap-2" role="search">
                <label for="q" class="sr-only">Buscar</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar alimento…" class="min-h-11 min-w-0 flex-1 rounded-xl border-0 px-4 text-sm ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600 sm:max-w-xs">
                <label for="categoria" class="sr-only">Categoría</label>
                <select id="categoria" name="categoria" onchange="this.form.submit()" class="min-h-11 rounded-xl border-0 pl-4 pr-9 text-sm ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600">
                    <option value="">Todas las categorías</option>
                    @foreach (\App\Enums\FoodCategory::options() as $value => $label)
                        <option value="{{ $value }}" @selected(request('categoria') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <x-button type="button" icon="plus" x-on:click="create = !create">Nuevo alimento</x-button>
        </div>

        <form x-cloak x-show="create" x-transition method="POST" action="{{ route('admin.catalog.foods.store') }}" class="mt-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
            @csrf
            <h2 class="mb-4 font-semibold text-slate-900">Nuevo alimento</h2>
            @include('admin.catalog._food-fields', ['bag' => 'create', 'useOld' => true, 'food' => null, 'prefix' => 'new-'])
            <div class="mt-5 flex justify-end gap-2">
                <x-button type="button" variant="ghost" x-on:click="create = false">Cancelar</x-button>
                <x-button icon="check">Agregar al catálogo</x-button>
            </div>
        </form>
    </div>

    <p class="mb-3 text-sm text-slate-500">{{ $foods->count() }} alimentos · valores por porción</p>
    <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
        @forelse ($foods as $food)
            @php $editing = old('_row') == $food->id && $errors->edit->any(); @endphp
            <div x-data="{ edit: {{ $editing ? 'true' : 'false' }} }" @class(['p-4', 'opacity-60' => ! $food->active])>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-slate-900">{{ $food->name }} @unless ($food->active)<x-badge>Oculto</x-badge>@endunless</p>
                        <p class="text-sm text-slate-500">{{ $food->category->label() }} · {{ $food->portion_label }}</p>
                    </div>
                    <p class="text-right text-sm tabular-nums text-slate-600"><span class="font-semibold text-slate-900">{{ $food->calories }} kcal</span><br>P {{ $food->protein_g }} · C {{ $food->carbs_g }} · G {{ $food->fats_g }}</p>
                    <x-button type="button" size="sm" variant="ghost" icon="pencil" x-on:click="edit = !edit">Editar</x-button>
                    <form method="POST" action="{{ route('admin.catalog.foods.toggle', $food) }}">
                        @csrf
                        <x-button size="sm" variant="ghost">{{ $food->active ? 'Ocultar' : 'Mostrar' }}</x-button>
                    </form>
                </div>
                <form x-cloak x-show="edit" x-transition method="POST" action="{{ route('admin.catalog.foods.update', $food) }}" class="mt-4 rounded-xl bg-slate-50 p-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="_row" value="{{ $food->id }}">
                    @include('admin.catalog._food-fields', ['bag' => $editing ? 'edit' : 'none', 'useOld' => $editing, 'food' => $food, 'prefix' => 'food'.$food->id.'-'])
                    <div class="mt-4 flex justify-end gap-2">
                        <x-button type="button" variant="ghost" x-on:click="edit = false">Cancelar</x-button>
                        <x-button icon="check">Guardar</x-button>
                    </div>
                </form>
            </div>
        @empty
            <div class="p-6"><x-empty-state icon="apple" title="Sin resultados" description="Prueba con otra búsqueda o crea el alimento." /></div>
        @endforelse
    </div>
</x-layouts.admin>
