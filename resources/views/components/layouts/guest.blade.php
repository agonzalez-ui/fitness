@props(['title' => null, 'heading' => null, 'subheading' => null])
<x-layouts.base :title="$title" class="bg-slate-900">
    <div class="flex min-h-full">
        <div class="relative hidden flex-1 overflow-hidden bg-slate-900 lg:block">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,var(--color-brand-600)_0%,transparent_60%)] opacity-40"></div>
            <div class="relative flex h-full flex-col justify-between p-12">
                <x-logo dark />
                <div class="max-w-md">
                    <p class="text-4xl font-extrabold leading-tight text-white">Tu entrenamiento, tu nutrición y tu progreso en un solo lugar.</p>
                    <p class="mt-4 text-lg text-slate-300">Con seguimiento directo de tu entrenador.</p>
                </div>
                <p class="text-sm text-slate-500">© {{ date('Y') }} GU FITCR</p>
            </div>
        </div>
        <div class="flex flex-1 flex-col justify-center bg-white px-6 py-12 sm:px-12 lg:max-w-xl lg:flex-none lg:px-16">
            <div class="mx-auto w-full max-w-sm">
                <a href="{{ url('/') }}" class="lg:hidden"><x-logo /></a>
                @if ($heading)
                    <h1 class="mt-8 text-2xl font-bold tracking-tight text-slate-900 lg:mt-0">{{ $heading }}</h1>
                @endif
                @if ($subheading)<p class="mt-2 text-slate-500">{{ $subheading }}</p>@endif
                <div class="mt-8">{{ $slot }}</div>
            </div>
        </div>
    </div>
</x-layouts.base>
