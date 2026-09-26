<x-layouts.admin title="Inicio">
    <x-page-header :title="'Hola, '.auth()->user()->first_name" :subtitle="now()->translatedFormat('l j \d\e F')">
        <x-slot:actions>
            @if (Route::has('admin.clients.create'))
                <x-button :href="route('admin.clients.create')" icon="plus">Nuevo cliente</x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Clientes activos" :value="$stats['clients']" icon="users" tone="brand" :hint="$newClients ? $newClients.' nuevos este mes' : null" :href="Route::has('admin.clients.index') ? route('admin.clients.index') : null" />
        <x-stat label="Membresías al día" :value="$stats['active']" icon="check-circle" tone="green" :hint="$expiredCount ? $expiredCount.' vencidas' : 'Todas al día'" />
        <x-stat label="Ingresos del mes" :value="money($stats['income'])" icon="money" tone="slate" :hint="now()->translatedFormat('F Y')" />
        <x-stat label="Mensajes sin leer" :value="$stats['unread']" icon="chat" :tone="$stats['unread'] ? 'amber' : 'slate'" :href="Route::has('admin.messages.index') ? route('admin.messages.index') : null" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        {{-- Membresías: lo más urgente primero --}}
        <x-card title="Membresías que necesitan atención" subtitle="Renueva con un clic">
            @forelse ($expiring->concat($expired) as $client)
                @php $membership = $client->latestMembership; @endphp
                <div class="flex items-center gap-3 border-b border-slate-100 py-3 first:pt-0 last:border-0 last:pb-0">
                    <x-avatar :user="$client" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-slate-900">{{ $client->name }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $membership->plan->name }} ·
                            @if ($client->membership_status === \App\Enums\MembershipStatus::Expired)
                                venció {{ $membership->ends_on->diffForHumans() }}
                            @else
                                vence {{ $membership->ends_on->isToday() ? 'hoy' : $membership->ends_on->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                    <x-membership-badge :status="$client->membership_status" class="hidden sm:inline-flex" />
                    @if (Route::has('admin.clients.show'))
                        <x-button :href="route('admin.clients.show', [$client, 'renovar' => 1])" size="sm" variant="soft" icon="refresh">Renovar</x-button>
                    @endif
                </div>
            @empty
                <x-empty-state icon="check-circle" title="Todo al día" description="Ninguna membresía vence en los próximos 7 días." />
            @endforelse
            @if ($expiredCount > $expired->count() && Route::has('admin.memberships.index'))
                <a href="{{ route('admin.memberships.index', ['estado' => 'expired']) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700">Ver las {{ $expiredCount }} vencidas <x-icon name="arrow-right" class="size-4" /></a>
            @endif
        </x-card>

        <x-card title="Mensajes recientes">
            @forelse ($recentMessages as $message)
                <a href="{{ Route::has('admin.messages.show') ? route('admin.messages.show', $message->sender) : '#' }}" class="-mx-2 flex items-start gap-3 rounded-xl p-2 hover:bg-slate-50">
                    <x-avatar :user="$message->sender" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-2 text-sm">
                            <span class="font-medium text-slate-900">{{ $message->sender->name }}</span>
                            @unless ($message->read_at)<span class="size-2 rounded-full bg-brand-500" title="Sin leer"></span>@endunless
                            <span class="ml-auto shrink-0 text-xs text-slate-400">{{ $message->created_at->diffForHumans(short: true) }}</span>
                        </p>
                        <p class="truncate text-sm text-slate-500">{{ $message->body }}</p>
                    </div>
                </a>
            @empty
                <x-empty-state icon="chat" title="Sin mensajes" description="Aquí verás las consultas de tus clientes." />
            @endforelse
        </x-card>

        <x-card title="Clientes sin rutina asignada">
            @forelse ($withoutPlan as $client)
                <div class="flex items-center gap-3 border-b border-slate-100 py-3 first:pt-0 last:border-0 last:pb-0">
                    <x-avatar :user="$client" size="sm" />
                    <p class="min-w-0 flex-1 truncate font-medium text-slate-900">{{ $client->name }}</p>
                    @if (Route::has('admin.clients.show'))
                        <x-button :href="route('admin.clients.show', $client).'#rutina'" size="sm" variant="secondary" icon="dumbbell">Asignar</x-button>
                    @endif
                </div>
            @empty
                <x-empty-state icon="dumbbell" title="Todos tienen rutina" />
            @endforelse
        </x-card>

        <x-card title="Sin registrar progreso en 2 semanas" subtitle="Buen momento para escribirles">
            @forelse ($inactive as $client)
                <div class="flex items-center gap-3 border-b border-slate-100 py-3 first:pt-0 last:border-0 last:pb-0">
                    <x-avatar :user="$client" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-slate-900">{{ $client->name }}</p>
                        <p class="text-sm text-slate-500">{{ $client->latestProgress ? 'Último registro '.$client->latestProgress->recorded_on->diffForHumans() : 'Nunca ha registrado' }}</p>
                    </div>
                    @if (Route::has('admin.messages.show'))
                        <x-button :href="route('admin.messages.show', $client)" size="sm" variant="ghost" icon="chat" aria-label="Escribir a {{ $client->name }}" />
                    @endif
                </div>
            @empty
                <x-empty-state icon="chart" title="Todos al día" description="Tus clientes registran su progreso con regularidad." />
            @endforelse
        </x-card>
    </div>

    @if ($recentWorkouts->isNotEmpty())
        <x-card title="Entrenamientos completados" class="mt-6">
            <ul class="divide-y divide-slate-100">
                @foreach ($recentWorkouts as $log)
                    <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                        <x-avatar :user="$log->user" size="sm" />
                        <p class="min-w-0 flex-1 text-sm"><span class="font-medium text-slate-900">{{ $log->user->name }}</span> completó <span class="font-medium">{{ $log->day?->name ?? 'un entrenamiento' }}</span></p>
                        <span class="text-xs text-slate-400">{{ $log->finished_at->diffForHumans(short: true) }}</span>
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endif
</x-layouts.admin>
