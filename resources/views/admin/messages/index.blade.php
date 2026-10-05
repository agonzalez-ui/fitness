<x-layouts.admin :title="$client ? 'Mensajes · '.$client->name : 'Mensajes'">
    <div class="-mx-4 -my-6 flex h-[calc(100dvh-3.75rem)] overflow-hidden bg-white sm:-mx-6 lg:-mx-8 lg:-my-10 lg:h-dvh">
        {{-- Lista de conversaciones --}}
        <aside @class(['w-full shrink-0 flex-col border-r border-slate-200 md:flex md:w-80', 'hidden' => $client, 'flex' => ! $client])>
            <div class="border-b border-slate-200 p-4">
                <h1 class="text-xl font-bold text-slate-900">Mensajes</h1>
                <form method="GET" class="relative mt-3" role="search">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <label for="q" class="sr-only">Buscar cliente</label>
                    <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar cliente…" class="block min-h-10 w-full rounded-xl border-0 bg-slate-100 pl-9 pr-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-600">
                </form>
            </div>
            <ul class="flex-1 overflow-y-auto">
                @forelse ($conversations as $item)
                    @php $active = $client?->id === $item->id; @endphp
                    <li>
                        <a href="{{ route('admin.messages.show', [$item, 'q' => request('q'), 'conversaciones' => request('conversaciones')]) }}" @if ($active) aria-current="page" @endif
                            @class(['flex items-center gap-3 px-4 py-3', 'bg-brand-50' => $active, 'hover:bg-slate-50' => ! $active])>
                            <x-avatar :user="$item" />
                            <div class="min-w-0 flex-1">
                                <p class="flex items-center gap-2">
                                    <span @class(['truncate text-sm', 'font-bold text-slate-900' => $item->unread_count, 'font-medium text-slate-800' => ! $item->unread_count])>{{ $item->name }}</span>
                                    @if ($item->last_message)<span class="ml-auto shrink-0 text-xs text-slate-400">{{ $item->last_message->created_at->diffForHumans(short: true) }}</span>@endif
                                </p>
                                <p @class(['truncate text-sm', 'text-slate-900' => $item->unread_count, 'text-slate-500' => ! $item->unread_count])>
                                    {{ $item->last_message ? ($item->last_message->sender_id === $item->id ? '' : 'Tú: ').$item->last_message->body : 'Sin mensajes' }}
                                </p>
                            </div>
                            @if ($item->unread_count)
                                <span class="rounded-full bg-brand-600 px-2 py-0.5 text-xs font-bold text-white">{{ $item->unread_count }}</span>
                            @endif
                        </a>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-slate-500">No hay clientes que coincidan.</li>
                @endforelse
            </ul>
            @if ($conversations->hasPages())<div class="border-t border-slate-200 p-3">{{ $conversations->links('pagination::simple-tailwind') }}</div>@endif
        </aside>

        {{-- Conversación --}}
        <section @class(['min-w-0 flex-1 flex-col bg-slate-50 md:flex', 'flex' => $client, 'hidden' => ! $client])>
            @if ($client)
                <header class="flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3">
                    <a href="{{ route('admin.messages.index') }}" class="-ml-1 rounded-lg p-1 text-slate-500 md:hidden" aria-label="Volver a conversaciones"><x-icon name="arrow-left" /></a>
                    <x-avatar :user="$client" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-slate-900">{{ $client->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $client->last_seen_at ? 'Visto '.$client->last_seen_at->diffForHumans() : $client->email }}</p>
                    </div>
                    <x-button :href="route('admin.clients.show', $client)" size="sm" variant="ghost">Ver ficha</x-button>
                </header>
                <div class="min-h-0 flex-1">
                    <livewire:chat :client="$client" :key="'chat-'.$client->id" />
                </div>
            @else
                <div class="flex flex-1 items-center justify-center text-center text-slate-500">
                    <div>
                        <x-icon name="chat" class="mx-auto size-12 text-slate-300" />
                        <p class="mt-2">Elige un cliente para ver la conversación.</p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-layouts.admin>
