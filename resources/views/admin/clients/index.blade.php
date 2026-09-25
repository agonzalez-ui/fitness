<x-layouts.admin title="Clientes">
    <x-page-header title="Clientes" :subtitle="$clients->total().' '.str('cliente')->plural($clients->total()).' encontrados'">
        <x-slot:actions>
            <x-button :href="route('admin.clients.create')" icon="plus">Nuevo cliente</x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Filtros en una fila: estado como pestañas + búsqueda --}}
    @php
        $current = request('estado');
        $tabs = [
            null => ['Todos', null],
            'active' => ['Al día', $counts['active']],
            'expiring_soon' => ['Por vencer', $counts['expiring_soon']],
            'expired' => ['Vencidas', $counts['expired']],
            'none' => ['Sin membresía', $counts['none']],
            'inactive' => ['Desactivados', $inactiveCount],
        ];
    @endphp
    <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center">
        <nav class="-mx-1 flex gap-1 overflow-x-auto px-1 pb-1" aria-label="Filtrar por estado">
            @foreach ($tabs as $value => [$label, $count])
                <a href="{{ route('admin.clients.index', array_filter(['estado' => $value, 'q' => request('q')])) }}"
                    @if ($current == $value) aria-current="page" @endif
                    @class(['inline-flex min-h-10 shrink-0 items-center gap-1.5 rounded-full px-4 text-sm font-medium transition',
                        'bg-slate-900 text-white' => $current == $value,
                        'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $current != $value])>
                    {{ $label }}
                    @if ($count)<span @class(['rounded-full px-1.5 text-xs', 'bg-white/20' => $current == $value, 'bg-slate-100' => $current != $value])>{{ $count }}</span>@endif
                </a>
            @endforeach
        </nav>
        <form method="GET" class="relative lg:ml-auto lg:w-80" role="search">
            @if ($current)<input type="hidden" name="estado" value="{{ $current }}">@endif
            <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400" />
            <label for="q" class="sr-only">Buscar cliente</label>
            <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, correo o teléfono"
                class="block min-h-11 w-full rounded-xl border-0 bg-white pl-11 pr-4 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-brand-600">
        </form>
    </div>

    @if ($clients->isEmpty())
        <x-empty-state icon="users" :title="request()->hasAny(['q', 'estado']) ? 'No hay clientes con ese filtro' : 'Aún no tienes clientes'"
            :description="request()->hasAny(['q', 'estado']) ? 'Prueba con otro nombre o quita el filtro.' : 'Crea el primero y déjalo listo con membresía, rutina y dieta en un solo paso.'" class="bg-white">
            @if (request()->hasAny(['q', 'estado']))
                <x-button :href="route('admin.clients.index')" variant="secondary">Quitar filtros</x-button>
            @else
                <x-button :href="route('admin.clients.create')" icon="plus">Nuevo cliente</x-button>
            @endif
        </x-empty-state>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($clients as $client)
                <a href="{{ route('admin.clients.show', $client) }}" class="group flex flex-col rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 transition hover:ring-brand-300">
                    <div class="flex items-center gap-3">
                        <x-avatar :user="$client" size="lg" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-900 group-hover:text-brand-800">{{ $client->name }}</p>
                            <p class="truncate text-sm text-slate-500">{{ $client->goal ? $client->goal->icon().' '.$client->goal->label() : $client->email }}</p>
                        </div>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500">Membresía</dt>
                            <dd class="mt-1">
                                @if ($client->active)
                                    <x-membership-badge :status="$client->membership_status" />
                                @else
                                    <x-badge>Desactivado</x-badge>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">{{ $client->latestMembership ? ($client->membership_status === \App\Enums\MembershipStatus::Expired ? 'Venció' : 'Vence') : 'Rutina' }}</dt>
                            <dd class="mt-1 font-medium text-slate-800">
                                @if ($client->latestMembership)
                                    {{ $client->latestMembership->ends_on->translatedFormat('j M Y') }}
                                @else
                                    {{ $client->activeWorkoutPlan?->title ?? 'Sin asignar' }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $clients->links() }}</div>
    @endif
</x-layouts.admin>
