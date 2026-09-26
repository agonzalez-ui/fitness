{{-- $bag, $useOld, $food (nullable), $prefix --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <x-input :bag="$bag" :use-old="$useOld" name="name" :id="$prefix.'name'" label="Nombre" :value="$food?->name ?? request('nuevo')" required class="sm:col-span-2" />
    <x-select :bag="$bag" :use-old="$useOld" name="category" :id="$prefix.'category'" label="Categoría" :value="$food?->category" :options="\App\Enums\FoodCategory::options()" />
    <x-input :bag="$bag" :use-old="$useOld" name="portion_label" :id="$prefix.'portion_label'" label="Porción" :value="$food?->portion_label" placeholder="1 taza, 100 g, 1 unidad…" hint="Los valores de abajo son por esta porción." />
    <x-input :bag="$bag" :use-old="$useOld" name="calories" :id="$prefix.'calories'" type="number" min="0" label="Calorías" suffix="kcal" :value="$food?->calories" inputmode="numeric" />
    <x-input :bag="$bag" :use-old="$useOld" name="protein_g" :id="$prefix.'protein_g'" type="number" min="0" step="0.1" label="Proteína" suffix="g" :value="$food?->protein_g ?? 0" inputmode="decimal" />
    <x-input :bag="$bag" :use-old="$useOld" name="carbs_g" :id="$prefix.'carbs_g'" type="number" min="0" step="0.1" label="Carbohidratos" suffix="g" :value="$food?->carbs_g ?? 0" inputmode="decimal" />
    <x-input :bag="$bag" :use-old="$useOld" name="fats_g" :id="$prefix.'fats_g'" type="number" min="0" step="0.1" label="Grasa" suffix="g" :value="$food?->fats_g ?? 0" inputmode="decimal" />
</div>
