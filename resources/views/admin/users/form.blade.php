<x-layouts.admin :title="$user->exists ? 'Editar usuario' : 'Nuevo usuario'">
    <x-page-header :title="$user->exists ? 'Editar usuario' : 'Nuevo usuario'" :back="route('admin.users.index')" />
    <div class="mx-auto max-w-2xl">
        <x-credentials-notice :user="$user" />
        <x-card>
            <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-5">
                @csrf
                @if ($user->exists) @method('PUT') @endif
                <x-input name="name" label="Nombre completo" :value="$user->name" required maxlength="100" />
                <x-input name="email" label="Correo" type="email" :value="$user->email" required maxlength="150" />
                <x-input name="phone" label="Teléfono (opcional)" :value="$user->phone" maxlength="30" />
                <x-select name="role" label="Rol" :options="['client' => 'Cliente', 'admin' => 'Entrenador']" :value="$user->role ?? 'client'" hint="El entrenador administra el sistema. El cliente solo ve sus planes y su progreso." required />
                @unless ($user->exists)<p class="text-sm text-slate-500">Al guardar se genera una contraseña temporal. Se mostrará una sola vez.</p>@endunless
                <div class="flex justify-end gap-2">
                    <x-button :href="route('admin.users.index')" variant="ghost">Cancelar</x-button>
                    <x-button icon="check">Guardar usuario</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.admin>
