<?php

namespace App\Livewire\Client;

use App\Models\WorkoutDay;
use App\Models\WorkoutLog;
use App\Models\WorkoutPlan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/** Entrenamiento del día: el cliente marca cada ejercicio y al final califica cómo le fue. */
class WorkoutSession extends Component
{
    #[Url(as: 'dia', history: true)]
    public ?int $dayId = null;

    public function mount(): void
    {
        $plan = $this->plan;
        if (! $plan) {
            return;
        }

        // Si no viene un día en la URL, se abre el que toca hoy (o el primero).
        if (! $this->dayId || ! $plan->days->contains('id', $this->dayId)) {
            $finished = auth()->user()->workoutLogs()->whereNotNull('finished_at')->count();
            $this->dayId = ($plan->dayFor(today(), $finished) ?? $plan->nextDayAfter(today()) ?? $plan->days->first())?->id;
        }
    }

    #[Computed]
    public function plan(): ?WorkoutPlan
    {
        return auth()->user()->activeWorkoutPlan()->with('days.items.exercise')->first();
    }

    #[Computed]
    public function day(): ?WorkoutDay
    {
        return $this->plan?->days->firstWhere('id', $this->dayId);
    }

    #[Computed]
    public function log(): ?WorkoutLog
    {
        return $this->day ? auth()->user()->workoutLogs()
            ->where('workout_day_id', $this->day->id)->whereDate('performed_on', today())->first() : null;
    }

    public function selectDay(int $dayId): void
    {
        abort_unless($this->plan?->days->contains('id', $dayId), 404);
        $this->dayId = $dayId;
        unset($this->day, $this->log);
    }

    public function toggle(int $itemId): void
    {
        abort_unless($this->day?->items->contains('id', $itemId), 404);

        $log = $this->log ?? auth()->user()->workoutLogs()->create([
            'workout_day_id' => $this->day->id,
            'performed_on' => today(),
            'completed_exercise_ids' => [],
        ]);

        $done = collect($log->completed_exercise_ids ?? []);
        $done = $done->contains($itemId) ? $done->reject(fn ($id) => $id === $itemId) : $done->push($itemId);
        $log->update(['completed_exercise_ids' => $done->values()->all()]);

        unset($this->log);
    }

    public function finish(int $effort): void
    {
        abort_unless($effort >= 1 && $effort <= 5 && $this->day, 422);

        $log = $this->log ?? auth()->user()->workoutLogs()->create([
            'workout_day_id' => $this->day->id,
            'performed_on' => today(),
            'completed_exercise_ids' => [],
        ]);
        $log->update(['effort' => $effort, 'finished_at' => now()]);

        unset($this->log);
        $this->dispatch('workout-finished');
    }

    public function reopen(): void
    {
        $this->log?->update(['finished_at' => null, 'effort' => null]);
        unset($this->log);
    }

    public function render()
    {
        return view('livewire.client.workout-session');
    }
}
