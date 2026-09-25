<x-layouts.admin title="Nuevo cliente">
    <x-page-header title="Nuevo cliente" subtitle="Déjalo listo en un solo paso: datos, membresía, rutina y dieta." :back="route('admin.clients.index')" />

    <form method="POST" action="{{ route('admin.clients.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        <x-card title="1. Datos del cliente" subtitle="La contraseña se genera sola y podrás enviársela por WhatsApp.">
            @include('admin.clients._profile-fields')
        </x-card>

        <x-card title="2. Membresía" subtitle="Opcional. Empieza hoy.">
            <x-radio-cards name="plan_id" columns="sm:grid-cols-2">
                <x-radio-card name="plan_id" value="" title="Todavía no" meta="La activo después" :checked="! old('plan_id')" />
                @foreach ($plans as $plan)
                    <x-radio-card name="plan_id" :value="$plan->id" :title="$plan->name" :meta="$plan->description" :aside="money($plan->price)" :checked="old('plan_id') == $plan->id" />
                @endforeach
            </x-radio-cards>
            <x-choice-chips name="payment_method" label="¿Cómo pagó?" :options="\App\Enums\PaymentMethod::options()" :value="old('payment_method', 'sinpe')" class="mt-5" />
        </x-card>

        <x-card title="3. Rutina" subtitle="Opcional. Elige una plantilla y la ajustas después.">
            <x-radio-cards name="workout_template_id" columns="sm:grid-cols-2">
                <x-radio-card name="workout_template_id" value="" title="Todavía no" meta="La asigno después" :checked="! old('workout_template_id')" />
                @foreach ($workoutTemplates as $template)
                    <x-radio-card name="workout_template_id" :value="$template->id" :title="$template->title"
                        :meta="$template->level->label().' · '.$template->days_count.' '.str('día')->plural($template->days_count)" :checked="old('workout_template_id') == $template->id" />
                @endforeach
            </x-radio-cards>
        </x-card>

        <x-card title="4. Plan de alimentación" subtitle="Opcional.">
            <x-radio-cards name="nutrition_template_id" columns="sm:grid-cols-2">
                <x-radio-card name="nutrition_template_id" value="" title="Todavía no" meta="Lo asigno después" :checked="! old('nutrition_template_id')" />
                @foreach ($nutritionTemplates as $template)
                    <x-radio-card name="nutrition_template_id" :value="$template->id" :title="$template->title"
                        :meta="$template->totals['calories'].' kcal · '.round($template->totals['protein_g']).' g proteína'" :checked="old('nutrition_template_id') == $template->id" />
                @endforeach
            </x-radio-cards>
        </x-card>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-button :href="route('admin.clients.index')" variant="ghost">Cancelar</x-button>
            <x-button size="lg" icon="check">Crear cliente</x-button>
        </div>
    </form>
</x-layouts.admin>
