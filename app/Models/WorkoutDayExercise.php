<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['workout_day_id', 'exercise_id', 'sets', 'reps', 'rest_seconds', 'notes', 'position'])]
class WorkoutDayExercise extends Model
{
    public function day(): BelongsTo
    {
        return $this->belongsTo(WorkoutDay::class, 'workout_day_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
