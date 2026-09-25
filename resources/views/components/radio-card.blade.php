@props(['name', 'value', 'checked' => false, 'title', 'meta' => null, 'aside' => null])
<label class="relative block cursor-pointer">
    <input type="radio" name="{{ $name }}" value="{{ $value }}" class="peer sr-only" @checked($checked) {{ $attributes }}>
    <span class="flex h-full items-center gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-300 transition peer-checked:bg-brand-50 peer-checked:ring-2 peer-checked:ring-brand-600 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand-600 hover:bg-slate-50">
        <span class="min-w-0 flex-1">
            <span class="block font-semibold text-slate-900">{{ $title }}</span>
            @if ($meta)<span class="mt-0.5 block text-sm text-slate-500">{{ $meta }}</span>@endif
        </span>
        @if ($aside)<span class="shrink-0 text-right font-bold text-slate-900">{{ $aside }}</span>@endif
    </span>
</label>
