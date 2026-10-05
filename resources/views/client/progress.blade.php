<x-layouts.client title="Progreso">
    @php
        $fmt = fn ($n) => $n === null ? '–' : rtrim(rtrim(number_format($n, 1), '0'), '.');
        $weights = $entries->whereNotNull('weight_kg');
        $startWeight = $weights->first()?->weight_kg;
        $currentWeight = $weights->last()?->weight_kg;
        $diff = $startWeight !== null && $weights->count() > 1 ? round($currentWeight - $startWeight, 1) : null;
        [$measureLabel, $measureUnit] = \App\Models\ProgressEntry::MEASURES[$measure];
        $openMeasures = $errors->hasAny(['body_fat_pct', 'waist_cm', 'hips_cm', 'chest_cm', 'arms_cm', 'legs_cm']) || ($today && ($today->waist_cm || $today->hips_cm));
    @endphp

    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tu progreso</h1>

    {{-- Resumen --}}
    <div class="mt-4 grid grid-cols-3 gap-3">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70">
            <p class="text-xs text-slate-500">Peso actual</p>
            <p class="mt-1 text-xl font-bold tabular-nums text-slate-900">{{ $fmt($currentWeight) }}<span class="text-sm font-medium text-slate-400"> kg</span></p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70">
            <p class="text-xs text-slate-500">Desde el inicio</p>
            <p class="mt-1 text-xl font-bold tabular-nums text-slate-900">{{ $diff === null ? '–' : ($diff > 0 ? '+' : '').$fmt($diff) }}<span class="text-sm font-medium text-slate-400"> kg</span></p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70">
            <p class="text-xs text-slate-500">Registros</p>
            <p class="mt-1 text-xl font-bold tabular-nums text-slate-900">{{ $entries->count() }}</p>
        </div>
    </div>

    {{-- Registrar --}}
    <div class="grid items-start gap-x-6 lg:grid-cols-2">
    <form method="POST" action="{{ route('client.progress.store') }}" x-data="{ more: {{ $openMeasures ? 'true' : 'false' }} }" class="mt-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        @csrf
        <h2 class="font-semibold text-slate-900">{{ $today ? 'Actualizar el registro de hoy' : 'Registrar hoy' }}</h2>
        <x-input name="weight_kg" type="number" step="0.1" inputmode="decimal" label="Peso" suffix="kg" :value="$today?->weight_kg ?? ''" :placeholder="$currentWeight ? $fmt($currentWeight) : '70.5'" class="mt-4" />

        <button type="button" x-on:click="more = !more" class="mt-3 text-sm font-semibold text-brand-700" ::aria-expanded="more">
            <span x-text="more ? '− Ocultar medidas' : '+ Agregar medidas (opcional)'">+ Agregar medidas (opcional)</span>
        </button>
        <div x-cloak x-show="more" x-transition class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach (collect(\App\Models\ProgressEntry::MEASURES)->except('weight_kg') as $field => [$label, $unit])
                <x-input :name="$field" type="number" step="0.1" inputmode="decimal" :label="$label" :suffix="$unit" :value="$today?->$field ?? ''" />
            @endforeach
            <x-input name="recorded_on" type="date" label="Fecha" :value="today()->toDateString()" :max="today()->toDateString()" class="col-span-2 sm:col-span-3" hint="Cámbiala solo si anotas un registro de otro día." />
            <x-textarea name="notes" label="¿Cómo te sientes? (opcional)" :value="$today?->notes" rows="2" class="col-span-2 sm:col-span-3" />
        </div>
        <x-button size="lg" icon="check" class="mt-4 w-full">Guardar</x-button>
    </form>

    {{-- Gráfica --}}
    <section class="mt-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70" aria-labelledby="chart-title">
        <h2 id="chart-title" class="font-semibold text-slate-900">{{ $measureLabel }}</h2>
        @if ($available->count() > 1)
            <div class="-mx-1 mt-3 flex gap-1.5 overflow-x-auto px-1 pb-1">
                @foreach ($available as $field => [$label, $unit])
                    <a href="{{ route('client.progress', ['medida' => $field]) }}" @if ($measure === $field) aria-current="true" @endif
                        @class(['shrink-0 rounded-full px-3 py-1.5 text-sm font-medium', 'bg-slate-900 text-white' => $measure === $field, 'bg-slate-100 text-slate-600' => $measure !== $field])>{{ $label }}</a>
                @endforeach
            </div>
        @endif
        <x-line-chart class="mt-4" :points="$entries->whereNotNull($measure)->map(fn ($e) => ['date' => $e->recorded_on->toDateString(), 'value' => $e->$measure])" :unit="$measureUnit" :label="$measureLabel" />
    </section>

    {{-- Fotos --}}
    <section id="fotos" class="mt-5 scroll-mt-24 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70" x-data="{ upload: {{ $errors->has('photo') ? 'true' : 'false' }} }">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Fotos</h2>
            <x-button type="button" size="sm" variant="soft" icon="camera" x-on:click="upload = !upload">Agregar</x-button>
        </div>
        <p class="mt-1 text-xs text-slate-500">Privadas: solo tú y tu entrenador pueden verlas.</p>

        <form x-cloak x-show="upload" x-transition method="POST" action="{{ route('client.photos.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4 rounded-xl bg-slate-50 p-4">
            @csrf
            <x-choice-chips name="pose" label="Tipo de foto" :options="\App\Models\ProgressPhoto::POSES" value="front" />
            <div>
                <label for="photo" class="mb-1.5 block text-sm font-medium text-slate-700">Foto</label>
                <input id="photo" type="file" name="photo" accept="image/*" capture="environment"
                    class="block w-full text-sm text-slate-600 file:mr-3 file:min-h-11 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:font-semibold file:text-white">
                <x-input-error name="photo" />
            </div>
            <x-button icon="check" class="w-full">Subir foto</x-button>
        </form>

        @if ($photos->isEmpty())
            <p class="mt-4 rounded-xl bg-slate-50 p-4 text-center text-sm text-slate-500">Toma una foto de frente hoy: dentro de unas semanas podrás comparar tu antes y después.</p>
        @else
            @php
                $fronts = $comparisonPhotos;
                $before = $fronts->first();
                $after = $fronts->count() > 1 ? $fronts->last() : null;
            @endphp
            @if ($after)
                <div class="mt-4 grid grid-cols-2 gap-2">
                    @foreach (['Antes' => $before, 'Ahora' => $after] as $label => $photo)
                        <figure class="relative aspect-[3/4] overflow-hidden rounded-xl bg-slate-100">
                            <img src="{{ $photo->url }}" alt="{{ $label }}: foto del {{ $photo->taken_on->translatedFormat('j M Y') }}" class="size-full object-cover" loading="lazy">
                            <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 p-2 text-white"><span class="block text-sm font-bold">{{ $label }}</span><span class="text-xs">{{ $photo->taken_on->translatedFormat('j M Y') }}</span></figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif
            <div class="mt-3 grid grid-cols-3 gap-2">
                @foreach ($photos as $photo)
                    <div class="group relative aspect-[3/4] overflow-hidden rounded-xl bg-slate-100">
                        <img src="{{ $photo->url }}" alt="Foto de {{ mb_strtolower(\App\Models\ProgressPhoto::POSES[$photo->pose] ?? '') }} del {{ $photo->taken_on->translatedFormat('j M Y') }}" class="size-full object-cover" loading="lazy">
                        <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 p-1.5 text-[11px] font-medium text-white">{{ $photo->taken_on->translatedFormat('j M') }}</span>
                        <form method="POST" action="{{ route('client.photos.destroy', $photo) }}" onsubmit="return confirm('¿Eliminar esta foto?')" class="absolute right-1 top-1">
                            @csrf @method('DELETE')
                            <button class="rounded-full bg-black/50 p-1.5 text-white" aria-label="Eliminar foto"><x-icon name="trash" class="size-3.5" /></button>
                        </form>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $photos->links() }}</div>
        @endif
    </section>

    {{-- Historial --}}
    @if ($entries->isNotEmpty())
        <section id="historial" class="mt-5 scroll-mt-24 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
            <h2 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-900">Historial</h2>
            <ul class="divide-y divide-slate-100">
                @foreach ($history as $entry)
                    <li class="flex items-start gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">{{ $entry->recorded_on->translatedFormat('j \d\e F Y') }}</p>
                            <p class="text-sm text-slate-600">
                                {{ collect(\App\Models\ProgressEntry::MEASURES)->filter(fn ($m, $f) => $entry->$f !== null)->map(fn ($m, $f) => $m[0].' '.$fmt($entry->$f).' '.$m[1])->implode(' · ') }}
                            </p>
                            @if ($entry->notes)<p class="mt-1 text-sm italic text-slate-500">“{{ $entry->notes }}”</p>@endif
                        </div>
                        <form method="POST" action="{{ route('client.progress.destroy', $entry) }}" onsubmit="return confirm('¿Eliminar el registro del {{ $entry->recorded_on->translatedFormat('j M') }}?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-700" aria-label="Eliminar registro"><x-icon name="trash" class="size-4" /></button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <div class="p-5">{{ $history->links() }}</div>
        </section>
    @endif
    </div>
</x-layouts.client>
