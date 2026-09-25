<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RenewMembershipRequest;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;

class ClientMembershipController extends Controller
{
    public function store(RenewMembershipRequest $request, User $client, MembershipService $service): RedirectResponse
    {
        $membership = $service->renew(
            $client,
            Plan::findOrFail($request->integer('plan_id')),
            PaymentMethod::from($request->input('payment_method')),
            $request->user(),
            $request->input('notes'),
        );

        return redirect()->route('admin.clients.show', $client)->with('success',
            "Membresía renovada: {$membership->plan->name}, del {$membership->starts_on->translatedFormat('j M')} al {$membership->ends_on->translatedFormat('j M Y')}.");
    }

    /** Para corregir un registro hecho por error. */
    public function destroy(User $client, Membership $membership): RedirectResponse
    {
        abort_unless($membership->user_id === $client->id, 404);
        $membership->delete();

        return back()->with('success', 'Pago eliminado del historial.');
    }
}
