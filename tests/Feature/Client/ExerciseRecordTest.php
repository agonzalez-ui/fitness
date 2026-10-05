<?php

use App\Livewire\Client\ExerciseRecord;
use App\Livewire\Client\WorkoutSession;
use App\Models\WorkoutPlan;
use App\Services\PlanAssignmentService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->client = client();
    $this->actingAs($this->client);
    app(PlanAssignmentService::class)->assignWorkout(WorkoutPlan::templates()->first(), $this->client, admin());
    $this->day = $this->client->activeWorkoutPlan->days()->first();
    $this->item = $this->day->items()->first();
});

it('records actual sets, corrects them and preserves completion and other exercises', function () {
    $this->client->workoutLogs()->create(['workout_day_id' => $this->day->id, 'performed_on' => today(),
        'completed_exercise_ids' => [$this->item->id], 'effort' => 4, 'finished_at' => now(),
        'exercise_records' => ['999' => ['name' => 'Anterior']]]);
    $component = Livewire::test(ExerciseRecord::class, ['itemId' => $this->item->id])
        ->set('unit', 'lb')->set('basis', 'per_dumbbell')
        ->set('sets', [['weight' => 45, 'reps' => 8, 'rir' => 2], ['weight' => 45, 'reps' => 6, 'rir' => '']])
        ->call('save')->assertHasNoErrors()->assertSet('saved', true)->assertDispatched('exercise-record-saved');
    $log = $this->client->workoutLogs()->sole();
    expect($log->exercise_records[$this->item->id]['sets'][1]['rir'])->toBeNull()
        ->and($log->exercise_records['999']['name'])->toBe('Anterior')
        ->and($log->completed_exercise_ids)->toBe([$this->item->id])->and($log->effort)->toBe(4);
    $component->set('sets.0.weight', 50)->call('save')->assertHasNoErrors();
    Livewire::test(ExerciseRecord::class, ['itemId' => $this->item->id])->assertSet('sets.0.weight', 50)->assertSet('unit', 'lb');
    expect($this->client->workoutLogs()->count())->toBe(1);
});

it('rejects invalid sets without creating a log', function ($sets, $field) {
    Livewire::test(ExerciseRecord::class, ['itemId' => $this->item->id])->set('sets', $sets)
        ->call('save')->assertHasErrors($field);
    expect($this->client->workoutLogs()->count())->toBe(0);
})->with([
    'negative weight' => [[['weight' => -1, 'reps' => 8, 'rir' => 2]], 'sets.0.weight'],
    'no repetitions' => [[['weight' => 20, 'reps' => 0, 'rir' => 2]], 'sets.0.reps'],
    'invalid RIR' => [[['weight' => 20, 'reps' => 8, 'rir' => 11]], 'sets.0.rir'],
    'empty sets' => [[], 'sets'],
]);

it('rejects exercises belonging to someone else or to templates', function () {
    $foreign = WorkoutPlan::templates()->first()->days()->first()->items()->first();
    Livewire::test(ExerciseRecord::class, ['itemId' => $foreign->id])->assertNotFound();
    $this->actingAs(client());
    Livewire::test(ExerciseRecord::class, ['itemId' => $this->item->id])->assertNotFound();
});

it('keeps sessions separate and groups history Monday to Sunday with user isolation', function () {
    Carbon::setTestNow('2026-10-05 12:00');
    $record = [['exercise_id' => $this->item->exercise_id, 'name' => 'Registro semanal', 'unit' => 'kg', 'basis' => 'total',
        'sets' => [['weight' => 100, 'reps' => 8, 'rir' => 0]]]];
    $this->client->workoutLogs()->create(['workout_day_id' => $this->day->id, 'performed_on' => '2026-10-04', 'exercise_records' => $record]);
    Livewire::test(ExerciseRecord::class, ['itemId' => $this->item->id])->set('sets', [['weight' => 110, 'reps' => 6, 'rir' => 1]])->call('save')->assertHasNoErrors();
    Livewire::test(WorkoutSession::class)->assertDontSee('Registro semanal')->call('changeWeek', -1)->assertSee('Registro semanal')
        ->call('changeWeek', 1)->assertDontSee('Registro semanal');
    expect($this->client->workoutLogs()->count())->toBe(2);
    $this->actingAs(client());
    Livewire::test(WorkoutSession::class)->call('changeWeek', -1)->assertDontSee('Registro semanal');
});

it('allows finishing without optional records', function () {
    Livewire::test(WorkoutSession::class)->call('selectDay', $this->day->id)->call('finish', 4)->assertHasNoErrors();
    expect($this->client->workoutLogs()->sole()->exercise_records)->toBeNull();
});
