<?php

use App\Enums\Goal;
use App\Enums\MembershipStatus;
use App\Enums\Role;
use App\Models\NutritionPlan;
use App\Models\Plan;
use App\Models\User;
use App\Models\WorkoutPlan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->coach = admin();
});

it('lists clients and filters them by computed membership status', function () {
    $plan = Plan::first();
    $expired = client(['name' => 'Juan Vencido']);
    $expired->memberships()->create(['plan_id' => $plan->id, 'starts_on' => '2026-01-01', 'ends_on' => today()->subDay(), 'price' => 25, 'payment_method' => 'cash']);
    client(['name' => 'Ana Sin Plan']);
    client(['name' => 'Pedro Inactivo', 'active' => false]);

    $this->actingAs($this->coach)->get('/admin/clientes')
        ->assertOk()->assertSee(['Juan Vencido', 'Ana Sin Plan'])->assertDontSee('Pedro Inactivo');

    $this->get('/admin/clientes?estado=expired')->assertSee('Juan Vencido')->assertDontSee('Ana Sin Plan');
    $this->get('/admin/clientes?estado=inactive')->assertSee('Pedro Inactivo')->assertDontSee('Juan Vencido');
    $this->get('/admin/clientes?q=ana')->assertSee('Ana Sin Plan')->assertDontSee('Juan Vencido');
});

it('creates a ready-to-go client in one step', function () {
    $plan = Plan::firstWhere('duration_months', 1);
    $workout = WorkoutPlan::templates()->first();
    $nutrition = NutritionPlan::templates()->first();

    $response = $this->actingAs($this->coach)->post('/admin/clientes', [
        'name' => ' Laura  Solís ',
        'email' => 'LAURA@example.com',
        'phone' => '8888-1234',
        'goal' => Goal::WeightLoss->value,
        'plan_id' => $plan->id,
        'payment_method' => 'sinpe',
        'workout_template_id' => $workout->id,
        'nutrition_template_id' => $nutrition->id,
    ]);

    $client = User::firstWhere('email', 'laura@example.com');
    $response->assertRedirect(route('admin.clients.show', $client))->assertSessionHas('credentials');
    $password = session('credentials')['password'];

    expect($client->name)->toBe('Laura Solís')
        ->and($client->role)->toBe(Role::Client)
        ->and($client->membership_status)->toBe(MembershipStatus::Active)
        ->and($client->activeWorkoutPlan->template_id)->toBe($workout->id)
        ->and($client->activeWorkoutPlan->days()->count())->toBe($workout->days()->count())
        ->and($client->activeNutritionPlan->title)->toBe($nutrition->title)
        ->and(Hash::check($password, $client->password))->toBeTrue();

    // La plantilla no se modifica: el cliente recibe una copia.
    expect($workout->fresh()->user_id)->toBeNull();

    $this->get(route('admin.clients.show', $client))
        ->assertOk()
        ->assertSee($password)
        ->assertSee('https://wa.me/50688881234', false);
});

it('requires a payment method when a plan is chosen', function () {
    $this->actingAs($this->coach)->post('/admin/clientes', [
        'name' => 'Sin Pago', 'email' => 'x@example.com', 'plan_id' => Plan::first()->id,
    ])->assertSessionHasErrors(['payment_method' => 'Elija cómo pagó la membresía.']);
});

it('shows the client profile with progress and renews the membership', function () {
    $client = client(['name' => 'María González']);
    $client->progressEntries()->createMany([
        ['recorded_on' => '2026-08-01', 'weight_kg' => 72.5],
        ['recorded_on' => '2026-09-01', 'weight_kg' => 70.3],
    ]);

    $this->actingAs($this->coach)->get(route('admin.clients.show', $client))
        ->assertOk()->assertSee(['María González', 'Peso', '70.3 kg', 'Asignar rutina desde plantilla']);

    $this->post(route('admin.clients.memberships.store', $client), ['plan_id' => Plan::first()->id, 'payment_method' => 'cash'])
        ->assertRedirect(route('admin.clients.show', $client))
        ->assertSessionHas('success');

    expect($client->fresh()->membership_status)->toBe(MembershipStatus::Active);
});

it('updates, deactivates and reactivates a client', function () {
    $client = client();

    $this->actingAs($this->coach)->put(route('admin.clients.update', $client), [
        'name' => 'Nuevo Nombre', 'email' => $client->email, 'height_cm' => 170,
    ])->assertRedirect(route('admin.clients.show', $client));
    expect($client->fresh()->name)->toBe('Nuevo Nombre');

    $this->delete(route('admin.clients.destroy', $client))->assertRedirect(route('admin.clients.index'));
    expect($client->fresh()->active)->toBeFalse();

    $this->post(route('admin.clients.activate', $client));
    expect($client->fresh()->active)->toBeTrue();
});

it('generates a new temporary password', function () {
    $client = client();

    $this->actingAs($this->coach)->post(route('admin.clients.password', $client))->assertSessionHas('credentials');

    expect(Hash::check(session('credentials')['password'], $client->fresh()->password))->toBeTrue();
});

it('assigns a template and sends the coach to adjust it', function () {
    $client = client();
    $old = WorkoutPlan::templates()->first();
    $new = WorkoutPlan::templates()->skip(1)->first();

    $this->actingAs($this->coach)->post(route('admin.clients.workout', $client), ['source_id' => $old->id]);
    $this->post(route('admin.clients.workout', $client), ['source_id' => $new->id]);

    expect($client->workoutPlans()->count())->toBe(2)
        ->and($client->fresh()->activeWorkoutPlan->template_id)->toBe($new->id)
        ->and($client->workoutPlans()->where('active', true)->count())->toBe(1);
});

it('does not treat admins as clients in client routes', function () {
    $this->actingAs($this->coach)->get(route('admin.clients.show', admin()))->assertNotFound();
});

it('keeps clients out of the admin area', function () {
    $this->actingAs(client())->get('/admin/clientes')->assertRedirect(route('client.dashboard'));
});
