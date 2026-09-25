<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'workout_day_id', 'performed_on', 'completed_exercise_ids', 'effort', 'notes', 'finished_at'])]
class WorkoutLog extends Model
{
    protected function casts(): array
    {
        return [
            'performed_on' => 'date',
            'completed_exercise_ids' => 'array',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(WorkoutDay::class, 'workout_day_id');
    }
}
