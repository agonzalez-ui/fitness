<?php

namespace App\Models;

use App\Enums\Goal;
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

#[Fillable(['user_id', 'template_id', 'title', 'goal', 'notes', 'file_path', 'file_name', 'active', 'created_by'])]
class NutritionPlan extends Model
{
    use Auditable, HasFactory;

    protected function casts(): array
    {
        return ['goal' => Goal::class, 'active' => 'boolean'];
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

    public function meals(): HasMany
    {
        return $this->hasMany(NutritionMeal::class)->orderBy('position');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Totales diarios calculados desde los alimentos; nunca se escriben a mano.
     *
     * @return array{calories: int, protein_g: float, carbs_g: float, fats_g: float}
     */
    protected function totals(): Attribute
    {
        return Attribute::get(function () {
            $items = $this->meals->flatMap->items;

            return [
                'calories' => (int) round($items->sum(fn ($item) => $item->food->calories * $item->servings)),
                'protein_g' => round($items->sum(fn ($item) => $item->food->protein_g * $item->servings), 1),
                'carbs_g' => round($items->sum(fn ($item) => $item->food->carbs_g * $item->servings), 1),
                'fats_g' => round($items->sum(fn ($item) => $item->food->fats_g * $item->servings), 1),
            ];
        });
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->file_path ? Storage::disk('public')->url($this->file_path) : null);
    }
}
