<?php

namespace App\Livewire\Client;

use App\Models\WorkoutDayExercise;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ExerciseRecord extends Component
{
    #[Locked]
    public int $itemId;

    public string $unit = 'kg';

    public string $basis = 'total';

    public array $sets = [];

    public bool $saved = false;

    private function item(): WorkoutDayExercise
    {
        return WorkoutDayExercise::with('exercise')->whereHas('day.plan', fn ($query) => $query
            ->whereKey(auth()->user()->activeWorkoutPlan?->id ?? 0)->where('user_id', auth()->id()))->findOrFail($this->itemId);
    }

    public function mount(int $itemId): void
    {
        $this->itemId = $itemId;
        $item = $this->item();
        $record = auth()->user()->workoutLogs()->where('workout_day_id', $item->workout_day_id)
            ->whereDate('performed_on', today())->first()?->exercise_records[$itemId] ?? null;
        $this->unit = $record['unit'] ?? 'kg';
        $this->basis = $record['basis'] ?? 'total';
        $this->sets = $record['sets'] ?? [['weight' => '', 'reps' => '', 'rir' => '']];
    }

    public function addSet(): void
    {
        if (count($this->sets) < 20) {
            $this->sets[] = ['weight' => '', 'reps' => '', 'rir' => ''];
            $this->saved = false;
        }
    }

    public function removeSet(int $index): void
    {
        unset($this->sets[$index]);
        $this->sets = array_values($this->sets);
        $this->saved = false;
    }

    public function updated(): void
    {
        $this->saved = false;
    }

    public function save(): void
    {
        $item = $this->item();
        $this->validate([
            'unit' => 'required|in:kg,lb', 'basis' => 'required|in:total,per_dumbbell,bodyweight',
            'sets' => 'required|array|min:1|max:20',
            'sets.*' => 'array:weight,reps,rir',
            'sets.*.weight' => 'required|numeric|min:0|max:2000',
            'sets.*.reps' => 'required|integer|min:1|max:200',
            'sets.*.rir' => 'nullable|integer|min:0|max:10',
        ], ['required' => 'Completá este dato.', 'numeric' => 'Ingresá un número.',
            'integer' => 'Ingresá un número entero.', 'min' => 'El valor es demasiado bajo.', 'max' => 'El valor es demasiado alto.']);

        DB::transaction(function () use ($item) {
            // Serializa los registros del cliente para conservar otros ejercicios al guardar.
            auth()->user()->newQuery()->whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $log = auth()->user()->workoutLogs()->firstOrCreate([
                'workout_day_id' => $item->workout_day_id, 'performed_on' => today()->toDateString(),
            ], ['completed_exercise_ids' => []]);
            $records = $log->exercise_records ?? [];
            $records[$item->id] = [
                'exercise_id' => $item->exercise_id, 'name' => $item->exercise->name,
                'unit' => $this->unit, 'basis' => $this->basis,
                'sets' => array_map(fn ($set) => ['weight' => (float) $set['weight'], 'reps' => (int) $set['reps'],
                    'rir' => ($set['rir'] ?? '') === '' || $set['rir'] === null ? null : (int) $set['rir']], $this->sets),
            ];
            $log->update(['exercise_records' => $records]);
        });
        $this->saved = true;
        $this->dispatch('exercise-record-saved');
    }

    public function render(): View
    {
        return view('livewire.client.exercise-record');
    }
}
