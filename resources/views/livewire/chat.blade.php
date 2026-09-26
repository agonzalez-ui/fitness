<div wire:poll.8s.visible class="flex h-full min-h-0 flex-col"
    x-data="{ scroll() { this.$nextTick(() => this.$refs.list.scrollTop = this.$refs.list.scrollHeight) } }"
    x-init="scroll()" x-on:message-sent="scroll()">
    <div x-ref="list" class="flex-1 space-y-1 overflow-y-auto px-4 py-4" aria-live="polite">
        @php $lastDate = null; @endphp
        @forelse ($this->messages as $message)
            @php
                $mine = $viewer->isAdmin() ? $message->sender->isAdmin() : $message->sender_id === $viewer->id;
                $date = $message->created_at->toDateString();
            @endphp
            @if ($date !== $lastDate)
                <p class="py-3 text-center text-xs font-medium text-slate-400">
                    {{ $message->created_at->isToday() ? 'Hoy' : ($message->created_at->isYesterday() ? 'Ayer' : $message->created_at->translatedFormat('j \d\e F Y')) }}
                </p>
                @php $lastDate = $date; @endphp
            @endif
            <div wire:key="msg-{{ $message->id }}" @class(['flex', 'justify-end' => $mine])>
                <div @class([
                    'max-w-[80%] rounded-2xl px-4 py-2 text-[15px] leading-snug shadow-sm',
                    'rounded-br-md bg-brand-700 text-white' => $mine,
                    'rounded-bl-md bg-white text-slate-900 ring-1 ring-slate-200' => ! $mine,
                ])>
                    @if ($viewer->isAdmin() && $mine && $message->sender_id !== $viewer->id)
                        <p class="mb-0.5 text-xs font-semibold text-brand-100">{{ $message->sender->first_name }}</p>
                    @endif
                    <p class="whitespace-pre-line break-words">{{ $message->body }}</p>
                    <p @class(['mt-1 text-right text-[11px]', 'text-brand-100' => $mine, 'text-slate-400' => ! $mine])>
                        {{ $message->created_at->format('g:i a') }}
                        @if ($mine)<span aria-label="{{ $message->read_at ? 'Leído' : 'Enviado' }}">{{ $message->read_at ? '✓✓' : '✓' }}</span>@endif
                    </p>
                </div>
            </div>
        @empty
            <div class="flex h-full flex-col items-center justify-center py-12 text-center text-slate-500">
                <x-icon name="chat" class="size-10 text-slate-300" />
                <p class="mt-2 font-medium text-slate-700">Inicien la conversación</p>
                <p class="text-sm">{{ $viewer->isAdmin() ? 'Escríbele a '.$this->client->first_name.' para motivarlo o resolver dudas.' : 'Pregúntale lo que quieras a tu entrenador.' }}</p>
            </div>
        @endforelse
    </div>

    <form wire:submit="send" class="border-t border-slate-200 bg-white p-3">
        @unless ($viewer->isAdmin())
            @if ($this->messages->isEmpty())
                <div class="mb-2 flex gap-2 overflow-x-auto pb-1">
                    @foreach (['Tengo una duda con un ejercicio', '¿Puedo cambiar un alimento?', 'Hoy no pude entrenar'] as $quick)
                        <button type="button" wire:click="$set('body', @js($quick))" class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-200">{{ $quick }}</button>
                    @endforeach
                </div>
            @endif
        @endunless
        <div class="flex items-end gap-2">
            <label for="chat-body" class="sr-only">Mensaje</label>
            <textarea id="chat-body" wire:model="body" rows="1" placeholder="Escribe un mensaje…"
                x-on:keydown.enter="if (!$event.shiftKey && window.matchMedia('(min-width: 768px)').matches) { $event.preventDefault(); $wire.send() }"
                x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 140) + 'px'"
                class="block max-h-36 min-h-11 flex-1 resize-none rounded-2xl border-0 bg-slate-100 px-4 py-2.5 text-[15px] focus:bg-white focus:ring-2 focus:ring-brand-600"></textarea>
            <button type="submit" wire:loading.attr="disabled" class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand-700 text-white hover:bg-brand-800 disabled:opacity-50" aria-label="Enviar">
                <x-icon name="send" class="size-5" />
            </button>
        </div>
        <x-input-error name="body" />
    </form>
</div>
