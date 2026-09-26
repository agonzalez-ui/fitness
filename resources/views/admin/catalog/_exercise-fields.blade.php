{{-- $bag, $useOld, $exercise (nullable), $prefix para ids únicos --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <x-input :bag="$bag" :use-old="$useOld" name="name" :id="$prefix.'name'" label="Nombre" :value="$exercise?->name ?? request('nuevo')" required class="sm:col-span-2" />
    <x-select :bag="$bag" :use-old="$useOld" name="muscle_group" :id="$prefix.'muscle_group'" label="Grupo muscular" :value="$exercise?->muscle_group" :options="\App\Enums\MuscleGroup::options()" />
    <x-select :bag="$bag" :use-old="$useOld" name="equipment" :id="$prefix.'equipment'" label="Equipo" :value="$exercise?->equipment" :options="\App\Enums\Equipment::options()" />
    <x-input :bag="$bag" :use-old="$useOld" name="default_sets" :id="$prefix.'default_sets'" type="number" min="1" max="10" label="Series sugeridas" :value="$exercise?->default_sets ?? 3" />
    <x-input :bag="$bag" :use-old="$useOld" name="default_reps" :id="$prefix.'default_reps'" label="Reps sugeridas" :value="$exercise?->default_reps ?? '10-12'" list="reps-presets" />
    <x-input :bag="$bag" :use-old="$useOld" name="default_rest_seconds" :id="$prefix.'default_rest_seconds'" type="number" min="0" max="600" step="15" label="Descanso" suffix="seg" :value="$exercise?->default_rest_seconds ?? 60" />
    <x-input :bag="$bag" :use-old="$useOld" name="video_url" :id="$prefix.'video_url'" type="url" label="Video (opcional)" :value="$exercise?->video_url" placeholder="https://youtube.com/…" />
    <x-textarea :bag="$bag" :use-old="$useOld" name="instructions" :id="$prefix.'instructions'" label="Técnica / instrucciones" :value="$exercise?->instructions" rows="2" class="sm:col-span-2 lg:col-span-4" />
</div>
