<?php

use App\Enums\PaymentMethod;
use App\Models\Message;
use App\Models\Plan;
use App\Models\WorkoutPlan;
use App\Services\MembershipService;
use App\Services\PlanAssignmentService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Carbon;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('shows the trainer what needs attention', function () {
    $coach = admin(['name' => 'Carlos Gonzalez']);
    $expiring = client(['name' => 'Juan Pérez']);
    $expiring->memberships()->create([
        'plan_id' => Plan::first()->id, 'starts_on' => today()->subMonth(), 'ends_on' => today()->addDays(3),
        'price' => 25, 'payment_method' => PaymentMethod::Sinpe,
    ]);
    client(['name' => 'Sin Plan']);
    Message::create(['sender_id' => $expiring->id, 'recipient_id' => $coach->id, 'body' => 'Coach, una duda']);

    $this->actingAs($coach)->get('/admin')
        ->assertOk()
        ->assertSee('Hola, Carlos')
        ->assertSeeInOrder(['Membresías que necesitan atención', 'Juan Pérez', 'Por vencer'])
        ->assertSee('Coach, una duda')
        ->assertSeeInOrder(['Clientes sin rutina asignada', 'Sin Plan']);
});

it('shows the client today\'s workout from their plan', function () {
    Carbon::setTestNow('2026-09-28 08:00'); // lunes
    $coach = admin();
    $client = client(['name' => 'María González']);
    $template = WorkoutPlan::templates()->where('title', 'like', 'Full Body%')->first();
    app(PlanAssignmentService::class)->assignWorkout($template, $client, $coach);
    app(MembershipService::class)->renew($client, Plan::first(), PaymentMethod::Cash, $coach);

    $this->actingAs($client)->get('/app')
        ->assertOk()
        ->assertSee('Hola, María')
        ->assertSee('Hoy te toca')
        ->assertSee('Día A')
        ->assertSee('Sentadilla goblet')
        ->assertDontSee('membresía venció');
});

it('shows a rest day and the next workout when nothing is scheduled today', function () {
    Carbon::setTestNow('2026-09-29 08:00'); // martes: el Full Body entrena lunes, miércoles y viernes
    $client = client();
    app(PlanAssignmentService::class)->assignWorkout(WorkoutPlan::templates()->where('title', 'like', 'Full Body%')->first(), $client, admin());

    $this->actingAs($client)->get('/app')
        ->assertSee('Día de descanso')
        ->assertSee('Próximo entrenamiento')
        ->assertSee('Día B');
});

it('tells new clients their plan is on the way', function () {
    $this->actingAs(client())->get('/app')
        ->assertOk()
        ->assertSee('Tu rutina viene en camino')
        ->assertSee('Aún no tienes una membresía activa');
});
