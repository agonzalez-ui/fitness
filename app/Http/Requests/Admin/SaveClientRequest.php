<?php

namespace App\Http\Requests\Admin;

use App\Enums\Goal;
use App\Enums\PaymentMethod;
use App\Models\NutritionPlan;
use App\Models\Plan;
use App\Models\WorkoutPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::squish((string) $this->input('name')),
            'email' => Str::lower(trim((string) $this->input('email'))),
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
        ]);
    }

    public function rules(): array
    {
        $client = $this->route('client');
        $creating = $client === null;

        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($client)],
            'phone' => ['nullable', 'string', 'max:30'],
            'goal' => ['nullable', Rule::enum(Goal::class)],
            'birth_date' => ['nullable', 'date', 'before:-10 years', 'after:1920-01-01'],
            'gender' => ['nullable', Rule::in(['female', 'male', 'other'])],
            'height_cm' => ['nullable', 'numeric', 'between:100,250'],
            'trainer_notes' => ['nullable', 'string', 'max:2000'],
            // Solo al crear: todo lo necesario para dejar al cliente listo en un paso.
            'plan_id' => $creating ? ['nullable', Rule::exists(Plan::class, 'id')->where('active', true)] : ['prohibited'],
            'payment_method' => $creating ? ['required_with:plan_id', 'nullable', Rule::enum(PaymentMethod::class)] : ['prohibited'],
            'workout_template_id' => $creating ? ['nullable', Rule::exists(WorkoutPlan::class, 'id')->whereNull('user_id')] : ['prohibited'],
            'nutrition_template_id' => $creating ? ['nullable', Rule::exists(NutritionPlan::class, 'id')->whereNull('user_id')] : ['prohibited'],
        ];
    }

    /** Solo los datos del perfil, sin los extras de la creación. */
    public function profile(): array
    {
        return collect($this->validated())->only(['name', 'email', 'phone', 'goal', 'birth_date', 'gender', 'height_cm', 'trainer_notes'])->all();
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'birth_date.before' => 'Revise la fecha de nacimiento.',
            'payment_method.required_with' => 'Elija cómo pagó la membresía.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre', 'email' => 'correo', 'phone' => 'teléfono', 'goal' => 'objetivo',
            'birth_date' => 'fecha de nacimiento', 'gender' => 'género', 'height_cm' => 'estatura',
            'trainer_notes' => 'notas', 'plan_id' => 'plan', 'payment_method' => 'forma de pago',
        ];
    }
}
