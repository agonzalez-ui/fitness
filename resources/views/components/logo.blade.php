@props(['dark' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 font-extrabold tracking-tight']) }}>
    <span class="flex size-9 items-center justify-center rounded-xl bg-brand-600 text-white"><x-icon name="dumbbell" class="size-5" /></span>
    <span @class(['text-lg', 'text-white' => $dark, 'text-slate-900' => ! $dark])>GU <span class="text-brand-500">FITCR</span></span>
</span>
