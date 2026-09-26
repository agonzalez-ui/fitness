<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sender_id', 'recipient_id', 'body', 'read_at'])]
class Message extends Model
{
    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /**
     * Conversación de un cliente con el equipo de entrenadores: todo lo que el cliente envió o recibió.
     * Así cualquier cuenta de entrenador ve el hilo completo.
     */
    #[Scope]
    protected function thread(Builder $query, User $client): void
    {
        $query->where(fn ($q) => $q->where('sender_id', $client->id)->orWhere('recipient_id', $client->id));
    }

    /** Mensajes de clientes que ningún entrenador ha leído. */
    #[Scope]
    protected function unreadByCoaches(Builder $query): void
    {
        $query->whereNull('read_at')->whereHas('sender', fn ($q) => $q->where('role', Role::Client));
    }

    /** Mensajes de entrenadores que el cliente no ha leído. */
    #[Scope]
    protected function unreadByClient(Builder $query, User $client): void
    {
        $query->whereNull('read_at')->where('recipient_id', $client->id);
    }
}
