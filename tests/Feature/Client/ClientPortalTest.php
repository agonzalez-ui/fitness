<?php

use App\Livewire\Client\WorkoutSession;
use App\Models\NutritionPlan;
use App\Models\ProgressPhoto;
use App\Models\WorkoutPlan;
use App\Services\PlanAssignmentService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->client = client(['name' => 'María González']);
    $this->coach = admin();
    $this->actingAs($this->client);
});

it('opens today\'s day and lets the client tick exercises and finish', function () {
    Carbon::setTestNow('2026-09-30 07:00'); // miércoles → Día B del Full Body
    app(PlanAssignmentService::class)->assignWorkout(WorkoutPlan::templates()->where('title', 'like', 'Full Body%')->first(), $this->client, $this->coach);
    $day = $this->client->activeWorkoutPlan->days()->firstWhere('name', 'Día B');
    $items = $day->items()->pluck('id');

    $component = Livewire::test(WorkoutSession::class)
        ->assertSet('dayId', $day->id)
        ->assertSee('Prensa de piernas')
        ->assertSee('0 de '.$items->count().' ejercicios')
        ->call('toggle', $items[0])
        ->call('toggle', $items[1])
        ->assertSee('2 de '.$items->count().' ejercicios')
        ->call('toggle', $items[1]);

    $log = $this->client->workoutLogs()->first();
    expect($log->completed_exercise_ids)->toBe([$items[0]]);

    $component->call('finish', 4)->assertSee('¡Entrenamiento completado!')->assertDispatched('workout-finished');
    expect($log->fresh()->effort)->toBe(4)->and($log->fresh()->finished_at)->not->toBeNull();

    // El entrenador lo ve en el dashboard.
    $this->actingAs($this->coach)->get('/admin')->assertSee('María González')->assertSee('completó');
});

it('does not let clients tick exercises from other days or plans', function () {
    app(PlanAssignmentService::class)->assignWorkout(WorkoutPlan::templates()->first(), $this->client, $this->coach);
    $foreign = WorkoutPlan::templates()->skip(1)->first()->days()->first()->items()->first();

    Livewire::test(WorkoutSession::class)->call('toggle', $foreign->id)->assertNotFound();
});

it('shows the nutrition plan with meals and totals', function () {
    app(PlanAssignmentService::class)->assignNutrition(NutritionPlan::templates()->where('title', 'like', 'Déficit%')->first(), $this->client, $this->coach);

    $this->get(route('client.nutrition'))
        ->assertOk()
        ->assertSee(['Déficit 1600 kcal', 'Desayuno', 'Clara de huevo', 'Tu meta diaria', 'Proteína']);
});

it('logs weight quickly without wiping other measures of the day', function () {
    $this->post(route('client.progress.store'), ['weight_kg' => 72.4, 'waist_cm' => 80])->assertSessionHas('success');
    $this->post(route('client.progress.store'), ['weight_kg' => 72.1, 'quick' => 1]);

    $entry = $this->client->progressEntries()->sole();
    expect($entry->weight_kg)->toBe(72.1)->and($entry->waist_cm)->toBe(80.0);
});

it('celebrates weight loss compared with the previous entry', function () {
    $this->client->progressEntries()->create(['recorded_on' => today()->subWeek(), 'weight_kg' => 75]);

    $this->post(route('client.progress.store'), ['weight_kg' => 74.2])
        ->assertSessionHas('success', '¡Registrado! Bajaste 0.8 kg desde la última vez. 💪');
});

it('requires at least one measure', function () {
    $this->post(route('client.progress.store'), [])->assertSessionHasErrors(['weight_kg' => 'Anota al menos tu peso o una medida.']);
});

it('shows progress chart and history', function () {
    $this->client->progressEntries()->createMany([
        ['recorded_on' => '2026-08-01', 'weight_kg' => 75, 'waist_cm' => 85],
        ['recorded_on' => '2026-09-01', 'weight_kg' => 73.5, 'waist_cm' => 82],
    ]);

    $this->get(route('client.progress'))->assertOk()->assertSee(['Tu progreso', '-1.5', 'Gráfica de peso', 'Historial']);
    $this->get(route('client.progress', ['medida' => 'waist_cm']))->assertSee('Gráfica de cintura');
});

it('keeps progress photos private', function () {
    Storage::fake('local');

    $this->post(route('client.photos.store'), ['photo' => UploadedFile::fake()->image('yo.jpg'), 'pose' => 'front'])->assertSessionHas('success');
    $photo = ProgressPhoto::sole();
    Storage::disk('local')->assertExists($photo->path);

    $this->get($photo->url)->assertOk();
    $this->actingAs($this->coach)->get($photo->url)->assertOk();
    $this->actingAs(client())->get($photo->url)->assertNotFound();
});

it('lets the client chat with the coach team', function () {
    $this->get(route('client.messages'))->assertOk()->assertSee('Tu entrenador')->assertSeeLivewire('chat');

    Livewire::test('chat', ['client' => $this->client])->set('body', 'Hola coach')->call('send');

    expect($this->client->sentMessages()->sole()->recipient_id)->toBe($this->coach->id);

    // Cualquier entrenador ve el mensaje sin leer y al abrir el chat queda leído.
    $otherCoach = admin();
    expect($otherCoach->unreadMessagesCount())->toBe(1);

    $this->actingAs($otherCoach);
    $this->get(route('admin.messages.index'))->assertSee('Hola coach');
    Livewire::test('chat', ['client' => $this->client])->assertSee('Hola coach')->set('body', 'Hola María')->call('send');

    expect($otherCoach->unreadMessagesCount())->toBe(0)->and($this->client->unreadMessagesCount())->toBe(1);
});

it('does not let a client open someone else\'s chat', function () {
    Livewire::test('chat', ['client' => client()])->assertForbidden();
});

it('updates profile and password', function () {
    $this->put(route('profile.update'), ['name' => 'María G.', 'email' => $this->client->email, 'goal' => 'toning'])->assertSessionHas('success');
    expect($this->client->fresh()->name)->toBe('María G.');

    $this->put(route('profile.password'), ['current_password' => 'mala', 'password' => 'nueva1234', 'password_confirmation' => 'nueva1234'])
        ->assertSessionHasErrorsIn('password', ['current_password']);

    $this->put(route('profile.password'), ['current_password' => 'password', 'password' => 'nueva1234', 'password_confirmation' => 'nueva1234'])
        ->assertSessionHas('success');

    $this->get(route('profile.edit'))->assertOk()->assertSee('Mi objetivo');
    $this->actingAs($this->coach)->get(route('profile.edit'))->assertOk()->assertDontSee('Mi objetivo');
});

it('renders every client page', function (string $route) {
    $this->get(route($route))->assertOk();
})->with(['client.dashboard', 'client.workout', 'client.nutrition', 'client.progress', 'client.messages', 'profile.edit']);
