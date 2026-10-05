<label for="estado" class="sr-only">Estado</label>
<select id="estado" name="estado" class="min-h-11 rounded-xl border-0 pl-4 pr-9 text-sm ring-1 ring-slate-300 focus:ring-2 focus:ring-brand-600">
    <option value="">Todos los estados</option>
    <option value="active" @selected(request('estado') === 'active')>Habilitados</option>
    <option value="inactive" @selected(request('estado') === 'inactive')>Deshabilitados</option>
</select>
<x-button size="sm" variant="secondary" icon="search">Buscar</x-button>
@if (request()->filled('q') || request()->filled('estado') || request()->filled('grupo') || request()->filled('categoria'))
    <x-button :href="route(request()->route()->getName())" size="sm" variant="ghost">Limpiar</x-button>
@endif
