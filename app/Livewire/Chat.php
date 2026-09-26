<?php

namespace App\Livewire;

use App\Enums\Role;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Conversación entre un cliente y el equipo de entrenadores.
 * El cliente solo puede abrir su propio hilo; cualquier entrenador puede abrir cualquiera.
 */
class Chat extends Component
{
    #[Locked]
    public int $clientId;

    #[Validate('required|string|max:2000', message: ['required' => 'Escribe un mensaje.'])]
    public string $body = '';

    public function mount(User $client): void
    {
        $this->clientId = $client->id;
        $this->authorizeThread();
    }

    public function boot(): void
    {
        if (isset($this->clientId)) {
            $this->authorizeThread();
        }
    }

    #[Computed]
    public function client(): User
    {
        return User::clients()->findOrFail($this->clientId);
    }

    #[Computed]
    public function messages(): Collection
    {
        return Message::thread($this->client)->with('sender')->latest('id')->take(100)->get()->reverse()->values();
    }

    public function send(): void
    {
        $this->body = trim($this->body);
        $this->validate();

        $viewer = auth()->user();
        Message::create([
            'sender_id' => $viewer->id,
            'recipient_id' => $viewer->isAdmin() ? $this->clientId : $this->coachFor($this->client)->id,
            'body' => $this->body,
        ]);

        $this->reset('body');
        unset($this->messages);
        $this->dispatch('message-sent');
    }

    public function render()
    {
        $this->markAsRead();

        return view('livewire.chat', ['viewer' => auth()->user()]);
    }

    /** El entrenador que le escribió por última vez; si ninguno, el primer entrenador activo. */
    private function coachFor(User $client): User
    {
        $lastCoachId = Message::where('recipient_id', $client->id)->latest('id')->value('sender_id');

        return User::where('role', Role::Admin)->active()->find($lastCoachId)
            ?? User::where('role', Role::Admin)->active()->orderBy('id')->firstOrFail();
    }

    private function markAsRead(): void
    {
        $viewer = auth()->user();
        $query = Message::thread($this->client)->whereNull('read_at');

        $viewer->isAdmin()
            ? $query->where('sender_id', $this->clientId)
            : $query->where('recipient_id', $this->clientId);

        if ($query->update(['read_at' => now()])) {
            unset($this->messages);
            $this->dispatch('messages-read');
        }
    }

    private function authorizeThread(): void
    {
        $viewer = auth()->user();
        abort_unless($viewer && ($viewer->isAdmin() || $viewer->id === $this->clientId), 403);
    }
}
