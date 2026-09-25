<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    /**
     * Si la membresía actual sigue vigente, la nueva empieza al día siguiente de que termine,
     * así el cliente no pierde días por renovar antes.
     */
    public function nextStartDate(User $client): CarbonImmutable
    {
        $current = $client->latestMembership()->first();

        return $current && $current->ends_on->gte(today())
            ? CarbonImmutable::parse($current->ends_on)->addDay()
            : CarbonImmutable::today();
    }

    public function renew(User $client, Plan $plan, PaymentMethod $method, User $by, ?string $notes = null): Membership
    {
        return DB::transaction(function () use ($client, $plan, $method, $by, $notes) {
            // Bloquea al cliente para que un doble clic no genere dos renovaciones con las mismas fechas.
            User::whereKey($client->id)->lockForUpdate()->first();
            $startsOn = $this->nextStartDate($client);

            return $client->memberships()->create([
                'plan_id' => $plan->id,
                'starts_on' => $startsOn,
                'ends_on' => $startsOn->addMonthsNoOverflow($plan->duration_months)->subDay(),
                'price' => $plan->price,
                'payment_method' => $method,
                'notes' => $notes,
                'created_by' => $by->id,
            ]);
        });
    }
}
