@props(['color' => 'slate'])
@php
$colors = [
    'slate' => 'bg-slate-100 text-slate-700',
    'green' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/20',
    'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
    'red' => 'bg-red-50 text-red-700 ring-red-600/20',
    'brand' => 'bg-brand-50 text-brand-800 ring-brand-600/20',
    'blue' => 'bg-sky-50 text-sky-800 ring-sky-600/20',
];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ring-transparent '.($colors[$color] ?? $colors['slate'])]) }}>{{ $slot }}</span>
