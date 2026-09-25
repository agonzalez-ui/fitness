<?php

namespace App\Services;

use App\Models\NutritionPlan;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Support\Facades\DB;

/**
 * Copia una plantilla (o el plan de otro cliente) como plan propio del cliente,
 * dejando inactivos sus planes anteriores. La plantilla original no se toca.
 */
class PlanAssignmentService
{
    public function assignWorkout(WorkoutPlan $source, User $client, User $by): WorkoutPlan
    {
        return DB::transaction(function () use ($source, $client, $by) {
            $source->loadMissing('days.items');
            $client->workoutPlans()->where('active', true)->update(['active' => false]);

            $plan = $client->workoutPlans()->create([
                ...$source->only(['title', 'description', 'level', 'goal', 'file_path', 'file_name']),
                'template_id' => $source->isTemplate() ? $source->id : $source->template_id,
                'active' => true,
                'created_by' => $by->id,
            ]);

            foreach ($source->days as $day) {
                $copy = $plan->days()->create($day->only(['name', 'weekday', 'position']));
                $copy->items()->createMany($day->items->map->only(['exercise_id', 'sets', 'reps', 'rest_seconds', 'notes', 'position'])->all());
            }

            return $plan;
        });
    }

    public function assignNutrition(NutritionPlan $source, User $client, User $by): NutritionPlan
    {
        return DB::transaction(function () use ($source, $client, $by) {
            $source->loadMissing('meals.items');
            $client->nutritionPlans()->where('active', true)->update(['active' => false]);

            $plan = $client->nutritionPlans()->create([
                ...$source->only(['title', 'goal', 'notes', 'file_path', 'file_name']),
                'template_id' => $source->isTemplate() ? $source->id : $source->template_id,
                'active' => true,
                'created_by' => $by->id,
            ]);

            foreach ($source->meals as $meal) {
                $copy = $plan->meals()->create($meal->only(['name', 'time', 'position']));
                $copy->items()->createMany($meal->items->map->only(['food_id', 'servings', 'position'])->all());
            }

            return $plan;
        });
    }
}
