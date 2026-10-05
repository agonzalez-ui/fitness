<x-layouts.admin title="Mantenimientos">
    <x-page-header title="Mantenimientos" subtitle="Elige qué quieres administrar. En cada sección puedes agregar, buscar, editar y deshabilitar registros." />
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ([
            ['admin.catalog.exercises', 'Ejercicios', 'dumbbell', $exerciseCount, 'Técnica, video, series, repeticiones y descanso.'],
            ['admin.catalog.foods', 'Alimentos', 'apple', $foodCount, 'Porciones, calorías y nutrientes para los planes de alimentación.'],
            ['admin.catalog.plans', 'Planes de membresía', 'card', $planCount, 'Duración, precio en colones y disponibilidad.'],
            ['admin.clients.index', 'Clientes', 'users', $clientCount, 'Datos personales, acceso, membresías y planes asignados.'],
            ['admin.users.index', 'Usuarios', 'users', $userCount, 'Cuentas de entrenadores y clientes, roles y estado de acceso.'],
        ] as [$route, $label, $icon, $count, $description])
            <a href="{{ route($route) }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:ring-brand-600 focus-visible:outline-2 focus-visible:outline-brand-600">
                <div class="mb-4 flex items-center justify-between">
                    <x-icon :name="$icon" class="size-7 text-brand-700" />
                    <span class="text-sm text-slate-500">{{ $count }} {{ $count === 1 ? 'registro' : 'registros' }}</span>
                </div>
                <h2 class="text-lg font-semibold text-slate-900">{{ $label }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ $description }}</p>
                <span class="mt-4 inline-block text-sm font-semibold text-brand-700">Administrar →</span>
            </a>
        @endforeach
    </div>
</x-layouts.admin>
