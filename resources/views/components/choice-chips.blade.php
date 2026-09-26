@props(['name', 'label' => null, 'options' => [], 'value' => null, 'icons' => []])
{{-- Opciones como botones grandes en vez de un select: un toque y listo. --}}
@php $selected = old($name, $value instanceof \BackedEnum ? $value->value : $value); @endphp
<fieldset {{ $attributes->only('class') }}>
    @if ($label)
        <legend class="mb-2 block text-sm font-medium text-slate-700">{{ $label }}</legend>
    @endif
    <div class="flex flex-wrap gap-2">
        @foreach ($options as $optionValue => $optionLabel)
            <label class="cursor-pointer">
                <input type="radio" name="{{ $name }}" value="{{ $optionValue }}" class="peer sr-only" @checked((string) $selected === (string) $optionValue) {{ $attributes->except('class') }}>
                <span class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-4 text-sm font-medium text-slate-700 ring-1 ring-slate-300 transition peer-checked:bg-brand-50 peer-checked:text-brand-800 peer-checked:ring-2 peer-checked:ring-brand-600 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-600 hover:bg-slate-50">
                    @isset($icons[$optionValue])<span aria-hidden="true">{{ $icons[$optionValue] }}</span>@endisset
                    {{ $optionLabel }}
                </span>
            </label>
        @endforeach
    </div>
    <x-input-error :name="$name" />
</fieldset>
