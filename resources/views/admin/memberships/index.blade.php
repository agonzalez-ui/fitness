<x-layouts.admin title="Membresías">
    <x-page-header title="Membresías" subtitle="Cobros del mes y renovaciones pendientes." />

    @php
        $change = $previous > 0 ? round(($total - $previous) / $previous * 100) : null;
        $isCurrent = $month->isSameMonth(now());
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Resumen del mes con selector --}}
            <x-card>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500">Ingresos de {{ $month->translatedFormat('F Y') }}</p>
                        <p class="text-4xl font-extrabold tabular-nums tracking-tight text-slate-900">{{ money($total) }}</p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $payments->count() }} {{ str('pago')->plural($payments->count()) }}
                            @if ($change !== null)
                                · <span class="{{ $change >= 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ $change >= 0 ? '▲' : '▼' }} {{ abs($change) }}%</span> vs. mes anterior ({{ money($previous) }})
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        <x-button :href="route('admin.memberships.index', ['mes' => $month->subMonthNoOverflow()->format('Y-m')])" variant="secondary" size="sm" aria-label="Mes anterior">←</x-button>
                        <span class="min-w-28 text-center text-sm font-medium text-slate-700">{{ ucfirst($month->translatedFormat('M Y')) }}</span>
                        @if (! $isCurrent)
                            <x-button :href="route('admin.memberships.index', ['mes' => $month->addMonthNoOverflow()->format('Y-m')])" variant="secondary" size="sm" aria-label="Mes siguiente">→</x-button>
                        @else
                            <span class="inline-flex min-h-9 w-9"></span>
                        @endif
                    </div>
                </div>

                @if ($byMethod->isNotEmpty())
                    <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-5 sm:grid-cols-4">
                        @foreach ($byMethod as $method => $amount)
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs text-slate-500">{{ $method }}</dt>
                                <dd class="font-semibold tabular-nums text-slate-900">{{ money($amount) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </x-card>

            <x-card title="Pagos registrados" :padding="false">
                @if ($payments->isEmpty())
                    <div class="p-5"><x-empty-state icon="money" title="Sin pagos este mes" description="Los pagos aparecen aquí al renovar desde la ficha del cliente." /></div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-sm">
                            <thead class="text-left text-xs text-slate-500">
                                <tr><th class="px-5 py-3 font-medium">Cliente</th><th class="px-3 py-3 font-medium">Plan</th><th class="px-3 py-3 font-medium">Vigencia</th><th class="px-3 py-3 font-medium">Pago</th><th class="px-5 py-3 text-right font-medium">Monto</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($payments as $payment)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-3"><a href="{{ route('admin.clients.show', $payment->user) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $payment->user->name }}</a><p class="text-xs text-slate-500">{{ $payment->created_at->translatedFormat('j M') }}</p></td>
                                        <td class="px-3 py-3 text-slate-700">{{ $payment->plan->name }}</td>
                                        <td class="whitespace-nowrap px-3 py-3 text-slate-700">{{ $payment->starts_on->translatedFormat('j M') }} – {{ $payment->ends_on->translatedFormat('j M y') }}</td>
                                        <td class="px-3 py-3 text-slate-700">{{ $payment->payment_method->label() }}</td>
                                        <td class="px-5 py-3 text-right font-semibold tabular-nums text-slate-900">{{ money($payment->price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>

        <x-card title="Renovaciones pendientes" class="self-start">
            <div class="-mt-1 mb-4 flex gap-1">
                @foreach (['expiring_soon' => 'Por vencer', 'expired' => 'Vencidas'] as $value => $label)
                    <a href="{{ route('admin.memberships.index', ['estado' => $value, 'mes' => request('mes')]) }}"
                        @class(['inline-flex min-h-9 items-center gap-1.5 rounded-full px-3 text-sm font-medium', 'bg-slate-900 text-white' => $pendingStatus === $value, 'bg-slate-100 text-slate-600' => $pendingStatus !== $value])>
                        {{ $label }} <span class="text-xs opacity-70">{{ $counts[$value] }}</span>
                    </a>
                @endforeach
            </div>
            @forelse ($pending as $client)
                <div class="flex items-center gap-3 border-b border-slate-100 py-3 first:pt-0 last:border-0 last:pb-0">
                    <x-avatar :user="$client" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $client->name }}</p>
                        <p class="text-xs text-slate-500">{{ $client->latestMembership->plan->name }} · {{ $client->latestMembership->ends_on->translatedFormat('j M') }}</p>
                    </div>
                    <x-button :href="route('admin.clients.show', [$client, 'renovar' => 1]).'#membresia'" size="sm" variant="soft" icon="refresh">Renovar</x-button>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ $pendingStatus === 'expired' ? 'No hay membresías vencidas. 🎉' : 'Nada vence en los próximos 7 días.' }}</p>
            @endforelse
        </x-card>
    </div>
</x-layouts.admin>
