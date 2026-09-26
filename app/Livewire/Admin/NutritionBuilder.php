<?php

namespace App\Livewire\Admin;

use App\Enums\FoodCategory;
use App\Enums\Goal;
use App\Models\Food;
use App\Models\NutritionMeal;
use App\Models\NutritionMealItem;
use App\Models\NutritionPlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Armador de planes de alimentación: comidas + alimentos del catálogo; los totales se calculan solos. */
class NutritionBuilder extends Component
{
    use WithFileUploads;

    /** Porciones que se ofrecen con los botones − / +. */
    public const SERVING_STEPS = [0.25, 0.5, 0.75, 1, 1.25, 1.5, 2, 2.5, 3, 4, 5, 6];

    #[Locked]
    public int $planId;

    public string $title = '';

    public string $notes = '';

    public ?string $goal = null;

    #[Locked]
    public ?int $activeMealId = null;

    public string $search = '';

    public ?string $category = null;

    public $file = null;

    public function boot(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function mount(NutritionPlan $plan): void
    {
        $this->planId = $plan->id;
        $this->title = $plan->title;
        $this->notes = (string) $plan->notes;
        $this->goal = $plan->goal?->value;
        $this->activeMealId = $plan->meals()->value('id');
    }

    #[Computed]
    public function plan(): NutritionPlan
    {
        return NutritionPlan::with(['user', 'meals.items.food'])->findOrFail($this->planId);
    }

    #[Computed]
    public function activeMeal(): ?NutritionMeal
    {
        return $this->plan->meals->firstWhere('id', $this->activeMealId) ?? $this->plan->meals->first();
    }

    #[Computed]
    public function library(): Collection
    {
        $term = trim($this->search);

        return Food::active()
            ->when($term !== '', fn ($q) => $q->where('name', 'like', "%$term%"))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->orderBy('name')->limit(40)->get();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['title', 'notes', 'goal'], true)) {
            $data = $this->validate([
                'title' => ['required', 'string', 'max:150'],
                'notes' => ['nullable', 'string', 'max:5000'],
                'goal' => ['nullable', Rule::enum(Goal::class)],
            ]);
            $this->plan->update([...$data, 'goal' => $data['goal'] ?: null]);
            $this->saved();
        }

        if ($property === 'file') {
            $this->validate(['file' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240']], ['file.mimes' => 'Solo PDF, Word o imágenes.', 'file.max' => 'Máximo 10 MB.']);
            $this->deleteFileIfUnused();
            $this->plan->update(['file_path' => $this->file->store('nutrition-files', 'public'), 'file_name' => $this->file->getClientOriginalName()]);
            $this->file = null;
            $this->saved('Archivo adjuntado');
        }
    }

    public function removeFile(): void
    {
        $this->deleteFileIfUnused();
        $this->plan->update(['file_path' => null, 'file_name' => null]);
        $this->saved('Archivo quitado');
    }

    /* ---------- Comidas ---------- */

    public function addMeal(?string $name = null): void
    {
        $existing = $this->plan->meals->pluck('name')->all();
        $name ??= collect(NutritionMeal::SUGGESTED)->keys()->first(fn ($n) => ! in_array($n, $existing, true)) ?? 'Comida '.(count($existing) + 1);
        abort_unless(mb_strlen($name) <= 60, 422);

        $meal = $this->plan->meals()->create([
            'name' => $name,
            'time' => NutritionMeal::SUGGESTED[$name] ?? null,
            'position' => count($existing),
        ]);
        $this->activeMealId = $meal->id;
        $this->refresh();
    }

    public function selectMeal(int $mealId): void
    {
        $this->activeMealId = $this->meal($mealId)->id;
    }

    public function updateMeal(int $mealId, string $field, ?string $value): void
    {
        $rules = ['name' => ['required', 'string', 'max:60'], 'time' => ['nullable', 'date_format:H:i']];
        abort_unless(isset($rules[$field]), 422);
        $validator = validator([$field => $value === '' ? null : $value], [$field => $rules[$field]]);

        if ($validator->passes()) {
            $this->meal($mealId)->update($validator->validated());
            $this->saved();
        }
        $this->refresh();
    }

    public function removeMeal(int $mealId): void
    {
        $this->meal($mealId)->delete();
        $this->activeMealId = null;
        $this->renumber($this->plan->meals()->orderBy('position')->get());
        $this->refresh();
    }

    public function sortMeals(int $mealId, int $position): void
    {
        $this->move($this->plan->meals()->orderBy('position')->get(), $mealId, $position);
        $this->refresh();
    }

    /* ---------- Alimentos ---------- */

    public function addFood(int $foodId): void
    {
        if (! $this->activeMeal) {
            $this->addMeal();
        }
        $meal = $this->activeMeal;
        $food = Food::active()->findOrFail($foodId);

        $existing = $meal->items->firstWhere('food_id', $food->id);
        if ($existing) {
            $existing->update(['servings' => min(max(self::SERVING_STEPS), $existing->servings + 1)]);
            $this->refresh();
            $this->saved('+1 '.$food->name);

            return;
        }

        $meal->items()->create(['food_id' => $food->id, 'servings' => 1, 'position' => $meal->items->count()]);
        $this->refresh();
        $this->saved($food->name.' agregado');
    }

    public function stepServings(int $itemId, int $direction): void
    {
        $item = $this->item($itemId);
        $steps = self::SERVING_STEPS;
        $index = collect($steps)->search(fn ($s) => $s >= $item->servings - 0.001);
        $index = $index === false ? count($steps) - 1 : $index;
        $next = $steps[max(0, min(count($steps) - 1, $index + ($direction > 0 ? 1 : -1)))];

        // Si ya estaba en un valor intermedio, el primer paso lo acomoda al valor de la lista.
        if ($direction > 0 && $steps[$index] > $item->servings + 0.001) {
            $next = $steps[$index];
        }

        $item->update(['servings' => $next]);
        $this->refresh();
    }

    public function removeItem(int $itemId): void
    {
        $item = $this->item($itemId);
        $item->delete();
        $this->renumber($item->meal->items()->orderBy('position')->get());
        $this->refresh();
    }

    public function sortItems(int $itemId, int $position): void
    {
        $item = $this->item($itemId);
        $this->move($item->meal->items()->orderBy('position')->get(), $itemId, $position);
        $this->refresh();
    }

    public function render()
    {
        return view('livewire.admin.nutrition-builder', [
            'goals' => Goal::options(),
            'categories' => FoodCategory::options(),
        ]);
    }

    /* ---------- Ayudantes ---------- */

    private function meal(int $mealId): NutritionMeal
    {
        return $this->plan->meals()->findOrFail($mealId);
    }

    private function item(int $itemId): NutritionMealItem
    {
        return NutritionMealItem::whereKey($itemId)
            ->whereHas('meal', fn ($q) => $q->where('nutrition_plan_id', $this->planId))
            ->with('meal')->firstOrFail();
    }

    private function move(Collection $models, int $id, int $position): void
    {
        $moving = $models->firstWhere('id', $id);
        abort_unless($moving, 404);
        $ordered = $models->reject(fn ($m) => $m->id === $id)->values();
        $ordered->splice(max(0, min($position, $ordered->count())), 0, [$moving]);
        $this->renumber($ordered);
    }

    private function renumber(Collection $models): void
    {
        $models->values()->each(fn ($model, $i) => $model->position === $i ?: $model->update(['position' => $i]));
    }

    private function deleteFileIfUnused(): void
    {
        $path = $this->plan->file_path;
        if ($path && ! NutritionPlan::where('file_path', $path)->whereKeyNot($this->planId)->exists()) {
            Storage::disk('public')->delete($path);
        }
    }

    private function refresh(): void
    {
        unset($this->plan, $this->activeMeal);
    }

    private function saved(string $message = 'Guardado'): void
    {
        $this->dispatch('saved', message: $message);
    }
}
