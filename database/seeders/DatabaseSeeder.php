<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Solo catálogos y plantillas. Los usuarios reales llegan con `php artisan fitness:import-legacy`
     * y los de prueba con `php artisan db:seed --class=DemoSeeder`.
     */
    public function run(): void
    {
        $this->call([PlanSeeder::class, ExerciseSeeder::class, FoodSeeder::class, TemplateSeeder::class]);
    }
}
