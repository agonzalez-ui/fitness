<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NutritionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NutritionPlanController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('ver') === 'clientes' ? 'clients' : 'templates';

        $plans = NutritionPlan::query()
            ->with(['user', 'meals.items.food'])
            ->when($tab === 'templates', fn ($q) => $q->templates(), fn ($q) => $q->whereNotNull('user_id')->where('active', true))
            ->orderBy($tab === 'templates' ? 'title' : 'updated_at', $tab === 'templates' ? 'asc' : 'desc')
            ->get();

        return view('admin.nutrition.index', compact('plans', 'tab'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:150']], ['title.required' => 'Ponle un nombre al plan.']);

        $plan = NutritionPlan::create([...$data, 'created_by' => $request->user()->id]);

        return redirect()->route('admin.nutrition.edit', $plan);
    }

    public function edit(NutritionPlan $nutrition): View
    {
        return view('admin.nutrition.edit', ['plan' => $nutrition]);
    }

    public function duplicate(Request $request, NutritionPlan $nutrition): RedirectResponse
    {
        $copy = DB::transaction(function () use ($request, $nutrition) {
            $nutrition->load('meals.items');
            $copy = NutritionPlan::create([
                ...$nutrition->only(['goal', 'notes', 'file_path', 'file_name']),
                'title' => $nutrition->title.($nutrition->isTemplate() ? ' (copia)' : ''),
                'created_by' => $request->user()->id,
            ]);
            foreach ($nutrition->meals as $meal) {
                $newMeal = $copy->meals()->create($meal->only(['name', 'time', 'position']));
                $newMeal->items()->createMany($meal->items->map->only(['food_id', 'servings', 'position'])->all());
            }

            return $copy;
        });

        return redirect()->route('admin.nutrition.edit', $copy)->with('success', 'Plantilla creada. Cámbiale el nombre si quieres.');
    }

    public function destroy(NutritionPlan $nutrition): RedirectResponse
    {
        if ($nutrition->isTemplate()) {
            $nutrition->delete();

            return redirect()->route('admin.nutrition.index')->with('success', 'Plantilla eliminada. Los planes ya asignados no cambian.');
        }

        $nutrition->update(['active' => false]);

        return redirect()->route('admin.clients.show', $nutrition->user_id)->with('success', 'Plan retirado. El historial del cliente se conserva.');
    }
}
