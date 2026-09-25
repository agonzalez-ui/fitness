<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Goal;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', ['goals' => Goal::cases()]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([...$request->validated(), 'role' => Role::Client]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('client.dashboard')
            ->with('success', "¡Bienvenido, {$user->first_name}! Tu entrenador ya puede asignarte tu plan.");
    }
}
