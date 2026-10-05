<x-layouts.admin :title="$client->name">
    @php
        $membership = $client->latestMembership;
        $status = $client->membership_status;
        $workout = $client->activeWorkoutPlan;
        $nutrition = $client->activeNutritionPlan;
        $weights = $client->progressEntries->whereNotNull('weight_kg');
        $whatsapp = \App\Support\TemporaryPassword::whatsappLink($client->phone, 'Hola '.$client->first_name.'! ');
    @endphp

    <a href="{{ route('admin.clients.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-800">
        <x-icon name="arrow-left" class="size-4" /> Clientes
    </a>

    <x-credentials-notice :user="$client" />

    {{-- Encabezado --}}
    <div class="mb-6 flex flex-wrap items-center gap-4">
        <x-avatar :user="$client" size="xl" />
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $client->name }}</h1>
            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500">
                @if ($client->goal)<span>{{ $client->goal->icon() }} {{ $client->goal->label() }}</span>@endif
                @if ($client->active)
                    <x-membership-badge :status="$status" />
                @else
                    <x-badge color="red">Cuenta desactivada</x-badge>
                @endif
                @if ($client->last_seen_at)<span>Visto {{ $client->last_seen_at->diffForHumans() }}</span>@endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($whatsapp)<x-button :href="$whatsapp" target="_blank" rel="noopener" variant="secondary" icon="send">WhatsApp</x-button>@endif
            @if (Route::has('admin.messages.show'))<x-button :href="route('admin.messages.show', $client)" variant="secondary" icon="chat">Mensaje</x-button>@endif
            <x-button :href="route('admin.clients.edit', $client)" variant="secondary" icon="pencil">Editar</x-button>
            <div x-data="{ open: false }" class="relative">
                <x-button type="button" variant="ghost" x-on:click="open = !open" aria-label="Más acciones" ::aria-expanded="open">•••</x-button>
                <div x-cloak x-show="open" x-on:click.outside="open = false" x-transition class="absolute right-0 z-20 mt-2 w-60 rounded-xl bg-white p-1 shadow-lg ring-1 ring-slate-200">
                    <form method="POST" action="{{ route('admin.clients.password', $client) }}" onsubmit="return confirm('¿Generar una contraseña nueva? La actual dejará de funcionar.')">
                        @csrf
                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm hover:bg-slate-50"><x-icon name="refresh" class="size-4" /> Nueva contraseña</button>
                    </form>
                    @if ($client->active)
                        <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" onsubmit="return confirm('¿Desactivar a {{ $client->first_name }}? No podrá entrar, pero se conserva todo su historial.')">
                            @csrf @method('DELETE')
                            <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm text-red-700 hover:bg-red-50"><x-icon name="x" class="size-4" /> Desactivar cliente</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.clients.activate', $client) }}">
                            @csrf
                            <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm text-emerald-700 hover:bg-emerald-50"><x-icon name="check" class="size-4" /> Reactivar cliente</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Membresía --}}
            <x-card title="Membresía" id="membresia" x-data="{ renew: {{ request('renovar') || $errors->hasAny(['plan_id', 'payment_method']) ? 'true' : 'false' }} }">
                <x-slot:actions>
                    <x-button type="button" size="sm" :variant="$status === \App\Enums\MembershipStatus::Active ? 'secondary' : 'primary'" icon="refresh" x-on:click="renew = !renew" x-show="!renew">Renovar</x-button>
                </x-slot:actions>

                @if ($membership)
                    <div class="flex flex-wrap items-center gap-6">
                        <div>
                            <p class="text-sm text-slate-500">Plan</p>
                            <p class="font-semibold text-slate-900">{{ $membership->plan->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Vigencia</p>
                            <p class="font-semibold text-slate-900">{{ $membership->starts_on->translatedFormat('j M') }} – {{ $membership->ends_on->translatedFormat('j M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">{{ $status === \App\Enums\MembershipStatus::Expired ? 'Vencida hace' : 'Quedan' }}</p>
                            <p @class(['font-semibold', 'text-red-700' => $status === \App\Enums\MembershipStatus::Expired, 'text-amber-700' => $status === \App\Enums\MembershipStatus::ExpiringSoon, 'text-slate-900' => $status === \App\Enums\MembershipStatus::Active])>
                                {{ $status === \App\Enums\MembershipStatus::Expired ? $membership->ends_on->diffInDays(today()).' días' : $membership->days_left.' '.str('día')->plural($membership->days_left) }}
                            </p>
                        </div>
                    </div>
                @else
                    <p class="text-slate-500">Sin membresía registrada.</p>
                @endif

                <form x-cloak x-show="renew" x-transition method="POST" action="{{ route('admin.clients.memberships.store', $client) }}" class="mt-5 border-t border-slate-100 pt-5">
                    @csrf
                    <p class="mb-3 text-sm text-slate-600">La nueva membresía empieza el <strong>{{ $nextStart->translatedFormat('j \d\e F') }}</strong>{{ $nextStart->isFuture() ? ', al terminar la actual, así no pierde días' : '' }}.</p>
                    <x-radio-cards name="plan_id">
                        @foreach ($plans as $plan)
                            <x-radio-card name="plan_id" :value="$plan->id" :title="$plan->name"
                                :meta="'Hasta el '.$nextStart->addMonthsNoOverflow($plan->duration_months)->subDay()->translatedFormat('j M Y')"
                                :aside="money($plan->price)" :checked="old('plan_id', $membership?->plan_id) == $plan->id" />
                        @endforeach
                    </x-radio-cards>
                    <x-choice-chips name="payment_method" label="¿Cómo pagó?" :options="\App\Enums\PaymentMethod::options()" :value="old('payment_method', $membership?->payment_method?->value ?? 'sinpe')" class="mt-5" />
                    <div class="mt-5 flex justify-end gap-2">
                        <x-button type="button" variant="ghost" x-on:click="renew = false">Cancelar</x-button>
                        <x-button icon="check">Registrar pago</x-button>
                    </div>
                </form>
            </x-card>

            {{-- Rutina --}}
            <x-card title="Rutina" id="rutina">
                @if ($workout)
                    <x-slot:actions>
                        @if (Route::has('admin.workouts.edit'))<x-button :href="route('admin.workouts.edit', $workout)" size="sm" variant="soft" icon="pencil">Editar rutina</x-button>@endif
                    </x-slot:actions>
                    <p class="font-semibold text-slate-900">{{ $workout->title }} <x-badge :color="$workout->level->color()" class="ml-1 align-middle">{{ $workout->level->label() }}</x-badge></p>
                    @if ($workout->file_path)
                        <a href="{{ $workout->file_url }}" target="_blank" class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-brand-700"><x-icon name="document" class="size-4" /> {{ $workout->file_name }}</a>
                    @endif
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($workout->days as $day)
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-sm font-semibold text-slate-900">{{ $day->name }} @if ($day->weekday_name)<span class="font-normal text-slate-500">· {{ $day->weekday_name }}</span>@endif</p>
                                <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $day->items->pluck('exercise.name')->implode(', ') ?: 'Sin ejercicios' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.clients.workout', $client) }}" @class(['flex flex-col gap-2 sm:flex-row sm:items-end', 'mt-5 border-t border-slate-100 pt-5' => $workout])>
                    @csrf
                    <x-select name="source_id" :label="$workout ? 'Cambiar por otra plantilla' : 'Asignar rutina desde plantilla'" placeholder="Elegir plantilla…"
                        :options="$workoutTemplates->mapWithKeys(fn ($t) => [$t->id => $t->title.' · '.$t->level->label()])->all()" class="flex-1" />
                    <x-button :variant="$workout ? 'secondary' : 'primary'" icon="dumbbell">Asignar</x-button>
                </form>
            </x-card>

            {{-- Nutrición --}}
            <x-card title="Plan de alimentación" id="nutricion">
                @if ($nutrition)
                    @php $totals = $nutrition->totals; @endphp
                    <x-slot:actions>
                        @if (Route::has('admin.nutrition.edit'))<x-button :href="route('admin.nutrition.edit', $nutrition)" size="sm" variant="soft" icon="pencil">Editar plan</x-button>@endif
                    </x-slot:actions>
                    <p class="font-semibold text-slate-900">{{ $nutrition->title }}</p>
                    @if ($nutrition->file_path)
                        <a href="{{ $nutrition->file_url }}" target="_blank" class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-brand-700"><x-icon name="document" class="size-4" /> {{ $nutrition->file_name }}</a>
                    @endif
                    @if ($totals['calories'])
                        <p class="mt-1 text-sm text-slate-500">{{ $totals['calories'] }} kcal · P {{ round($totals['protein_g']) }} g · C {{ round($totals['carbs_g']) }} g · G {{ round($totals['fats_g']) }} g · {{ $nutrition->meals->count() }} comidas</p>
                    @elseif ($nutrition->notes)
                        <p class="mt-2 line-clamp-3 whitespace-pre-line text-sm text-slate-500">{{ $nutrition->notes }}</p>
                    @endif
                @endif

                <form method="POST" action="{{ route('admin.clients.nutrition', $client) }}" @class(['flex flex-col gap-2 sm:flex-row sm:items-end', 'mt-5 border-t border-slate-100 pt-5' => $nutrition])>
                    @csrf
                    <x-select name="source_id" :label="$nutrition ? 'Cambiar por otra plantilla' : 'Asignar plan desde plantilla'" placeholder="Elegir plantilla…" id="nutrition_source_id"
                        :options="$nutritionTemplates->mapWithKeys(fn ($t) => [$t->id => $t->title])->all()" class="flex-1" />
                    <x-button :variant="$nutrition ? 'secondary' : 'primary'" icon="apple">Asignar</x-button>
                </form>
            </x-card>

            {{-- Progreso --}}
            <x-card title="Peso" :subtitle="$weights->count() ? $weights->count().' registros' : null" id="progreso">
                <x-line-chart :points="$weights->map(fn ($e) => ['date' => $e->recorded_on->toDateString(), 'value' => $e->weight_kg])" unit="kg" label="Peso" />

                @if ($client->progressEntries->isNotEmpty())
                    <div class="-mx-5 mt-5 overflow-x-auto border-t border-slate-100">
                        <table class="w-full min-w-[560px] text-sm">
                            <caption class="sr-only">Historial de medidas</caption>
                            <thead class="text-left text-xs text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Fecha</th>
                                    @foreach (\App\Models\ProgressEntry::MEASURES as [$label, $unit])
                                        <th class="px-2 py-3 text-right font-medium">{{ $label }} <span class="font-normal">({{ $unit }})</span></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($progressHistory as $entry)
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-2.5 font-medium text-slate-900" @if ($entry->notes) title="{{ $entry->notes }}" @endif>
                                            {{ $entry->recorded_on->translatedFormat('j M Y') }} @if ($entry->notes)<x-icon name="chat" class="inline size-3.5 text-slate-400" />@endif
                                        </td>
                                        @foreach (array_keys(\App\Models\ProgressEntry::MEASURES) as $field)
                                            <td class="px-2 py-2.5 text-right tabular-nums text-slate-700">{{ $entry->$field !== null ? rtrim(rtrim(number_format($entry->$field, 1), '0'), '.') : '–' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $progressHistory->links() }}</div>
                @endif
            </x-card>

            @if ($progressPhotos->isNotEmpty())
                <x-card title="Fotos de progreso" id="fotos" class="scroll-mt-24">
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                        @foreach ($progressPhotos as $photo)
                            <a href="{{ $photo->url }}" target="_blank" class="group relative block aspect-[3/4] overflow-hidden rounded-xl bg-slate-100">
                                <img src="{{ $photo->url }}" alt="Foto de progreso del {{ $photo->taken_on->translatedFormat('j M Y') }}" loading="lazy" class="size-full object-cover transition group-hover:scale-105" onerror="this.remove()">
                                <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 p-2 text-xs font-medium text-white">{{ $photo->taken_on->translatedFormat('j M Y') }}</span>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $progressPhotos->links() }}</div>
                </x-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-card title="Datos">
                <dl class="space-y-3 text-sm">
                    @foreach ([
                        'Correo' => $client->email,
                        'Teléfono' => $client->phone,
                        'Edad' => $client->age ? $client->age.' años' : null,
                        'Estatura' => $client->height_cm ? rtrim(rtrim($client->height_cm, '0'), '.').' cm' : null,
                        'Cliente desde' => $client->created_at?->translatedFormat('F Y'),
                    ] as $label => $value)
                        @continue(! $value)
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ $label }}</dt><dd class="truncate text-right font-medium text-slate-900">{{ $value }}</dd></div>
                    @endforeach
                </dl>
                @if ($client->trainer_notes)
                    <div class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-900">
                        <p class="mb-1 text-xs font-semibold uppercase tracking-wide">Notas privadas</p>
                        <p class="whitespace-pre-line">{{ $client->trainer_notes }}</p>
                    </div>
                @endif
            </x-card>

            <x-card title="Últimos entrenamientos">
                @forelse ($recentLogs as $log)
                    <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm first:pt-0 last:border-0 last:pb-0">
                        <span class="flex items-center gap-2 font-medium text-slate-900"><x-icon name="check-circle" class="size-4 text-emerald-600" /> {{ $log->day?->name ?? 'Entrenamiento' }}</span>
                        <span class="text-slate-500">{{ $log->performed_on->translatedFormat('D j M') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Aún no ha marcado entrenamientos en la app.</p>
                @endforelse
            </x-card>

            <x-card title="Historial de pagos">
                @forelse ($client->memberships as $payment)
                    <div class="flex items-center gap-3 border-b border-slate-100 py-2.5 text-sm first:pt-0 last:border-0 last:pb-0">
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-slate-900">{{ $payment->plan->name }}</p>
                            <p class="text-xs text-slate-500">{{ $payment->starts_on->translatedFormat('j M Y') }} · {{ $payment->payment_method->label() }}</p>
                        </div>
                        <span class="font-semibold tabular-nums text-slate-900">{{ money($payment->price) }}</span>
                        <form method="POST" action="{{ route('admin.clients.memberships.destroy', [$client, $payment]) }}" onsubmit="return confirm('¿Eliminar este pago del historial? Úselo solo para corregir un error.')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-700" aria-label="Eliminar pago" title="Eliminar (corregir error)"><x-icon name="trash" class="size-4" /></button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Sin pagos registrados.</p>
                @endforelse
            </x-card>
        </div>
    </div>
</x-layouts.admin>
