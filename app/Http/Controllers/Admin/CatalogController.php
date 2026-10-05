<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Equipment;
use App\Enums\FoodCategory;
use App\Enums\MuscleGroup;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Catálogos que alimentan los armadores: ejercicios, alimentos y planes de membresía.
 * El alta usa el bag de errores "create" y la edición en línea el bag "edit" (con _row = id de la fila).
 */
class CatalogController extends Controller
{
    public function index(): View
    {
        return view('admin.catalog.index', [
            'exerciseCount' => Exercise::count(),
            'foodCount' => Food::count(),
            'planCount' => Plan::count(),
            'clientCount' => User::clients()->count(),
            'userCount' => User::count(),
        ]);
    }

    /* ---------- Ejercicios ---------- */

    public function exercises(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'grupo' => ['nullable', Rule::enum(MuscleGroup::class)], 'estado' => ['nullable', Rule::in(['active', 'inactive'])]]);

        return view('admin.catalog.exercises', [
            'exercises' => Exercise::query()
                ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%$term%"))
                ->when($request->query('grupo'), fn ($q, $group) => $q->where('muscle_group', $group))
                ->when($request->query('estado'), fn ($q, $status) => $q->where('active', $status === 'active'))
                ->orderByDesc('active')->orderBy('name')->paginate(15)->withQueryString(),
        ]);
    }

    public function storeExercise(Request $request): RedirectResponse
    {
        $exercise = Exercise::create($this->validateExercise($request));

        return redirect()->route('admin.catalog.exercises')->with('success', "Ejercicio «{$exercise->name}» agregado al catálogo.");
    }

    public function updateExercise(Request $request, Exercise $exercise): RedirectResponse
    {
        $exercise->update($this->validateExercise($request, $exercise));

        return back()->with('success', 'Ejercicio actualizado.');
    }

    public function toggleExercise(Exercise $exercise): RedirectResponse
    {
        $exercise->update(['active' => ! $exercise->active]);

        return back()->with('success', $exercise->active ? 'Ejercicio visible de nuevo.' : 'Ejercicio oculto del catálogo. Las rutinas que ya lo usan no cambian.');
    }

    /* ---------- Alimentos ---------- */

    public function foods(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'categoria' => ['nullable', Rule::enum(FoodCategory::class)], 'estado' => ['nullable', Rule::in(['active', 'inactive'])]]);

        return view('admin.catalog.foods', [
            'foods' => Food::query()
                ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%$term%"))
                ->when($request->query('categoria'), fn ($q, $cat) => $q->where('category', $cat))
                ->when($request->query('estado'), fn ($q, $status) => $q->where('active', $status === 'active'))
                ->orderByDesc('active')->orderBy('name')->paginate(15)->withQueryString(),
        ]);
    }

    public function storeFood(Request $request): RedirectResponse
    {
        $food = Food::create($this->validateFood($request));

        return redirect()->route('admin.catalog.foods')->with('success', "Alimento «{$food->name}» agregado al catálogo.");
    }

    public function updateFood(Request $request, Food $food): RedirectResponse
    {
        $food->update($this->validateFood($request, $food));

        return back()->with('success', 'Alimento actualizado. Los totales de los planes se recalculan solos.');
    }

    public function toggleFood(Food $food): RedirectResponse
    {
        $food->update(['active' => ! $food->active]);

        return back()->with('success', $food->active ? 'Alimento visible de nuevo.' : 'Alimento oculto del catálogo. Los planes que ya lo usan no cambian.');
    }

    /* ---------- Planes de membresía ---------- */

    public function plans(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'estado' => ['nullable', Rule::in(['active', 'inactive'])]]);

        return view('admin.catalog.plans', [
            'plans' => Plan::withCount('memberships')
                ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%$term%"))
                ->when($request->query('estado'), fn ($q, $status) => $q->where('active', $status === 'active'))
                ->orderByDesc('active')->orderBy('duration_months')->paginate(15)->withQueryString(),
        ]);
    }

    public function storePlan(Request $request): RedirectResponse
    {
        Plan::create($this->validatePlan($request));

        return back()->with('success', 'Plan creado.');
    }

    public function updatePlan(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validatePlan($request, $plan));

        return back()->with('success', 'Plan actualizado. Las membresías ya pagadas conservan su precio.');
    }

    public function togglePlan(Plan $plan): RedirectResponse
    {
        $plan->update(['active' => ! $plan->active]);

        return back()->with('success', $plan->active ? 'Plan disponible de nuevo.' : 'Plan oculto: ya no aparece al renovar.');
    }

    /* ---------- Validación ---------- */

    private function validateExercise(Request $request, ?Exercise $exercise = null): array
    {
        $request->merge(['name' => Str::squish((string) $request->input('name'))]);

        return $request->validateWithBag($exercise ? 'edit' : 'create', [
            'name' => ['required', 'string', 'max:120', Rule::unique('exercises')->ignore($exercise)],
            'muscle_group' => ['required', Rule::enum(MuscleGroup::class)],
            'equipment' => ['required', Rule::enum(Equipment::class)],
            'default_sets' => ['required', 'integer', 'between:1,10'],
            'default_reps' => ['required', 'string', 'max:20'],
            'default_rest_seconds' => ['required', 'integer', 'between:0,600'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'video_url' => ['nullable', 'url:http,https', 'max:255'],
        ], ['name.unique' => 'Ya existe un ejercicio con ese nombre.'], ['name' => 'nombre', 'video_url' => 'enlace del video']);
    }

    private function validateFood(Request $request, ?Food $food = null): array
    {
        $request->merge(['name' => Str::squish((string) $request->input('name'))]);

        return $request->validateWithBag($food ? 'edit' : 'create', [
            'name' => ['required', 'string', 'max:120', Rule::unique('foods')->ignore($food)],
            'category' => ['required', Rule::enum(FoodCategory::class)],
            'portion_label' => ['required', 'string', 'max:40'],
            'calories' => ['required', 'integer', 'between:0,3000'],
            'protein_g' => ['required', 'numeric', 'between:0,300'],
            'carbs_g' => ['required', 'numeric', 'between:0,300'],
            'fats_g' => ['required', 'numeric', 'between:0,300'],
        ], ['name.unique' => 'Ya existe un alimento con ese nombre.'], ['name' => 'nombre', 'portion_label' => 'porción', 'calories' => 'calorías']);
    }

    private function validatePlan(Request $request, ?Plan $plan = null): array
    {
        $request->merge(['name' => Str::squish((string) $request->input('name'))]);

        return $request->validateWithBag($plan ? 'edit' : 'create', [
            'name' => ['required', 'string', 'max:80', Rule::unique('plans')->ignore($plan)],
            'duration_months' => ['required', 'integer', 'between:1,24'],
            'price' => ['required', 'numeric', 'between:0,1000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [], ['name' => 'nombre', 'duration_months' => 'duración', 'price' => 'precio']);
    }
}
