<?php

use App\Models\NutritionPlan;
use App\Models\Plan;
use App\Models\WorkoutPlan;
use Database\Seeders\DatabaseSeeder;

it('paginates progress and photos independently without changing chart data', function () {
    $user = client();
    for ($i = 0; $i < 13; $i++) {
        $user->progressEntries()->create(['recorded_on' => today()->subDays($i), 'weight_kg' => 70 + $i, 'notes' => 'Registro '.$i]);
        $user->progressPhotos()->create(['taken_on' => today()->subDays($i), 'path' => 'test/'.$i.'.jpg', 'pose' => 'front']);
    }
    $this->actingAs($user)->get(route('client.progress', ['registros' => 2, 'fotos' => 2, 'medida' => 'weight_kg']))
        ->assertOk()->assertViewHas('history', fn ($page) => $page->count() === 3 && $page->total() === 13)
        ->assertViewHas('photos', fn ($page) => $page->count() === 6 && $page->currentPage() === 2)
        ->assertViewHas('entries', fn ($entries) => $entries->count() === 13)
        ->assertViewHas('comparisonPhotos', fn ($photos) => $photos->count() === 13)
        ->assertSee('Registro 12');
    $this->actingAs(admin())->get(route('admin.clients.show', [$user, 'registros' => 2, 'fotos' => 2]))
        ->assertOk()->assertViewHas('progressHistory', fn ($page) => $page->total() === 13 && $page->count() === 3)
        ->assertViewHas('progressPhotos', fn ($page) => $page->currentPage() === 2);
});

it('paginates workout and nutrition plans retaining the client tab', function () {
    $user = client();
    for ($i = 0; $i < 14; $i++) {
        WorkoutPlan::create(['user_id' => $user->id, 'title' => 'Rutina '.$i, 'active' => true]);
        NutritionPlan::create(['user_id' => $user->id, 'title' => 'Comidas '.$i, 'active' => true]);
    }
    $this->actingAs(admin());
    foreach (['admin.workouts.index', 'admin.nutrition.index'] as $route) {
        $this->get(route($route, ['ver' => 'clientes', 'page' => 2]))->assertOk()
            ->assertViewHas('plans', fn ($page) => $page->total() === 14 && $page->count() === 2)
            ->assertViewHas('tab', 'clients');
    }
});

it('keeps monthly totals and methods independent of payment pagination', function () {
    $this->seed(DatabaseSeeder::class);
    $user = client();
    $plan = Plan::first();
    for ($i = 0; $i < 18; $i++) {
        $user->memberships()->create(['plan_id' => $plan->id, 'starts_on' => today(), 'ends_on' => today()->addMonth(),
            'price' => 100, 'payment_method' => 'cash']);
    }
    $this->actingAs(admin())->get(route('admin.memberships.index', ['pagos' => 2]))->assertOk()
        ->assertViewHas('payments', fn ($page) => $page->count() === 3 && $page->total() === 18)
        ->assertViewHas('total', fn ($total) => (float) $total === 1800.0)
        ->assertViewHas('byMethod', fn ($methods) => $methods['Efectivo'] === 1800.0);
});

it('paginates conversations keeping the search filter', function () {
    for ($i = 0; $i < 17; $i++) {
        client(['name' => 'Cliente paginado '.$i]);
    }
    $this->actingAs(admin())->get(route('admin.messages.index', ['q' => 'Cliente paginado', 'conversaciones' => 2]))
        ->assertOk()->assertViewHas('conversations', fn ($page) => $page->count() === 2 && $page->total() === 17)
        ->assertSee('conversaciones=1', false);
});
