<?php

use App\Enums\PaymentMethod;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\Plan;
use App\Services\MembershipService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs($this->coach = admin());
});

it('shows monthly income by payment method and pending renewals', function () {
    Carbon::setTestNow('2026-09-15');
    $plan = Plan::firstWhere('duration_months', 1);
    $service = app(MembershipService::class);
    $service->renew(client(['name' => 'Ana Paga']), $plan, PaymentMethod::Sinpe, $this->coach);
    $service->renew(client(['name' => 'Luis Paga']), $plan, PaymentMethod::Cash, $this->coach);

    $expiring = client(['name' => 'Marta Vence']);
    $expiring->memberships()->create(['plan_id' => $plan->id, 'starts_on' => '2026-08-20', 'ends_on' => '2026-09-19', 'price' => 25, 'payment_method' => 'cash', 'created_at' => '2026-08-20']);

    $this->get(route('admin.memberships.index'))
        ->assertOk()
        ->assertSee(money($plan->price * 2))
        ->assertSee(['SINPE Móvil', 'Efectivo', 'Ana Paga', 'Luis Paga'])
        ->assertSeeInOrder(['Renovaciones pendientes', 'Marta Vence']);

    $this->get(route('admin.memberships.index', ['mes' => '2026-08']))->assertSee('Marta Vence')->assertDontSee('Ana Paga');
});

it('adds, edits and hides exercises', function () {
    $this->post(route('admin.catalog.exercises.store'), [
        'name' => '  Remo   invertido ', 'muscle_group' => 'back', 'equipment' => 'bodyweight',
        'default_sets' => 3, 'default_reps' => '8-12', 'default_rest_seconds' => 60,
    ])->assertRedirect(route('admin.catalog.exercises'));

    $exercise = Exercise::firstWhere('name', 'Remo invertido');
    expect($exercise)->not->toBeNull();

    $this->put(route('admin.catalog.exercises.update', $exercise), [...$exercise->only(['name', 'default_sets', 'default_reps', 'default_rest_seconds']), 'muscle_group' => 'back', 'equipment' => 'bodyweight', 'default_sets' => 4]);
    expect($exercise->fresh()->default_sets)->toBe(4);

    $this->post(route('admin.catalog.exercises.toggle', $exercise));
    expect($exercise->fresh()->active)->toBeFalse();

    $this->get(route('admin.catalog.exercises', ['q' => 'Remo invertido']))->assertOk()->assertSee('Oculto');
});

it('keeps create and inline-edit errors apart', function () {
    $existing = Exercise::first();

    $this->from(route('admin.catalog.exercises'))->post(route('admin.catalog.exercises.store'), ['name' => $existing->name])
        ->assertSessionHasErrorsIn('create', ['name', 'muscle_group']);

    $this->from(route('admin.catalog.exercises'))->put(route('admin.catalog.exercises.update', $existing), ['_row' => $existing->id, 'name' => ''])
        ->assertSessionHasErrorsIn('edit', ['name']);
});

it('updates foods and plan totals follow', function () {
    $food = Food::firstWhere('name', 'Huevo entero');

    $this->put(route('admin.catalog.foods.update', $food), [
        'name' => $food->name, 'category' => 'protein', 'portion_label' => '1 unidad grande',
        'calories' => 80, 'protein_g' => 7, 'carbs_g' => 0.5, 'fats_g' => 5,
    ])->assertSessionHas('success');

    expect($food->fresh()->calories)->toBe(80)->and($food->fresh()->portion_label)->toBe('1 unidad grande');
});

it('manages membership plans', function () {
    $this->post(route('admin.catalog.plans.store'), ['name' => 'Bimestral', 'duration_months' => 2, 'price' => 45])->assertSessionHas('success');
    $plan = Plan::firstWhere('name', 'Bimestral');

    $this->put(route('admin.catalog.plans.update', $plan), ['name' => 'Bimestral', 'duration_months' => 2, 'price' => 48]);
    expect($plan->fresh()->price)->toBe('48.00');

    $this->post(route('admin.catalog.plans.toggle', $plan));
    $client = client();
    $this->post(route('admin.clients.memberships.store', $client), ['plan_id' => $plan->id, 'payment_method' => 'cash'])->assertSessionHasErrors('plan_id');

    $this->get(route('admin.catalog.plans'))->assertOk()->assertSee(['Bimestral', 'Oculto']);
});

it('renders every catalog page', function (string $route) {
    $this->get(route($route))->assertOk();
})->with(['admin.catalog.exercises', 'admin.catalog.foods', 'admin.catalog.plans', 'admin.workouts.index', 'admin.nutrition.index']);
