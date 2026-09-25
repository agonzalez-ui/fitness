@props(['icon' => 'sparkles', 'title', 'description' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 px-6 py-10 text-center']) }}>
    <span class="flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-500"><x-icon :name="$icon" class="size-6" /></span>
    <h3 class="mt-3 font-semibold text-slate-900">{{ $title }}</h3>
    @if ($description)<p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>@endif
    @if ($slot->isNotEmpty())<div class="mt-5 flex flex-wrap justify-center gap-2">{{ $slot }}</div>@endif
</div>
