@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'rows' => 3])
@php($id = $attributes->get('id', $name))
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
        {{ $attributes->except(['class', 'id'])->merge([
            'class' => 'block w-full rounded-xl border-0 bg-white px-4 py-3 text-base text-slate-900 ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm '
                .($errors->has($name) ? 'ring-red-400' : 'ring-slate-300'),
        ]) }}>{{ old($name, $value) }}</textarea>
    @if ($hint && ! $errors->has($name))
        <p class="mt-1.5 text-sm text-slate-500">{{ $hint }}</p>
    @endif
    <x-input-error :name="$name" />
</div>
