@php $client ??= null; @endphp
<div class="grid gap-5 sm:grid-cols-2">
    <x-input name="name" label="Nombre completo" :value="$client?->name" required autofocus class="sm:col-span-2" />
    <x-input name="email" type="email" label="Correo" :value="$client?->email" inputmode="email" required />
    <x-input name="phone" type="tel" label="Teléfono / WhatsApp" :value="$client?->phone" inputmode="tel" placeholder="8888-8888" hint="Para enviarle sus datos de acceso por WhatsApp." />
</div>

<x-choice-chips name="goal" label="Objetivo" :value="$client?->goal" :options="\App\Enums\Goal::options()"
    :icons="collect(\App\Enums\Goal::cases())->mapWithKeys(fn ($g) => [$g->value => $g->icon()])->all()" class="mt-5" />

<details class="group mt-5 rounded-xl bg-slate-50 p-4" @if ($client?->birth_date || $client?->height_cm || $errors->hasAny(['birth_date', 'height_cm'])) open @endif>
    <summary class="cursor-pointer select-none text-sm font-semibold text-slate-700">Más datos (opcional)</summary>
    <div class="mt-4 grid gap-5 sm:grid-cols-3">
        <x-input name="birth_date" type="date" label="Fecha de nacimiento" :value="$client?->birth_date?->toDateString()" />
        <x-input name="height_cm" type="number" step="0.1" label="Estatura" suffix="cm" :value="$client?->height_cm" inputmode="decimal" />
        <x-select name="gender" label="Género" :value="$client?->gender" placeholder="Prefiero no decir" :options="['female' => 'Mujer', 'male' => 'Hombre', 'other' => 'Otro']" />
    </div>
    <x-textarea name="trainer_notes" label="Notas privadas del entrenador" :value="$client?->trainer_notes" hint="Lesiones, horarios, preferencias. El cliente no las ve." class="mt-5" />
</details>
