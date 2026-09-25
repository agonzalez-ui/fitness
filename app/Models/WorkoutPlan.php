<?php

namespace App\Models;

use App\Enums\Goal;
use App\Enums\Level;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'template_id', 'title', 'description', 'level', 'goal', 'file_path', 'file_name', 'active', 'created_by'])]
class WorkoutPlan extends Model
{
    use Auditable, HasFactory;

    protected function casts(): array
    {
        return ['level' => Level::class, 'goal' => Goal::class, 'active' => 'boolean'];
    }

    #[Scope]
    protected function templates(Builder $query): void
    {
        $query->whereNull('user_id');
    }

    public function isTemplate(): bool
    {
        return $this->user_id === null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(self::class, 'template_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(WorkoutDay::class)->orderBy('position');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * El día que toca en una fecha. Si ningún día tiene asignado ese día de la semana, devuelve null (descanso).
     * Los planes sin días de la semana asignados se recorren en orden, según los entrenamientos ya hechos.
     */
    public function dayFor(\DateTimeInterface $date, int $completedCount = 0): ?WorkoutDay
    {
        $days = $this->days;

        if ($days->isEmpty()) {
            return null;
        }

        if ($days->whereNotNull('weekday')->isEmpty()) {
            return $days->values()[$completedCount % $days->count()];
        }

        return $days->firstWhere('weekday', (int) $date->format('N'));
    }

    /** Próximo día con entrenamiento después de la fecha indicada. */
    public function nextDayAfter(\DateTimeInterface $date): ?WorkoutDay
    {
        $today = (int) $date->format('N');

        return $this->days->whereNotNull('weekday')
            ->sortBy(fn (WorkoutDay $day) => ($day->weekday - $today + 7) % 7 ?: 7)
            ->first();
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->file_path ? Storage::disk('public')->url($this->file_path) : null);
    }
}
