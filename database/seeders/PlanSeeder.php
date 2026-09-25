<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Básico 1 mes', 'duration_months' => 1, 'price' => 25, 'description' => 'Rutina y seguimiento mensual.'],
            ['name' => 'Trimestral', 'duration_months' => 3, 'price' => 65, 'description' => 'Rutina, nutrición y seguimiento.'],
            ['name' => 'Premium 6 meses', 'duration_months' => 6, 'price' => 200, 'description' => 'Todo incluido con prioridad en mensajes.'],
            ['name' => 'Anual', 'duration_months' => 12, 'price' => 360, 'description' => 'El mejor precio por mes.'],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
