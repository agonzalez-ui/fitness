<x-page-header title="Catálogos" subtitle="Lo que usan los armadores de rutinas, dietas y las renovaciones." />
<nav class="mb-6 flex gap-1 overflow-x-auto pb-1" aria-label="Catálogos">
    @foreach ([['admin.catalog.exercises', 'Ejercicios', 'dumbbell'], ['admin.catalog.foods', 'Alimentos', 'apple'], ['admin.catalog.plans', 'Planes de membresía', 'card']] as [$route, $label, $icon])
        <a href="{{ route($route) }}" @if (request()->routeIs($route)) aria-current="page" @endif
            @class(['inline-flex min-h-10 shrink-0 items-center gap-2 rounded-full px-4 text-sm font-medium',
                'bg-slate-900 text-white' => request()->routeIs($route),
                'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => ! request()->routeIs($route)])>
            <x-icon :name="$icon" class="size-4" /> {{ $label }}
        </a>
    @endforeach
</nav>
