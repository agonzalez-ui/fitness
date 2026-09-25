@foreach (['success' => ['check-circle', 'bg-emerald-50 text-emerald-800 ring-emerald-200'], 'error' => ['alert', 'bg-red-50 text-red-800 ring-red-200']] as $key => [$icon, $classes])
    @if (session($key))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition.opacity
            role="{{ $key === 'error' ? 'alert' : 'status' }}"
            class="mb-5 flex items-start gap-3 rounded-2xl p-4 text-sm font-medium ring-1 {{ $classes }}">
            <x-icon :name="$icon" class="size-5 shrink-0" />
            <p class="flex-1">{{ session($key) }}</p>
            <button type="button" x-on:click="show = false" class="-m-1 rounded-lg p-1 opacity-60 hover:opacity-100" aria-label="Cerrar">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    @endif
@endforeach
