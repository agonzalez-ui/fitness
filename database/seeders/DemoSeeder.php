<?php

namespace Database\Seeders;

use App\Enums\Goal;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Models\Message;
use App\Models\NutritionPlan;
use App\Models\Plan;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\MembershipService;
use App\Services\PlanAssignmentService;
use Illuminate\Database\Seeder;

/**
 * Cuentas de prueba para desarrollo local (nunca en producción):
 *   entrenador: coach@demo.test / demo1234
 *   cliente:    cliente@demo.test / demo1234
 */
class DemoSeeder extends Seeder
{
    public function run(MembershipService $memberships, PlanAssignmentService $plans): void
    {
        if (app()->isProduction()) {
            return;
        }

        $coach = User::updateOrCreate(['email' => 'coach@demo.test'], [
            'name' => 'Coach Demo', 'password' => 'demo1234', 'role' => Role::Admin, 'email_verified_at' => now(),
        ]);

        $client = User::updateOrCreate(['email' => 'cliente@demo.test'], [
            'name' => 'Laura Demo', 'password' => 'demo1234', 'role' => Role::Client, 'goal' => Goal::WeightLoss,
            'phone' => '8888-0000', 'height_cm' => 165, 'email_verified_at' => now(),
        ]);

        if (! $client->memberships()->exists()) {
            $memberships->renew($client, Plan::firstWhere('duration_months', 3), PaymentMethod::Sinpe, $coach);
        }
        if (! $client->activeWorkoutPlan) {
            $plans->assignWorkout(WorkoutPlan::templates()->where('title', 'like', 'Full Body%')->firstOrFail(), $client, $coach);
        }
        if (! $client->activeNutritionPlan) {
            $plans->assignNutrition(NutritionPlan::templates()->where('title', 'like', 'Déficit%')->firstOrFail(), $client, $coach);
        }

        foreach ([56 => 78.4, 42 => 77.6, 28 => 76.9, 14 => 76.1, 0 => 75.3] as $daysAgo => $weight) {
            $client->progressEntries()->updateOrCreate(['recorded_on' => today()->subDays($daysAgo)], [
                'weight_kg' => $weight, 'waist_cm' => round(88 - (56 - $daysAgo) / 14, 1),
            ]);
        }

        if (! Message::thread($client)->exists()) {
            Message::create(['sender_id' => $coach->id, 'recipient_id' => $client->id, 'body' => '¡Bienvenida, Laura! Ya tienes tu rutina y tu plan de comidas.', 'read_at' => now(), 'created_at' => now()->subDays(3)]);
            Message::create(['sender_id' => $client->id, 'recipient_id' => $coach->id, 'body' => '¡Gracias! ¿Puedo cambiar la tilapia por pollo en la cena?', 'created_at' => now()->subHours(2)]);
        }
    }
}
