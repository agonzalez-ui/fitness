@props(['title' => null])
@php
    $user = auth()->user();
    $unread = $user->unreadMessagesCount();
    $nav = [
        ['admin.dashboard', 'Inicio', 'home', 'admin.dashboard'],
        ['admin.clients.index', 'Clientes', 'users', 'admin.clients.*'],
        ['admin.memberships.index', 'Membresías', 'card', 'admin.memberships.*'],
        ['admin.workouts.index', 'Rutinas', 'dumbbell', 'admin.workouts.*'],
        ['admin.nutrition.index', 'Nutrición', 'apple', 'admin.nutrition.*'],
        ['admin.messages.index', 'Mensajes', 'chat', 'admin.messages.*'],
        ['admin.catalog.index', 'Mantenimientos', 'book', 'admin.catalog.*'],
    ];
@endphp
<x-layouts.base :title="$title">
    <div x-data="{ menu: false }" class="min-h-full">
        {{-- Barra superior móvil --}}
        <div class="sticky top-0 z-30 flex items-center justify-between bg-slate-900 px-4 py-3 lg:hidden">
            <a href="{{ route('admin.dashboard') }}"><x-logo dark /></a>
            <button type="button" x-on:click="menu = true" class="relative rounded-lg p-2 text-slate-200 hover:bg-slate-800" aria-label="Abrir menú">
                <x-icon name="menu" class="size-6" />
                @if ($unread)<span class="absolute right-1 top-1 size-2.5 rounded-full bg-brand-500"></span>@endif
            </button>
        </div>

        {{-- Fondo del menú móvil --}}
        <div x-cloak x-show="menu" x-transition.opacity x-on:click="menu = false" class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"></div>

        <aside x-cloak :class="menu ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-900 px-4 pb-4 pt-5 transition-transform lg:translate-x-0">
            <div class="mb-8 flex items-center justify-between px-2">
                <a href="{{ route('admin.dashboard') }}"><x-logo dark /></a>
                <button type="button" x-on:click="menu = false" class="rounded-lg p-1 text-slate-400 hover:text-white lg:hidden" aria-label="Cerrar menú"><x-icon name="x" class="size-6" /></button>
            </div>

            <nav class="flex-1 space-y-1" aria-label="Principal">
                @foreach ($nav as [$route, $label, $icon, $pattern])
                    @continue(! Route::has($route))
                    @php $current = request()->routeIs($pattern) || ($route === 'admin.catalog.index' && request()->routeIs('admin.users.*')); @endphp
                    <a href="{{ route($route) }}" @if ($current) aria-current="page" @endif
                        @class([
                            'flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition',
                            'bg-slate-800 text-white' => $current,
                            'text-slate-300 hover:bg-slate-800/60 hover:text-white' => ! $current,
                        ])>
                        <x-icon :name="$icon" @class(['size-5', 'text-brand-400' => $current]) />
                        <span class="flex-1">{{ $label }}</span>
                        @if ($route === 'admin.messages.index' && $unread)
                            <span class="rounded-full bg-brand-600 px-2 py-0.5 text-xs font-bold text-white">{{ $unread }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="mt-4 border-t border-slate-800 pt-4">
                <div class="flex items-center gap-3 px-2">
                    <a href="{{ route('profile.edit') }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-xl p-1 hover:bg-slate-800" title="Mi perfil">
                        <x-avatar :user="$user" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                            <p class="truncate text-xs text-slate-400">{{ $user->role->label() }}</p>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg p-2 text-slate-400 hover:bg-slate-800 hover:text-white" aria-label="Cerrar sesión" title="Cerrar sesión"><x-icon name="logout" /></button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="lg:pl-72">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
                <x-flash />
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layouts.base>
