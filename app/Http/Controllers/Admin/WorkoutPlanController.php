<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Level;
use App\Http\Controllers\Controller;
use App\Models\WorkoutPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkoutPlanController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('ver') === 'clientes' ? 'clients' : 'templates';

        $plans = WorkoutPlan::query()
            ->with(['user', 'days.items'])
            ->when($tab === 'templates', fn ($q) => $q->templates(), fn ($q) => $q->whereNotNull('user_id')->where('active', true))
            ->orderBy($tab === 'templates' ? 'title' : 'updated_at', $tab === 'templates' ? 'asc' : 'desc')
            ->paginate(12)->withQueryString();

        return view('admin.workouts.index', compact('plans', 'tab'));
    }

    /** Crea una plantilla vacía y abre directamente el armador. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'level' => ['required', Rule::enum(Level::class)],
        ], ['title.required' => 'Ponle un nombre a la rutina.']);

        $plan = WorkoutPlan::create([...$data, 'created_by' => $request->user()->id]);

        return redirect()->route('admin.workouts.edit', $plan);
    }

    public function edit(WorkoutPlan $workout): View
    {
        return view('admin.workouts.edit', ['plan' => $workout]);
    }

    /** Guarda una copia como plantilla nueva (por ejemplo, una rutina de cliente que quedó muy bien). */
    public function duplicate(Request $request, WorkoutPlan $workout): RedirectResponse
    {
        $copy = DB::transaction(function () use ($request, $workout) {
            $workout->load('days.items');
            $copy = WorkoutPlan::create([
                ...$workout->only(['description', 'level', 'goal', 'file_path', 'file_name']),
                'title' => $workout->title.($workout->isTemplate() ? ' (copia)' : ''),
                'created_by' => $request->user()->id,
            ]);
            foreach ($workout->days as $day) {
                $newDay = $copy->days()->create($day->only(['name', 'weekday', 'position']));
                $newDay->items()->createMany($day->items->map->only(['exercise_id', 'sets', 'reps', 'rest_seconds', 'notes', 'position'])->all());
            }

            return $copy;
        });

        return redirect()->route('admin.workouts.edit', $copy)->with('success', 'Plantilla creada. Cámbiale el nombre si quieres.');
    }

    public function destroy(WorkoutPlan $workout): RedirectResponse
    {
        if ($workout->isTemplate()) {
            $workout->delete();

            return redirect()->route('admin.workouts.index')->with('success', 'Plantilla eliminada. Las rutinas ya asignadas a clientes no cambian.');
        }

        $workout->update(['active' => false]);

        return redirect()->route('admin.clients.show', $workout->user_id)->with('success', 'Rutina retirada. El historial del cliente se conserva.');
    }
}
