@props(['name', 'label' => null, 'columns' => 'sm:grid-cols-2'])
{{-- Contenedor de <x-radio-card> con su leyenda y el error del campo. --}}
<fieldset {{ $attributes->only('class') }}>
    @if ($label)
        <legend class="mb-2 block text-sm font-medium text-slate-700">{{ $label }}</legend>
    @endif
    <div class="grid gap-2 {{ $columns }}">
        {{ $slot }}
    </div>
    <x-input-error :name="$name" />
</fieldset>
