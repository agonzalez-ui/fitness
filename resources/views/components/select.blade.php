@props(['bag' => 'default', 'useOld' => true, 'name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null])
@php
    $id = $attributes->get('id', $name);
    $plain = $value instanceof \BackedEnum ? $value->value : $value;
    $selected = $useOld ? old($name, $plain) : $plain;
@endphp
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif
    <select id="{{ $id }}" name="{{ $name }}"
        {{ $attributes->except(['class', 'id'])->merge([
            'class' => 'block min-h-12 w-full rounded-xl border-0 bg-white px-4 text-base text-slate-900 ring-1 ring-inset focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm '
                .($errors->getBag($bag)->has($name) ? 'ring-red-400' : 'ring-slate-300'),
        ]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint && ! $errors->getBag($bag)->has($name))
        <p class="mt-1.5 text-sm text-slate-500">{{ $hint }}</p>
    @endif
    <x-input-error :bag="$bag" :name="$name" />
</div>
