<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveUserRequest;
use App\Models\User;
use App\Support\TemporaryPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'rol' => ['nullable', Rule::enum(Role::class)],
            'estado' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        return view('admin.users.index', [
            'users' => User::query()->search($filters['q'] ?? null)
                ->when($filters['rol'] ?? null, fn ($q, $role) => $q->where('role', $role))
                ->when($filters['estado'] ?? null, fn ($q, $status) => $q->where('active', $status === 'active'))
                ->orderBy('name')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(SaveUserRequest $request): RedirectResponse
    {
        $password = TemporaryPassword::generate();
        $user = User::create([...$request->validated(), 'password' => $password, 'email_verified_at' => now()]);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Usuario creado. Anota su contraseña temporal.')
            ->with('credentials', ['email' => $user->email, 'password' => $password]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(SaveUserRequest $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user) {
            $this->protectAccess($request, $user, $request->input('role') !== Role::Admin->value);
            $user->update($request->validated());
        });

        return redirect()->route('admin.users.edit', $user)->with('success', 'Usuario actualizado.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user) {
            $this->protectAccess($request, $user, true);
            $user->update(['active' => ! $user->active]);
            if (! $user->active) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        });

        return back()->with('success', $user->active ? 'Usuario habilitado.' : 'Usuario deshabilitado. Su historial se conserva.');
    }

    /** Bloquea cambios que dejarían al administrador sin acceso al sistema. */
    private function protectAccess(Request $request, User $user, bool $removesAccess): void
    {
        $admins = User::where('role', Role::Admin)->where('active', true)->orderBy('id')->lockForUpdate()->get();
        abort_unless($admins->contains('id', $request->user()->id), 403);
        $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
        $user->setRawAttributes($lockedUser->getAttributes(), true);

        if ($removesAccess && ($user->is($request->user()) || ($user->isAdmin() && $user->active && $admins->count() <= 1))) {
            throw ValidationException::withMessages(['role' => 'No puedes quitar tu propio acceso ni el del último entrenador activo.']);
        }
    }
}
