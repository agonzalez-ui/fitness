@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'icon' => null, 'type' => 'submit'])
@php
$variants = [
    'primary' => 'bg-brand-700 text-white hover:bg-brand-800 focus-visible:outline-brand-700',
    'dark' => 'bg-slate-900 text-white hover:bg-slate-800 focus-visible:outline-slate-900',
    'secondary' => 'bg-white text-slate-800 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus-visible:outline-slate-400',
    'soft' => 'bg-brand-50 text-brand-800 hover:bg-brand-100 focus-visible:outline-brand-600',
    'danger' => 'bg-red-50 text-red-700 hover:bg-red-100 focus-visible:outline-red-600',
    'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-slate-400',
];
$sizes = [
    'sm' => 'min-h-9 gap-1.5 rounded-lg px-3 text-sm',
    'md' => 'min-h-11 gap-2 rounded-xl px-4 text-sm',
    'lg' => 'min-h-14 gap-2 rounded-2xl px-6 text-base',
];
$classes = 'inline-flex items-center justify-center font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50 '.$variants[$variant].' '.$sizes[$size];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" class="size-5 shrink-0" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" class="size-5 shrink-0" />@endif
        {{ $slot }}
    </button>
@endif
