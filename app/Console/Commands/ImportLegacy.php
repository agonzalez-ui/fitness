<?php

namespace App\Console\Commands;

use App\Enums\Goal;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Models\Plan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

#[Signature('fitness:import-legacy')]
#[Description('Importa usuarios, membresías, progreso, mensajes y planes desde la BD del sitio anterior')]
class ImportLegacy extends Command
{
    /** Planes de texto libre del sitio anterior → nombre en la tabla plans. */
    private const PLAN_MAP = [
        'basic' => 'Básico 1 mes',
        'basic_1m' => 'Básico 1 mes',
        'standard_3m' => 'Trimestral',
        'premium_6m' => 'Premium 6 meses',
        'annual_12m' => 'Anual',
    ];

    private Connection $legacy;

    public function handle(): int
    {
        $this->legacy = DB::connection('legacy');

        if (DB::table('users')->exists()) {
            $this->error('La BD nueva ya tiene usuarios. Ejecute primero: php artisan migrate:fresh --seed');

            return self::FAILURE;
        }

        if (! Plan::exists()) {
            $this->error('Faltan los planes. Ejecute primero: php artisan db:seed');

            return self::FAILURE;
        }

        DB::transaction(function () {
            $this->importUsers();
            $this->importMemberships();
            $this->importProgress();
            $this->importMessages();
            $this->importWorkoutPlans();
            $this->importNutritionPlans();
        });

        $this->newLine();
        $this->info('Importación completa. Las contraseñas se conservan: cada usuario entra con la misma clave.');
        $this->warn('Copie los archivos del servidor viejo (carpeta uploads) a storage/app/public/legacy/ para ver fotos y PDFs.');

        return self::SUCCESS;
    }

    private function importUsers(): void
    {
        $users = $this->legacy->table('users')->get();

        // Inserción directa: los hashes bcrypt viejos se copian tal cual (el cast "hashed" los rechazaría por tener otro costo).
        foreach ($users as $user) {
            DB::table('users')->insert([
                'id' => $user->id,
                'name' => $user->name,
                'email' => mb_strtolower($user->email),
                'password' => $user->password,
                'role' => $user->role === 'admin' ? Role::Admin->value : Role::Client->value,
                'active' => $user->status === 'active',
                'phone' => $user->phone ?: null,
                'goal' => Goal::fromLegacy($user->goal)?->value,
                'email_verified_at' => $user->created_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        $this->line("  Usuarios: {$users->count()}");
    }

    private function importMemberships(): void
    {
        $plans = Plan::pluck('id', 'name');
        $planId = fn (string $legacy) => $plans[self::PLAN_MAP[$legacy] ?? 'Básico 1 mes'];
        $count = 0;

        foreach ($this->legacy->table('membership_history')->get() as $row) {
            DB::table('memberships')->insert([
                'user_id' => $row->user_id,
                'plan_id' => $planId($row->membership_plan),
                'starts_on' => $row->start_date,
                'ends_on' => $row->end_date,
                'price' => $row->price ?? 0,
                'payment_method' => PaymentMethod::fromLegacy($row->payment_method)->value,
                'notes' => $row->notes ?: null,
                'created_by' => $row->created_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->created_at,
            ]);
            $count++;
        }

        // Clientes con fechas en `users` pero sin historial: se crea su membresía para no perderla.
        $withHistory = $this->legacy->table('membership_history')->pluck('user_id')->all();
        $orphans = $this->legacy->table('users')->whereNotNull('membership_end_date')->whereNotIn('id', $withHistory)->get();

        foreach ($orphans as $user) {
            DB::table('memberships')->insert([
                'user_id' => $user->id,
                'plan_id' => $planId($user->membership_plan),
                'starts_on' => $user->membership_start_date ?? $user->membership_end_date,
                'ends_on' => $user->membership_end_date,
                'price' => Plan::find($planId($user->membership_plan))->price,
                'payment_method' => PaymentMethod::Transfer->value,
                'notes' => 'Importada del sitio anterior (sin historial de pago).',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->line("  Membresías: $count");
    }

    private function importProgress(): void
    {
        $entries = $this->legacy->table('client_progress')->orderBy('progress_date')->get()
            ->unique(fn ($row) => $row->user_id.'|'.$row->progress_date);

        foreach ($entries as $row) {
            DB::table('progress_entries')->insert([
                'user_id' => $row->user_id,
                'recorded_on' => $row->progress_date,
                'weight_kg' => $row->weight_kg,
                'waist_cm' => $row->waist_cm,
                'hips_cm' => $row->hips_cm,
                'chest_cm' => $row->chest_cm,
                'arms_cm' => $row->arms_cm,
                'legs_cm' => $row->legs_cm,
                'notes' => $row->notes ?: null,
                'created_at' => $row->created_at,
                'updated_at' => $row->created_at,
            ]);
        }

        $photos = $this->legacy->table('progress_photos')->get();
        foreach ($photos as $row) {
            DB::table('progress_photos')->insert([
                'user_id' => $row->user_id,
                'path' => 'legacy/'.ltrim($row->photo_path, '/'),
                'taken_on' => $row->taken_at,
                'created_at' => $row->created_at,
                'updated_at' => $row->created_at,
            ]);
        }

        $this->line("  Registros de progreso: {$entries->count()} · Fotos: {$photos->count()}");
    }

    private function importMessages(): void
    {
        $messages = $this->legacy->table('messages')->orderBy('created_at')->get();

        foreach ($messages as $row) {
            DB::table('messages')->insert([
                'sender_id' => $row->from_user_id,
                'recipient_id' => $row->to_user_id,
                'body' => $row->message,
                'read_at' => $row->is_read ? $row->created_at : null,
                'created_at' => $row->created_at,
                'updated_at' => $row->created_at,
            ]);
        }

        $this->line("  Mensajes: {$messages->count()}");
    }

    private function importWorkoutPlans(): void
    {
        $plans = $this->legacy->table('workout_plans')->get();

        foreach ($plans as $row) {
            $planId = DB::table('workout_plans')->insertGetId([
                'user_id' => $row->user_id,
                'title' => $row->title,
                'description' => $row->description ?: null,
                'level' => $row->level ?? 'beginner',
                'file_path' => $this->legacyFile($row->file_path),
                'file_name' => $row->file_name,
                'active' => true,
                'created_by' => $row->created_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            $this->importWorkoutExercises($row->id, $planId);
        }

        $this->line("  Rutinas: {$plans->count()}");
    }

    /** El sitio viejo guardaba ejercicios como texto libre por día; se agrupan en días y se vinculan al catálogo. */
    private function importWorkoutExercises(int $legacyPlanId, int $planId): void
    {
        $days = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];
        $rows = $this->legacy->table('workout_exercises')->where('workout_plan_id', $legacyPlanId)->orderBy('exercise_order')->get()
            ->groupBy(fn ($row) => array_search(str(mb_strtolower($row->day_of_week))->ascii()->toString(), $days) ?: 1);

        foreach ($rows->sortKeys() as $weekday => $exercises) {
            $dayId = DB::table('workout_days')->insertGetId([
                'workout_plan_id' => $planId, 'name' => ucfirst($days[$weekday]), 'weekday' => $weekday,
                'position' => $weekday, 'created_at' => now(), 'updated_at' => now(),
            ]);

            foreach ($exercises->values() as $position => $row) {
                $exerciseId = DB::table('exercises')->where('name', $row->name)->value('id')
                    ?? DB::table('exercises')->insertGetId([
                        'name' => $row->name, 'muscle_group' => 'full_body', 'equipment' => 'bodyweight',
                        'instructions' => $row->description, 'created_at' => now(), 'updated_at' => now(),
                    ]);

                DB::table('workout_day_exercises')->insert([
                    'workout_day_id' => $dayId, 'exercise_id' => $exerciseId, 'sets' => $row->sets,
                    'reps' => $row->reps, 'rest_seconds' => $row->rest_seconds, 'position' => $position,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    private function importNutritionPlans(): void
    {
        $plans = $this->legacy->table('nutrition_plans')->get();

        foreach ($plans as $row) {
            // En el sitio viejo `description` y `meals` repetían casi el mismo texto; se guarda el más completo como notas.
            $notes = collect([$row->description, $row->meals])->filter()->sortByDesc(fn ($text) => mb_strlen($text))->first();

            DB::table('nutrition_plans')->insert([
                'user_id' => $row->user_id,
                'title' => $row->title,
                'notes' => $notes ? str_replace("\r\n", "\n", $notes) : null,
                'file_path' => $this->legacyFile($row->file_path),
                'file_name' => $row->file_name,
                'active' => true,
                'created_by' => $row->created_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        $this->line("  Planes nutricionales: {$plans->count()}");
    }

    private function legacyFile(?string $path): ?string
    {
        return $path ? 'legacy/'.str($path)->ltrim('/')->after('uploads/') : null;
    }
}
