<x-layouts.client title="Mi entrenador">
    <div class="-mx-4 -mb-28 -mt-5 flex h-[calc(100dvh-3.75rem-4.25rem)] flex-col bg-slate-50">
        <header class="flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3">
            <span class="flex size-10 items-center justify-center rounded-full bg-slate-900 text-white"><x-icon name="dumbbell" class="size-5" /></span>
            <div>
                <p class="font-semibold text-slate-900">Tu entrenador</p>
                <p class="text-xs text-slate-500">Normalmente responde el mismo día</p>
            </div>
        </header>
        <div class="min-h-0 flex-1">
            <livewire:chat :client="$client" />
        </div>
    </div>
</x-layouts.client>
