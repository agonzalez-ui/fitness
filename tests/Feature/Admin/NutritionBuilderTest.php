<?php

use App\Livewire\Admin\NutritionBuilder;
use App\Models\Food;
use App\Models\NutritionPlan;
use Database\Seeders\FoodSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(FoodSeeder::class);
    $this->actingAs(admin());
});

it('creates a template and opens the builder', function () {
    $this->post(route('admin.nutrition.store'), ['title' => 'Déficit 1800'])
        ->assertRedirect(route('admin.nutrition.edit', $plan = NutritionPlan::firstWhere('title', 'Déficit 1800')));

    $this->get(route('admin.nutrition.edit', $plan))->assertOk()->assertSee('Agrega las comidas del día');
});

it('builds meals from the catalog and computes totals', function () {
    $plan = NutritionPlan::create(['title' => 'Plan']);
    $egg = Food::firstWhere('name', 'Huevo entero');
    $oats = Food::firstWhere('name', 'Avena en hojuelas');

    $component = Livewire::test(NutritionBuilder::class, ['plan' => $plan])
        ->call('addMeal')
        ->call('addFood', $egg->id)
        ->call('addFood', $egg->id) // otra vez el mismo: suma una porción en vez de duplicar
        ->call('addFood', $oats->id);

    $meal = $plan->meals()->first();
    expect($meal->name)->toBe('Desayuno')->and(substr($meal->time, 0, 5))->toBe('07:00')
        ->and($meal->items()->count())->toBe(2)
        ->and($meal->items()->firstWhere('food_id', $egg->id)->servings)->toBe(2.0);

    $oatsItem = $meal->items()->firstWhere('food_id', $oats->id);
    $component->call('stepServings', $oatsItem->id, -1);
    expect($oatsItem->fresh()->servings)->toBe(0.75);

    $expected = (int) round($egg->calories * 2 + $oats->calories * 0.75);
    expect($plan->fresh()->load('meals.items.food')->totals['calories'])->toBe($expected);
    $component->assertSee(number_format($expected));
});

it('adds the next suggested meal each time', function () {
    $plan = NutritionPlan::create(['title' => 'Plan']);

    Livewire::test(NutritionBuilder::class, ['plan' => $plan])->call('addMeal')->call('addMeal')->call('addMeal');

    expect($plan->meals()->pluck('name')->all())->toBe(['Desayuno', 'Merienda AM', 'Almuerzo']);
});

it('rejects invalid meal times and keeps the old value', function () {
    $plan = NutritionPlan::create(['title' => 'Plan']);
    $component = Livewire::test(NutritionBuilder::class, ['plan' => $plan])->call('addMeal');
    $meal = $plan->meals()->first();

    $component->call('updateMeal', $meal->id, 'time', '25:99');
    expect(substr($meal->fresh()->time, 0, 5))->toBe('07:00');

    $component->call('updateMeal', $meal->id, 'time', '06:30');
    expect(substr($meal->fresh()->time, 0, 5))->toBe('06:30');
});

it('cannot touch meals from another plan', function () {
    $mine = NutritionPlan::create(['title' => 'Mío']);
    $other = NutritionPlan::create(['title' => 'Otro']);
    $otherMeal = $other->meals()->create(['name' => 'Ajena', 'position' => 0]);

    Livewire::test(NutritionBuilder::class, ['plan' => $mine])->call('removeMeal', $otherMeal->id)->assertNotFound();
    expect($otherMeal->fresh())->not->toBeNull();
});
