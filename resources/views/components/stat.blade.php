@props(['label', 'value', 'icon' => null, 'hint' => null, 'href' => null, 'tone' => 'slate'])
@php
$tones = [
    'slate' => 'bg-slate-100 text-slate-600',
    'brand' => 'bg-brand-100 text-brand-700',
    'green' => 'bg-emerald-100 text-emerald-700',
    'amber' => 'bg-amber-100 text-amber-700',
    'red' => 'bg-red-100 text-red-700',
];
$tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70'.($href ? ' transition hover:ring-brand-300' : '')]) }}>
    @if ($icon)
        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] }}"><x-icon :name="$icon" class="size-6" /></span>
    @endif
    <div class="min-w-0">
        <p class="text-sm text-slate-500">{{ $label }}</p>
        <p class="text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
        @if ($hint)<p class="truncate text-xs text-slate-500">{{ $hint }}</p>@endif
    </div>
</{{ $tag }}>
