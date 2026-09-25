<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return ['email' => 'correo', 'password' => 'contraseña'];
    }

    /** Intenta iniciar sesión con límite de 5 intentos por minuto por correo e IP. */
    public function authenticate(): void
    {
        $key = Str::transliterate($this->string('email').'|'.$this->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            event(new Lockout($this));
            throw ValidationException::withMessages([
                'email' => 'Demasiados intentos. Intente de nuevo en '.RateLimiter::availableIn($key).' segundos.',
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no son correctos.']);
        }

        if (! Auth::user()->active) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Su cuenta está desactivada. Contacte a su entrenador.']);
        }

        RateLimiter::clear($key);
    }
}
