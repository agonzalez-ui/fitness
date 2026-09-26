<x-layouts.admin title="Planes de membresía">
    @include('admin.catalog._tabs')

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            @foreach ($plans as $plan)
                @php $editing = old('_row') == $plan->id && $errors->edit->any(); @endphp
                <div x-data="{ edit: {{ $editing ? 'true' : 'false' }} }" @class(['rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70', 'opacity-60' => ! $plan->active])>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-900">{{ $plan->name }} @unless ($plan->active)<x-badge>Oculto</x-badge>@endunless</p>
                            <p class="text-sm text-slate-500">{{ $plan->duration_months }} {{ str('mes')->plural($plan->duration_months) }} · {{ $plan->memberships_count }} {{ str('venta')->plural($plan->memberships_count) }}{{ $plan->description ? ' · '.$plan->description : '' }}</p>
                        </div>
                        <p class="text-2xl font-bold tabular-nums text-slate-900">{{ money($plan->price) }}</p>
                        <div class="flex gap-1">
                            <x-button type="button" size="sm" variant="ghost" icon="pencil" x-on:click="edit = !edit">Editar</x-button>
                            <form method="POST" action="{{ route('admin.catalog.plans.toggle', $plan) }}">
                                @csrf
                                <x-button size="sm" variant="ghost">{{ $plan->active ? 'Ocultar' : 'Mostrar' }}</x-button>
                            </form>
                        </div>
                    </div>
                    <form x-cloak x-show="edit" x-transition method="POST" action="{{ route('admin.catalog.plans.update', $plan) }}" class="mt-4 grid gap-4 rounded-xl bg-slate-50 p-4 sm:grid-cols-3">
                        @csrf @method('PUT')
                        <input type="hidden" name="_row" value="{{ $plan->id }}">
                        @php $bag = $editing ? 'edit' : 'none'; @endphp
                        <x-input :bag="$bag" :use-old="$editing" name="name" :id="'plan'.$plan->id.'-name'" label="Nombre" :value="$plan->name" />
                        <x-input :bag="$bag" :use-old="$editing" name="duration_months" :id="'plan'.$plan->id.'-months'" type="number" min="1" max="24" label="Duración" suffix="meses" :value="$plan->duration_months" />
                        <x-input :bag="$bag" :use-old="$editing" name="price" :id="'plan'.$plan->id.'-price'" type="number" min="0" step="0.01" label="Precio" :suffix="config('fitness.currency')" :value="$plan->price" />
                        <x-input :bag="$bag" :use-old="$editing" name="description" :id="'plan'.$plan->id.'-desc'" label="Descripción (opcional)" :value="$plan->description" class="sm:col-span-2" />
                        <div class="flex items-end justify-end gap-2">
                            <x-button type="button" variant="ghost" x-on:click="edit = false">Cancelar</x-button>
                            <x-button icon="check">Guardar</x-button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>

        <x-card title="Nuevo plan" class="self-start">
            <form method="POST" action="{{ route('admin.catalog.plans.store') }}" class="space-y-4">
                @csrf
                <x-input bag="create" name="name" id="new-plan-name" label="Nombre" placeholder="Ej.: Bimestral" />
                <div class="grid grid-cols-2 gap-3">
                    <x-input bag="create" name="duration_months" id="new-plan-months" type="number" min="1" max="24" label="Duración" suffix="meses" value="1" />
                    <x-input bag="create" name="price" id="new-plan-price" type="number" min="0" step="0.01" label="Precio" :suffix="config('fitness.currency')" />
                </div>
                <x-input bag="create" name="description" id="new-plan-desc" label="Descripción (opcional)" />
                <x-button icon="plus" class="w-full">Crear plan</x-button>
            </form>
        </x-card>
    </div>
</x-layouts.admin>
