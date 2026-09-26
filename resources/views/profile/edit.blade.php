@php $layout = $user->isAdmin() ? 'layouts.admin' : 'layouts.client'; @endphp
<x-dynamic-component :component="$layout" title="Mi perfil">
    <h1 class="mb-5 text-2xl font-bold tracking-tight text-slate-900">Mi perfil</h1>

    <div class="mx-auto max-w-2xl space-y-5">
        <x-card title="Mis datos">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf @method('PUT')
                <div class="flex items-center gap-4" x-data="{ preview: null }">
                    <template x-if="preview"><img :src="preview" alt="" class="size-20 rounded-full object-cover"></template>
                    <x-avatar :user="$user" size="xl" x-show="!preview" />
                    <label class="cursor-pointer">
                        <span class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-white px-4 text-sm font-semibold text-slate-800 ring-1 ring-slate-300 hover:bg-slate-50"><x-icon name="camera" class="size-5" /> Cambiar foto</span>
                        <input type="file" name="avatar" accept="image/*" class="sr-only" x-on:change="preview = URL.createObjectURL($event.target.files[0])">
                    </label>
                    <x-input-error bag="profile" name="avatar" />
                </div>
                <x-input bag="profile" name="name" label="Nombre" :value="$user->name" autocomplete="name" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input bag="profile" name="email" type="email" label="Correo" :value="$user->email" autocomplete="email" />
                    <x-input bag="profile" name="phone" type="tel" label="Teléfono" :value="$user->phone" autocomplete="tel" />
                </div>
                @if ($user->isClient())
                    <x-choice-chips name="goal" label="Mi objetivo" :value="$user->goal" :options="\App\Enums\Goal::options()"
                        :icons="collect(\App\Enums\Goal::cases())->mapWithKeys(fn ($g) => [$g->value => $g->icon()])->all()" />
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-input bag="profile" name="birth_date" type="date" label="Fecha de nacimiento" :value="$user->birth_date?->toDateString()" />
                        <x-input bag="profile" name="height_cm" type="number" step="0.1" label="Estatura" suffix="cm" :value="$user->height_cm" inputmode="decimal" />
                    </div>
                @endif
                <x-button icon="check">Guardar</x-button>
            </form>
        </x-card>

        <x-card title="Cambiar contraseña">
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
                @csrf @method('PUT')
                <x-input bag="password" name="current_password" type="password" label="Contraseña actual" autocomplete="current-password" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input bag="password" name="password" type="password" label="Nueva contraseña" autocomplete="new-password" hint="Mínimo 8 caracteres, con letras y números." />
                    <x-input bag="password" name="password_confirmation" type="password" label="Repítela" autocomplete="new-password" />
                </div>
                <x-button variant="dark" icon="check">Cambiar contraseña</x-button>
            </form>
        </x-card>
    </div>
</x-dynamic-component>
