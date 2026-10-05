<details class="mt-3 rounded-xl border border-slate-200 p-3">
    <summary class="cursor-pointer text-sm font-semibold text-brand-700">Registrar mis series · opcional</summary>
    <form wire:submit="save" class="mt-3 space-y-3">
        <p class="text-xs text-slate-500">Anotá lo que hiciste hoy. RIR = repeticiones que todavía podrías haber hecho (0 = ninguna).</p>
        <div class="grid grid-cols-2 gap-2">
            <label class="text-sm">Unidad<select wire:model="unit" class="mt-1 block w-full rounded-lg border-slate-300"><option value="kg">KG</option><option value="lb">LB</option></select></label>
            <label class="text-sm">Tipo de peso<select wire:model="basis" class="mt-1 block w-full rounded-lg border-slate-300"><option value="total">Peso total / barra</option><option value="per_dumbbell">Por mancuerna</option><option value="bodyweight">Peso corporal (carga extra)</option></select></label>
        </div>
        <p class="text-xs text-slate-500">Para peso corporal sin carga extra, ingresá 0.</p>
        @foreach ($sets as $index => $set)
            <fieldset wire:key="set-{{ $itemId }}-{{ $index }}" class="rounded-lg bg-slate-50 p-2">
                <legend class="text-xs font-semibold">Serie {{ $index + 1 }}</legend>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['weight' => ['Peso', '0.01', 0, 2000], 'reps' => ['Reps', 1, 1, 200], 'rir' => ['RIR (opcional)', 1, 0, 10]] as $field => [$label, $step, $min, $max])
                        <label class="text-xs">{{ $label }}<input type="number" wire:model="sets.{{ $index }}.{{ $field }}" step="{{ $step }}" min="{{ $min }}" max="{{ $max }}" @required($field !== 'rir') class="mt-1 block w-full rounded-lg border-slate-300" />
                            @error('sets.'.$index.'.'.$field)<span class="text-red-700">{{ $message }}</span>@enderror
                        </label>
                    @endforeach
                </div>
                @if (count($sets) > 1)<button type="button" wire:click="removeSet({{ $index }})" class="mt-2 text-xs text-red-700 underline">Quitar serie {{ $index + 1 }}</button>@endif
            </fieldset>
        @endforeach
        @if ($errors->any())<p role="alert" class="text-sm text-red-700">Revisá los datos de las series antes de guardar.</p>@endif
        <div class="flex flex-wrap gap-3">
            @if (count($sets) < 20)<button type="button" wire:click="addSet" class="text-sm font-semibold text-brand-700">+ Agregar serie</button>@endif
            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white">Guardar series</button>
        </div>
        @if ($saved)<p role="status" class="text-sm font-semibold text-emerald-700">Series guardadas para hoy. Podés corregirlas y volver a guardar.</p>@endif
    </form>
</details>
