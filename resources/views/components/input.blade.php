@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'suffix' => null])
@php($id = $attributes->get('id', $name))
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif
    <div class="relative">
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
            @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
            {{ $attributes->except(['class', 'id'])->merge([
                'class' => 'block min-h-12 w-full rounded-xl border-0 bg-white px-4 text-base text-slate-900 ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm '
                    .($errors->has($name) ? 'ring-red-400' : 'ring-slate-300').($suffix ? ' pr-14' : ''),
            ]) }}
            @error($name) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror>
        @if ($suffix)
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-sm text-slate-500">{{ $suffix }}</span>
        @endif
    </div>
    @if ($hint && ! $errors->has($name))
        <p class="mt-1.5 text-sm text-slate-500">{{ $hint }}</p>
    @endif
    <x-input-error :name="$name" :id="$id.'-error'" />
</div>
