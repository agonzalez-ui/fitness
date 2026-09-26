<?php

namespace App\Livewire\Admin;

use App\Enums\Equipment;
use App\Enums\Goal;
use App\Enums\Level;
use App\Enums\MuscleGroup;
use App\Models\Exercise;
use App\Models\WorkoutDay;
use App\Models\WorkoutDayExercise;
use App\Models\WorkoutPlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Armador de rutinas: todo se guarda al instante, sin botón "Guardar".
 * Sirve igual para plantillas y para el plan de un cliente.
 */
class WorkoutBuilder extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $planId;

    public string $title = '';

    public string $description = '';

    public string $level = '';

    public ?string $goal = null;

    #[Locked]
    public ?int $activeDayId = null;

    public string $search = '';

    public ?string $muscle = null;

    public $file = null;

    public function boot(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function mount(WorkoutPlan $plan): void
    {
        $this->planId = $plan->id;
        $this->title = $plan->title;
        $this->description = (string) $plan->description;
        $this->level = $plan->level->value;
        $this->goal = $plan->goal?->value;
        $this->activeDayId = $plan->days()->value('id');
    }

    #[Computed]
    public function plan(): WorkoutPlan
    {
        return WorkoutPlan::with(['user', 'days.items.exercise'])->findOrFail($this->planId);
    }

    #[Computed]
    public function activeDay(): ?WorkoutDay
    {
        return $this->plan->days->firstWhere('id', $this->activeDayId) ?? $this->plan->days->first();
    }

    #[Computed]
    public function library(): Collection
    {
        $term = trim($this->search);

        return Exercise::active()
            ->when($term !== '', fn ($q) => $q->where('name', 'like', "%$term%"))
            ->when($this->muscle, fn ($q) => $q->where('muscle_group', $this->muscle))
            ->orderBy('name')->limit(40)->get();
    }

    /* ---------- Datos del plan (se guardan al salir de cada campo) ---------- */

    public function updated(string $property): void
    {
        if (in_array($property, ['title', 'description', 'level', 'goal'], true)) {
            $data = $this->validate([
                'title' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:2000'],
                'level' => ['required', Rule::enum(Level::class)],
                'goal' => ['nullable', Rule::enum(Goal::class)],
            ]);
            $this->plan->update([...$data, 'goal' => $data['goal'] ?: null]);
            $this->saved();
        }

        if ($property === 'file') {
            $this->validate(['file' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240']], ['file.mimes' => 'Solo PDF, Word o imágenes.', 'file.max' => 'Máximo 10 MB.']);
            $plan = $this->plan;
            if ($plan->file_path && ! $this->pathSharedWithOtherPlans($plan)) {
                Storage::disk('public')->delete($plan->file_path);
            }
            $plan->update(['file_path' => $this->file->store('workout-files', 'public'), 'file_name' => $this->file->getClientOriginalName()]);
            $this->file = null;
            $this->saved('Archivo adjuntado');
        }
    }

    public function removeFile(): void
    {
        $plan = $this->plan;
        if ($plan->file_path && ! $this->pathSharedWithOtherPlans($plan)) {
            Storage::disk('public')->delete($plan->file_path);
        }
        $plan->update(['file_path' => null, 'file_name' => null]);
        $this->saved('Archivo quitado');
    }

    /* ---------- Días ---------- */

    public function addDay(): void
    {
        $days = $this->plan->days;
        $used = $days->pluck('weekday')->filter()->all();
        // Siguiente día libre, dejando uno de descanso de por medio cuando se puede.
        $weekday = collect([1, 3, 5, 2, 4, 6, 7])->first(fn ($d) => ! in_array($d, $used, true));

        $day = $this->plan->days()->create([
            'name' => 'Día '.chr(65 + $days->count()),
            'weekday' => $weekday,
            'position' => $days->count(),
        ]);
        $this->activeDayId = $day->id;
        $this->refresh();
    }

    public function selectDay(int $dayId): void
    {
        $this->activeDayId = $this->day($dayId)->id;
    }

    public function renameDay(int $dayId, string $name): void
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 80) {
            return;
        }
        $this->day($dayId)->update(['name' => $name]);
        $this->refresh();
    }

    public function setWeekday(int $dayId, ?string $weekday): void
    {
        $weekday = $weekday === '' || $weekday === null ? null : (int) $weekday;
        abort_unless($weekday === null || ($weekday >= 1 && $weekday <= 7), 422);
        $this->day($dayId)->update(['weekday' => $weekday]);
        $this->refresh();
    }

    public function duplicateDay(int $dayId): void
    {
        $source = $this->day($dayId);
        $copy = DB::transaction(function () use ($source) {
            $copy = $this->plan->days()->create(['name' => $source->name.' (copia)', 'weekday' => null, 'position' => $this->plan->days->count()]);
            $copy->items()->createMany($source->items->map->only(['exercise_id', 'sets', 'reps', 'rest_seconds', 'notes', 'position'])->all());

            return $copy;
        });
        $this->activeDayId = $copy->id;
        $this->refresh();
    }

    public function removeDay(int $dayId): void
    {
        $this->day($dayId)->delete();
        $this->activeDayId = null;
        $this->renumber($this->plan->days()->orderBy('position')->get());
        $this->refresh();
    }

    public function sortDays(int $dayId, int $position): void
    {
        $this->move($this->plan->days()->orderBy('position')->get(), $dayId, $position);
        $this->refresh();
    }

    /* ---------- Ejercicios ---------- */

    public function addExercise(int $exerciseId): void
    {
        if (! $this->activeDay) {
            $this->addDay();
        }
        $day = $this->activeDay;
        $exercise = Exercise::active()->findOrFail($exerciseId);

        $day->items()->create([
            'exercise_id' => $exercise->id,
            'sets' => $exercise->default_sets,
            'reps' => $exercise->default_reps,
            'rest_seconds' => $exercise->default_rest_seconds,
            'position' => $day->items->count(),
        ]);
        $this->refresh();
        $this->saved($exercise->name.' agregado');
    }

    public function stepSets(int $itemId, int $delta): void
    {
        $item = $this->item($itemId);
        $item->update(['sets' => max(1, min(10, $item->sets + ($delta > 0 ? 1 : -1)))]);
        $this->refresh();
    }

    public function updateItem(int $itemId, string $field, ?string $value): void
    {
        $rules = [
            'reps' => ['required', 'string', 'max:20'],
            'rest_seconds' => ['required', 'integer', 'between:0,600'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
        abort_unless(isset($rules[$field]), 422);

        $validator = validator([$field => $value === null ? null : trim($value)], [$field => $rules[$field]]);
        if ($validator->fails()) {
            $this->refresh();

            return;
        }

        $this->item($itemId)->update($validator->validated());
        $this->refresh();
        $this->saved();
    }

    public function removeItem(int $itemId): void
    {
        $item = $this->item($itemId);
        $item->delete();
        $this->renumber($item->day->items()->orderBy('position')->get());
        $this->refresh();
    }

    public function sortItems(int $itemId, int $position): void
    {
        $item = $this->item($itemId);
        $this->move($item->day->items()->orderBy('position')->get(), $itemId, $position);
        $this->refresh();
    }

    public function render()
    {
        return view('livewire.admin.workout-builder', [
            'levels' => Level::options(),
            'goals' => Goal::options(),
            'muscles' => MuscleGroup::options(),
            'equipment' => Equipment::options(),
        ]);
    }

    /* ---------- Ayudantes ---------- */

    private function day(int $dayId): WorkoutDay
    {
        return $this->plan->days()->findOrFail($dayId);
    }

    private function item(int $itemId): WorkoutDayExercise
    {
        return WorkoutDayExercise::whereKey($itemId)
            ->whereHas('day', fn ($q) => $q->where('workout_plan_id', $this->planId))
            ->with('day')->firstOrFail();
    }

    private function move(Collection $models, int $id, int $position): void
    {
        $moving = $models->firstWhere('id', $id);
        abort_unless($moving, 404);
        $ordered = $models->reject(fn ($m) => $m->id === $id)->values();
        $ordered->splice(max(0, min($position, $ordered->count())), 0, [$moving]);
        $this->renumber($ordered);
    }

    private function renumber(Collection $models): void
    {
        $models->values()->each(fn ($model, $i) => $model->position === $i ?: $model->update(['position' => $i]));
    }

    private function pathSharedWithOtherPlans(WorkoutPlan $plan): bool
    {
        return WorkoutPlan::where('file_path', $plan->file_path)->whereKeyNot($plan->id)->exists();
    }

    private function refresh(): void
    {
        unset($this->plan, $this->activeDay);
    }

    private function saved(string $message = 'Guardado'): void
    {
        $this->dispatch('saved', message: $message);
    }
}
