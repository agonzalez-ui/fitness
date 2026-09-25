<x-layouts.guest title="Iniciar sesión" heading="¡Hola de nuevo!" subheading="Ingresa para ver tu rutina y tu progreso.">
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf
        <x-input name="email" type="email" label="Correo" autocomplete="email" inputmode="email" required autofocus />
        <x-input name="password" type="password" label="Contraseña" autocomplete="current-password" required />

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-brand-700 focus:ring-brand-600">
            Mantener sesión iniciada
        </label>

        <x-button size="lg" class="w-full">Entrar</x-button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        ¿No tienes cuenta?
        <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:text-brand-800">Regístrate gratis</a>
    </p>
</x-layouts.guest>
