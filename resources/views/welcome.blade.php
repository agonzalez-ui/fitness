<x-layouts.base title="Entrenamiento personal" class="bg-white">
    <header class="absolute inset-x-0 top-0 z-10">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <x-logo dark />
            <div class="flex items-center gap-2">
                @auth
                    <x-button :href="route(auth()->user()->role->home())" variant="primary">Ir a mi panel</x-button>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/10">Iniciar sesión</a>
                    <x-button :href="route('register')" class="hidden sm:inline-flex">Comenzar</x-button>
                @endauth
            </div>
        </nav>
    </header>

    <section class="relative overflow-hidden bg-slate-900">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,var(--color-brand-600)_0%,transparent_55%)] opacity-40"></div>
        <div class="relative mx-auto max-w-6xl px-6 pb-24 pt-36 sm:pb-32 sm:pt-44">
            <p class="text-sm font-semibold uppercase tracking-widest text-brand-400">Entrenamiento personalizado</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-extrabold tracking-tight text-white sm:text-6xl">Transforma tu cuerpo con un plan hecho para ti.</h1>
            <p class="mt-6 max-w-xl text-lg text-slate-300">Tu rutina del día, tu plan de comidas y tu progreso siempre en el celular, con tu entrenador a un mensaje de distancia.</p>
            <div class="mt-10 flex flex-wrap gap-3">
                <x-button :href="route('register')" size="lg" icon="arrow-right">Crear mi cuenta gratis</x-button>
                <x-button :href="route('login')" size="lg" variant="secondary">Ya tengo cuenta</x-button>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-6 py-20">
        <h2 class="text-3xl font-bold tracking-tight text-slate-900">Todo lo que necesitas para lograr tu objetivo</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['dumbbell', 'Rutina del día', 'Abre la app y ve exactamente qué te toca hoy, con técnica de cada ejercicio.'],
                ['apple', 'Plan de comidas', 'Qué comer y cuánto, con tus calorías y macros calculadas.'],
                ['chart', 'Tu progreso', 'Registra peso, medidas y fotos y mira tu evolución en gráficas.'],
                ['chat', 'Chat con tu coach', 'Resuelve dudas y ajusta tu plan directamente con tu entrenador.'],
            ] as [$icon, $title, $text])
                <div class="rounded-2xl bg-slate-50 p-6">
                    <span class="flex size-12 items-center justify-center rounded-xl bg-brand-100 text-brand-700"><x-icon :name="$icon" class="size-6" /></span>
                    <h3 class="mt-4 font-semibold text-slate-900">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-slate-50">
        <div class="mx-auto max-w-6xl px-6 py-20">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">Cómo funciona</h2>
            <ol class="mt-10 grid gap-6 sm:grid-cols-3">
                @foreach (['Crea tu cuenta y elige tu objetivo.', 'Tu entrenador te asigna tu rutina y tu plan de comidas.', 'Entrena, registra tu progreso y mira los resultados.'] as $i => $step)
                    <li class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70">
                        <span class="flex size-10 items-center justify-center rounded-full bg-slate-900 font-bold text-white">{{ $i + 1 }}</span>
                        <p class="mt-4 font-medium text-slate-800">{{ $step }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <footer class="border-t border-slate-200 py-8 text-center text-sm text-slate-500">© {{ date('Y') }} GU FITCR. Todos los derechos reservados.</footer>
</x-layouts.base>
