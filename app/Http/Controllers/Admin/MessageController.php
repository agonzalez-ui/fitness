<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.messages.index', ['conversations' => $this->conversations($request->query('q')), 'client' => null]);
    }

    public function show(Request $request, User $client): View
    {
        return view('admin.messages.index', ['conversations' => $this->conversations($request->query('q')), 'client' => $client]);
    }

    /** Clientes con su último mensaje y cuántos sin leer; primero los que tienen conversación reciente. */
    private function conversations(?string $search): Collection
    {
        $clients = User::clients()->active()->search($search)->get(['id', 'name', 'avatar_path']);

        $last = Message::query()
            ->selectRaw('case when s.role = ? then messages.sender_id else messages.recipient_id end as client_id, max(messages.id) as last_id', [Role::Client->value])
            ->join('users as s', 's.id', '=', 'messages.sender_id')
            ->groupBy('client_id')
            ->pluck('last_id', 'client_id');

        $messages = Message::whereIn('id', $last->values())->get()->keyBy('id');
        $unread = Message::unreadByCoaches()->selectRaw('sender_id, count(*) as total')->groupBy('sender_id')->pluck('total', 'sender_id');

        return $clients->map(function (User $client) use ($last, $messages, $unread) {
            $client->setAttribute('last_message', isset($last[$client->id]) ? $messages[$last[$client->id]] : null);
            $client->setAttribute('unread_count', (int) ($unread[$client->id] ?? 0));

            return $client;
        })->sortByDesc(fn (User $c) => [$c->unread_count > 0, $c->last_message?->id ?? 0])->values();
    }
}
