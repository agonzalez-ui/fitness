<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nutrition_meal_id', 'food_id', 'servings', 'position'])]
class NutritionMealItem extends Model
{
    protected function casts(): array
    {
        return ['servings' => 'float'];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(NutritionMeal::class, 'nutrition_meal_id');
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }
}
