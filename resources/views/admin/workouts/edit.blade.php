<x-layouts.admin :title="$plan->title">
    <a href="{{ $plan->user ? route('admin.clients.show', $plan->user).'#rutina' : route('admin.workouts.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-800">
        <x-icon name="arrow-left" class="size-4" /> {{ $plan->user ? $plan->user->name : 'Rutinas' }}
    </a>
    <livewire:admin.workout-builder :plan="$plan" />
    <p class="mt-6 text-center text-xs text-slate-400">Los cambios se guardan automáticamente.</p>
</x-layouts.admin>
