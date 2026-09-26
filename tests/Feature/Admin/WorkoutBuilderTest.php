<?php

use App\Livewire\Admin\WorkoutBuilder;
use App\Models\Exercise;
use App\Models\WorkoutPlan;
use Database\Seeders\ExerciseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(ExerciseSeeder::class);
    $this->coach = admin();
    $this->actingAs($this->coach);
});

it('creates a template and opens the builder', function () {
    $this->post(route('admin.workouts.store'), ['title' => 'Fuerza 4 días', 'level' => 'intermediate'])
        ->assertRedirect(route('admin.workouts.edit', $plan = WorkoutPlan::firstWhere('title', 'Fuerza 4 días')));

    expect($plan->isTemplate())->toBeTrue();
    $this->get(route('admin.workouts.edit', $plan))->assertOk()->assertSeeLivewire(WorkoutBuilder::class)->assertSee('Agrega el primer día');
});

it('builds a workout without typing: days, exercises, sets and order', function () {
    $plan = WorkoutPlan::create(['title' => 'Nueva', 'level' => 'beginner']);
    $squat = Exercise::firstWhere('name', 'Sentadilla con barra');
    $press = Exercise::firstWhere('name', 'Press de banca con barra');

    $component = Livewire::test(WorkoutBuilder::class, ['plan' => $plan])
        ->call('addDay')
        ->call('addExercise', $squat->id)
        ->call('addExercise', $press->id);

    $day = $plan->days()->first();
    expect($day->name)->toBe('Día A')->and($day->weekday)->toBe(1);

    [$first, $second] = $day->items()->get()->all();
    expect($first->exercise_id)->toBe($squat->id)
        ->and($first->sets)->toBe($squat->default_sets)
        ->and($first->reps)->toBe($squat->default_reps);

    $component->call('stepSets', $first->id, 1)
        ->call('updateItem', $first->id, 'reps', '5')
        ->call('sortItems', $second->id, 0);

    expect($first->fresh()->sets)->toBe($squat->default_sets + 1)
        ->and($first->fresh()->reps)->toBe('5')
        ->and($second->fresh()->position)->toBe(0)
        ->and($first->fresh()->position)->toBe(1);

    $component->call('addDay');
    expect($plan->days()->pluck('weekday')->all())->toBe([1, 3]);
});

it('autosaves plan details and ignores invalid item values', function () {
    $plan = WorkoutPlan::create(['title' => 'Vieja', 'level' => 'beginner']);

    $component = Livewire::test(WorkoutBuilder::class, ['plan' => $plan])
        ->set('title', 'Nueva rutina')
        ->set('level', 'advanced')
        ->assertDispatched('saved')
        ->call('addDay')
        ->call('addExercise', Exercise::first()->id);

    expect($plan->fresh()->title)->toBe('Nueva rutina')->and($plan->fresh()->level->value)->toBe('advanced');

    $item = $plan->days()->first()->items()->first();
    $component->call('updateItem', $item->id, 'rest_seconds', '9999');
    expect($item->fresh()->rest_seconds)->not->toBe(9999);

    $component->set('title', '')->assertHasErrors('title');
    expect($plan->fresh()->title)->toBe('Nueva rutina');
});

it('duplicates and removes days', function () {
    $plan = WorkoutPlan::create(['title' => 'X', 'level' => 'beginner']);
    $component = Livewire::test(WorkoutBuilder::class, ['plan' => $plan])
        ->call('addDay')->call('addExercise', Exercise::first()->id);
    $day = $plan->days()->first();

    $component->call('duplicateDay', $day->id);
    expect($plan->days()->count())->toBe(2)->and($plan->days()->latest('id')->first()->items()->count())->toBe(1);

    $component->call('removeDay', $day->id);
    expect($plan->days()->count())->toBe(1)->and($plan->days()->first()->position)->toBe(0);
});

it('cannot touch days or items from another plan', function () {
    $mine = WorkoutPlan::create(['title' => 'Mía', 'level' => 'beginner']);
    $other = WorkoutPlan::create(['title' => 'Otra', 'level' => 'beginner']);
    $otherDay = $other->days()->create(['name' => 'Ajeno', 'position' => 0]);
    $otherItem = $otherDay->items()->create(['exercise_id' => Exercise::first()->id]);

    Livewire::test(WorkoutBuilder::class, ['plan' => $mine])->call('removeDay', $otherDay->id)->assertNotFound();
    Livewire::test(WorkoutBuilder::class, ['plan' => $mine])->call('removeItem', $otherItem->id)->assertNotFound();

    expect($otherDay->fresh())->not->toBeNull()->and($otherItem->fresh())->not->toBeNull();
});

it('attaches a PDF to the plan', function () {
    Storage::fake('public');
    $plan = WorkoutPlan::create(['title' => 'Con PDF', 'level' => 'beginner']);

    Livewire::test(WorkoutBuilder::class, ['plan' => $plan])
        ->set('file', UploadedFile::fake()->create('rutina.pdf', 200, 'application/pdf'));

    expect($plan->fresh()->file_name)->toBe('rutina.pdf');
    Storage::disk('public')->assertExists($plan->fresh()->file_path);
});

it('saves a client plan as a new template', function () {
    $client = client();
    $plan = WorkoutPlan::create(['title' => 'De María', 'level' => 'beginner', 'user_id' => $client->id]);
    $plan->days()->create(['name' => 'Día A', 'position' => 0])->items()->create(['exercise_id' => Exercise::first()->id]);

    $this->post(route('admin.workouts.duplicate', $plan));

    $copy = WorkoutPlan::templates()->firstWhere('title', 'De María');
    expect($copy)->not->toBeNull()->and($copy->days()->first()->items()->count())->toBe(1);
});

it('only lets admins use the builder', function () {
    $this->actingAs(client());
    Livewire::test(WorkoutBuilder::class, ['plan' => WorkoutPlan::create(['title' => 'X', 'level' => 'beginner'])])->assertForbidden();
});
