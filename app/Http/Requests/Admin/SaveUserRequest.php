<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($this->route('user'))],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::enum(Role::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::squish((string) $this->input('name')),
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    public function messages(): array
    {
        return ['email.unique' => 'Ya existe un usuario con ese correo.'];
    }

    public function attributes(): array
    {
        return ['name' => 'nombre', 'email' => 'correo', 'phone' => 'teléfono', 'role' => 'rol'];
    }
}
