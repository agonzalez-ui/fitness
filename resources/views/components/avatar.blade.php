@props(['user', 'size' => 'md'])
@php
$sizes = ['sm' => 'size-8 text-xs', 'md' => 'size-10 text-sm', 'lg' => 'size-14 text-lg', 'xl' => 'size-20 text-2xl'];
// Color estable por usuario para distinguirlos rápido en listas.
$palette = ['bg-brand-100 text-brand-800', 'bg-sky-100 text-sky-800', 'bg-emerald-100 text-emerald-800', 'bg-violet-100 text-violet-800', 'bg-rose-100 text-rose-800', 'bg-amber-100 text-amber-800'];
$tone = $palette[$user->id % count($palette)];
@endphp
@if ($user->avatar_url)
    <img src="{{ $user->avatar_url }}" alt="" {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover '.$sizes[$size]]) }}>
@else
    <span aria-hidden="true" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full font-semibold '.$sizes[$size].' '.$tone]) }}>{{ $user->initials }}</span>
@endif
