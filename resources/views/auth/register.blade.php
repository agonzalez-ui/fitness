<x-layouts.guest title="Crear cuenta" heading="Empieza tu transformación" subheading="Crea tu cuenta en menos de un minuto.">
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
        @csrf
        <x-input name="name" label="Nombre completo" autocomplete="name" required autofocus />
        <x-input name="email" type="email" label="Correo" autocomplete="email" inputmode="email" required />
        <x-input name="phone" type="tel" label="Teléfono (opcional)" autocomplete="tel" inputmode="tel" placeholder="8888-8888" />

        <x-choice-chips name="goal" label="¿Cuál es tu objetivo?" :options="\App\Enums\Goal::options()"
            :icons="collect($goals)->mapWithKeys(fn ($goal) => [$goal->value => $goal->icon()])->all()" />

        <x-input name="password" type="password" label="Contraseña" autocomplete="new-password" hint="Mínimo 8 caracteres, con letras y números." required />
        <x-input name="password_confirmation" type="password" label="Repite la contraseña" autocomplete="new-password" required />

        <x-button size="lg" class="w-full">Crear mi cuenta</x-button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800">Inicia sesión</a>
    </p>
</x-layouts.guest>
