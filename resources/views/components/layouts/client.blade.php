@props(['title' => null])
@php
    $user = auth()->user();
    $unread = $user->unreadMessagesCount();
    $nav = [
        ['client.dashboard', 'Hoy', 'home', 'client.dashboard'],
        ['client.workout', 'Rutina', 'dumbbell', 'client.workout*'],
        ['client.nutrition', 'Comidas', 'apple', 'client.nutrition*'],
        ['client.progress', 'Progreso', 'chart', 'client.progress*'],
        ['client.messages', 'Coach', 'chat', 'client.messages*'],
    ];
@endphp
<x-layouts.base :title="$title">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('client.dashboard') }}"><x-logo /></a>
            <nav class="hidden items-center gap-1 md:flex" aria-label="Principal en escritorio">
                @foreach ($nav as [$route, $label, $icon, $pattern])
                    <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                        @class(['flex min-h-11 items-center gap-2 rounded-xl px-3 text-sm font-semibold transition', 'bg-brand-50 text-brand-700' => request()->routeIs($pattern), 'text-slate-600 hover:bg-slate-100' => ! request()->routeIs($pattern)])>
                        <x-icon :name="$icon" class="size-5" /> {{ $label }}
                        @if ($route === 'client.messages' && $unread)<span class="rounded-full bg-brand-600 px-1.5 text-xs text-white">{{ $unread }}</span>@endif
                    </a>
                @endforeach
            </nav>
            <div class="flex items-center gap-1">
                @if (Route::has('profile.edit'))
                    <a href="{{ route('profile.edit') }}" class="rounded-full p-1 hover:bg-slate-100" aria-label="Mi perfil"><x-avatar :user="$user" size="sm" /></a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Cerrar sesión" title="Cerrar sesión"><x-icon name="logout" /></button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 pb-28 pt-6 sm:px-6 md:pb-10 lg:px-8 lg:pt-8">
        <x-flash />
        {{ $slot }}
    </main>

    {{-- Navegación inferior tipo app: al alcance del pulgar --}}
    <nav class="pb-safe fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white md:hidden" aria-label="Principal">
        <div class="mx-auto grid max-w-3xl grid-cols-5">
            @foreach ($nav as [$route, $label, $icon, $pattern])
                @php $current = request()->routeIs($pattern); @endphp
                <a href="{{ Route::has($route) ? route($route) : '#' }}" @if ($current) aria-current="page" @endif
                    @class(['relative flex flex-col items-center gap-1 pt-2.5 text-xs font-medium', 'text-brand-700' => $current, 'text-slate-500 hover:text-slate-900' => ! $current])>
                    <x-icon :name="$icon" class="size-6" />
                    {{ $label }}
                    @if ($route === 'client.messages' && $unread)
                        <span class="absolute right-1/2 top-1.5 -mr-5 rounded-full bg-brand-600 px-1.5 text-[10px] font-bold text-white">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>
</x-layouts.base>
