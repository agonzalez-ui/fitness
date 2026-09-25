<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveClientRequest;
use App\Models\NutritionPlan;
use App\Models\Plan;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\MembershipService;
use App\Services\PlanAssignmentService;
use App\Support\TemporaryPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'in:active,expiring_soon,expired,none,inactive'],
        ]);
        $status = $filters['estado'] ?? null;

        $clients = User::clients()
            ->with(['latestMembership.plan', 'activeWorkoutPlan', 'latestProgress'])
            ->search($filters['q'] ?? null)
            ->when($status === 'inactive', fn ($q) => $q->where('active', false), fn ($q) => $q->where('active', true))
            ->when($status && $status !== 'inactive', fn ($q) => $q->withMembershipStatus(MembershipStatus::from($status)))
            ->orderBy('name')
            ->paginate(24)->withQueryString();

        $counts = collect(MembershipStatus::cases())->mapWithKeys(fn (MembershipStatus $s) => [
            $s->value => User::clients()->active()->withMembershipStatus($s)->count(),
        ]);

        return view('admin.clients.index', [
            'clients' => $clients,
            'counts' => $counts,
            'inactiveCount' => User::clients()->where('active', false)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.clients.create', $this->templates());
    }

    public function store(SaveClientRequest $request, MembershipService $memberships, PlanAssignmentService $plans): RedirectResponse
    {
        $password = TemporaryPassword::generate();
        $coach = $request->user();

        $client = DB::transaction(function () use ($request, $password, $coach, $memberships, $plans) {
            $client = User::create([...$request->profile(), 'password' => $password, 'role' => Role::Client, 'email_verified_at' => now()]);

            if ($request->filled('plan_id')) {
                $memberships->renew($client, Plan::findOrFail($request->integer('plan_id')), PaymentMethod::from($request->input('payment_method')), $coach);
            }
            if ($request->filled('workout_template_id')) {
                $plans->assignWorkout(WorkoutPlan::findOrFail($request->integer('workout_template_id')), $client, $coach);
            }
            if ($request->filled('nutrition_template_id')) {
                $plans->assignNutrition(NutritionPlan::findOrFail($request->integer('nutrition_template_id')), $client, $coach);
            }

            return $client;
        });

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "Cliente {$client->first_name} creado.")
            ->with('credentials', ['email' => $client->email, 'password' => $password]);
    }

    public function show(User $client): View
    {
        $client->load([
            'memberships.plan', 'latestMembership.plan',
            'activeWorkoutPlan.days.items.exercise', 'activeNutritionPlan.meals.items.food',
            'progressEntries', 'progressPhotos',
        ]);

        return view('admin.clients.show', [
            'client' => $client,
            'plans' => Plan::active()->orderBy('duration_months')->get(),
            'nextStart' => app(MembershipService::class)->nextStartDate($client),
            'recentLogs' => $client->workoutLogs()->with('day')->whereNotNull('finished_at')->latest('performed_on')->take(8)->get(),
            ...$this->templates(),
        ]);
    }

    public function edit(User $client): View
    {
        return view('admin.clients.edit', ['client' => $client]);
    }

    public function update(SaveClientRequest $request, User $client): RedirectResponse
    {
        $client->update($request->profile());

        return redirect()->route('admin.clients.show', $client)->with('success', 'Datos actualizados.');
    }

    /** Desactivar, nunca borrar: se conserva su historial. */
    public function destroy(User $client): RedirectResponse
    {
        $client->update(['active' => false]);

        return redirect()->route('admin.clients.index')->with('success', "{$client->name} fue desactivado. Su historial se conserva.");
    }

    public function activate(User $client): RedirectResponse
    {
        $client->update(['active' => true]);

        return back()->with('success', "{$client->name} está activo de nuevo.");
    }

    public function resetPassword(User $client): RedirectResponse
    {
        $password = TemporaryPassword::generate();
        $client->update(['password' => $password]);

        return back()->with('success', 'Se generó una contraseña nueva.')
            ->with('credentials', ['email' => $client->email, 'password' => $password]);
    }

    /** @return array{workoutTemplates: \Illuminate\Support\Collection, nutritionTemplates: \Illuminate\Support\Collection, plans: \Illuminate\Support\Collection} */
    private function templates(): array
    {
        return [
            'workoutTemplates' => WorkoutPlan::templates()->where('active', true)->withCount('days')->orderBy('title')->get(),
            'nutritionTemplates' => NutritionPlan::templates()->where('active', true)->with('meals.items.food')->orderBy('title')->get(),
            'plans' => Plan::active()->orderBy('duration_months')->get(),
        ];
    }
}
