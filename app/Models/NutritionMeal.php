<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nutrition_plan_id', 'name', 'time', 'position'])]
class NutritionMeal extends Model
{
    public const SUGGESTED = ['Desayuno' => '07:00', 'Merienda AM' => '10:00', 'Almuerzo' => '12:30', 'Merienda PM' => '16:00', 'Cena' => '19:00'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(NutritionPlan::class, 'nutrition_plan_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(NutritionMealItem::class)->orderBy('position');
    }
}
