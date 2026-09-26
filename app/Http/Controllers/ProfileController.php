<?php

namespace App\Http\Controllers;

use App\Enums\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge(['name' => Str::squish((string) $request->input('name')), 'email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30'],
            'goal' => ['nullable', Rule::enum(Goal::class)],
            'birth_date' => ['nullable', 'date', 'before:-10 years', 'after:1920-01-01'],
            'height_cm' => ['nullable', 'numeric', 'between:100,250'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ], ['email.unique' => 'Ese correo ya lo usa otra cuenta.', 'birth_date.before' => 'Revisa la fecha de nacimiento.'], [
            'name' => 'nombre', 'email' => 'correo', 'phone' => 'teléfono', 'birth_date' => 'fecha de nacimiento', 'height_cm' => 'estatura', 'avatar' => 'foto',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }
        unset($data['avatar']);

        $user->update($data);

        return back()->with('success', 'Perfil actualizado.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'different:current_password'],
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.different' => 'La nueva contraseña debe ser distinta a la actual.',
        ], ['current_password' => 'contraseña actual', 'password' => 'nueva contraseña']);

        $request->user()->update(['password' => $request->input('password')]);

        return back()->with('success', 'Contraseña cambiada.');
    }
}
