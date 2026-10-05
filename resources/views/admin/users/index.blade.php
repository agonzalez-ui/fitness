<x-layouts.admin title="Usuarios">
    <x-page-header title="Usuarios" subtitle="Administra las cuentas y su acceso. Deshabilitar conserva su historial." :back="route('admin.catalog.index')">
        <x-slot:actions><x-button :href="route('admin.users.create')" icon="plus">Nuevo usuario</x-button></x-slot:actions>
    </x-page-header>
    @if ($errors->any())
        <p role="alert" class="mb-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</p>
    @endif
    <form method="GET" role="search" class="mb-6 grid items-end gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <x-input name="q" type="search" label="Buscar usuario" :value="request('q')" :use-old="false" placeholder="Nombre o correo" />
        <x-select name="rol" label="Rol" :options="['admin' => 'Entrenador', 'client' => 'Cliente']" placeholder="Todos los roles" :value="request('rol')" :use-old="false" />
        <x-select name="estado" label="Estado" :options="['active' => 'Habilitados', 'inactive' => 'Deshabilitados']" placeholder="Todos los estados" :value="request('estado')" :use-old="false" />
        <div class="flex gap-2"><x-button icon="search">Buscar</x-button><x-button :href="route('admin.users.index')" variant="ghost">Limpiar</x-button></div>
    </form>
    <p class="mb-3 text-sm text-slate-500">{{ $users->total() }} usuarios encontrados</p>
    <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        @forelse ($users as $user)
            <div class="flex flex-wrap items-center gap-4 p-4">
                <x-avatar :user="$user" size="sm" />
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-900">{{ $user->name }} @if ($user->is(auth()->user()))<span class="text-xs text-slate-500">(Tú)</span>@endif</p>
                    <p class="break-all text-sm text-slate-500">{{ $user->email }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $user->role->label() }} · {{ $user->active ? 'Habilitado' : 'Deshabilitado' }}</p>
                </div>
                <x-button :href="route('admin.users.edit', $user)" variant="secondary" size="sm" icon="pencil">Editar</x-button>
                @unless ($user->is(auth()->user()))
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                        @csrf
                        <x-button variant="ghost" size="sm">{{ $user->active ? 'Deshabilitar' : 'Habilitar' }}</x-button>
                    </form>
                @endunless
            </div>
        @empty
            <div class="p-6"><x-empty-state icon="users" title="Sin resultados" description="Cambia los filtros o crea un usuario." /></div>
        @endforelse
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
</x-layouts.admin>
