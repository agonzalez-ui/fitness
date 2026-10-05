<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Cuenta de entrenador para desarrollo local (nunca en producción):
 *   coach@corre.com / demo1234
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        User::updateOrCreate(['email' => 'coach@corre.com'], [
            'name' => 'Coach', 'password' => 'demo1234', 'role' => Role::Admin, 'email_verified_at' => now(),
        ]);
    }
}
