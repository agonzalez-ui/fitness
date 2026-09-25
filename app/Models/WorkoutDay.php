<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['workout_plan_id', 'name', 'weekday', 'position'])]
class WorkoutDay extends Model
{
    use HasFactory;

    public const WEEKDAYS = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

    protected function casts(): array
    {
        return ['weekday' => 'integer', 'position' => 'integer'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class, 'workout_plan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkoutDayExercise::class)->orderBy('position');
    }

    protected function weekdayName(): Attribute
    {
        return Attribute::get(fn () => self::WEEKDAYS[$this->weekday] ?? null);
    }
}
