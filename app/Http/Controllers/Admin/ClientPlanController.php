<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NutritionPlan;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\PlanAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Asignar una plantilla o el plan de otro cliente con un clic. */
class ClientPlanController extends Controller
{
    public function workout(Request $request, User $client, PlanAssignmentService $service): RedirectResponse
    {
        $data = $request->validate(['source_id' => ['required', Rule::exists(WorkoutPlan::class, 'id')]], ['source_id.required' => 'Elija una rutina.']);
        $plan = $service->assignWorkout(WorkoutPlan::findOrFail($data['source_id']), $client, $request->user());

        return redirect()->route('admin.workouts.edit', $plan)
            ->with('success', "Rutina «{$plan->title}» asignada a {$client->first_name}. Ajústala si quieres; los cambios se guardan solos.");
    }

    public function nutrition(Request $request, User $client, PlanAssignmentService $service): RedirectResponse
    {
        $data = $request->validate(['source_id' => ['required', Rule::exists(NutritionPlan::class, 'id')]], ['source_id.required' => 'Elija un plan.']);
        $plan = $service->assignNutrition(NutritionPlan::findOrFail($data['source_id']), $client, $request->user());

        return redirect()->route('admin.nutrition.edit', $plan)
            ->with('success', "Plan «{$plan->title}» asignado a {$client->first_name}. Ajústalo si quieres; los cambios se guardan solos.");
    }
}
