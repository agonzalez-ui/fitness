@props(['user'])
{{-- Se muestra una sola vez, justo después de crear el cliente o generar una contraseña nueva. --}}
@if ($credentials = session('credentials'))
    @php
        $message = \App\Support\TemporaryPassword::welcomeMessage($user->name, $credentials['email'], $credentials['password']);
        $whatsapp = \App\Support\TemporaryPassword::whatsappLink($user->phone, $message);
    @endphp
    <div x-data="{ copied: false, message: @js($message) }" class="mb-6 rounded-2xl bg-slate-900 p-5 text-white">
        <p class="text-sm font-semibold uppercase tracking-wider text-brand-300">Datos de acceso para {{ $user->first_name }}</p>
        <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-400">Correo</dt><dd class="font-mono text-base">{{ $credentials['email'] }}</dd></div>
            <div><dt class="text-slate-400">Contraseña temporal</dt><dd class="font-mono text-base">{{ $credentials['password'] }}</dd></div>
        </dl>
        <p class="mt-3 text-xs text-slate-400">Anótala o envíala ahora: por seguridad no se volverá a mostrar.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            @if ($whatsapp)
                <x-button :href="$whatsapp" target="_blank" rel="noopener" icon="send">Enviar por WhatsApp</x-button>
            @endif
            <x-button type="button" variant="secondary" icon="copy"
                x-on:click="await navigator.clipboard.writeText(message); copied = true; setTimeout(() => copied = false, 2000)">
                <span x-text="copied ? '¡Copiado!' : 'Copiar mensaje'">Copiar mensaje</span>
            </x-button>
        </div>
    </div>
@endif
