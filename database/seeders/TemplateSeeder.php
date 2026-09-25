<?php

namespace Database\Seeders;

use App\Enums\Goal;
use App\Enums\Level;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\NutritionPlan;
use App\Models\WorkoutPlan;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->workout('Full Body principiante (3 días)', Level::Beginner, Goal::GeneralFitness, 'Ideal para empezar: todo el cuerpo tres veces por semana.', [
            ['Día A', 1, ['Sentadilla goblet', 'Press de banca con mancuernas', 'Jalón al pecho', 'Elevaciones laterales', 'Plancha']],
            ['Día B', 3, ['Prensa de piernas', 'Remo sentado en polea', 'Press de hombro con mancuernas', 'Puente de glúteos', 'Crunch abdominal']],
            ['Día C', 5, ['Zancadas con mancuernas', 'Flexiones de pecho', 'Remo con mancuerna a una mano', 'Curl alterno con mancuernas', 'Extensión de tríceps en polea']],
        ]);

        $this->workout('Push / Pull / Legs (6 días)', Level::Advanced, Goal::MuscleGain, 'Empuje, jalón y pierna dos veces por semana.', [
            ['Empuje', 1, ['Press de banca con barra', 'Press inclinado con mancuernas', 'Press militar con barra', 'Elevaciones laterales', 'Extensión de tríceps en polea', 'Press francés']],
            ['Jalón', 2, ['Dominadas', 'Remo con barra', 'Remo sentado en polea', 'Face pull', 'Curl con barra', 'Curl martillo']],
            ['Pierna', 3, ['Sentadilla con barra', 'Peso muerto rumano', 'Prensa de piernas', 'Curl femoral acostado', 'Elevación de talones de pie']],
            ['Empuje 2', 4, ['Press de banca con mancuernas', 'Cruce de poleas', 'Press de hombro con mancuernas', 'Pájaros (deltoide posterior)', 'Fondos en paralelas']],
            ['Jalón 2', 5, ['Jalón al pecho', 'Remo con mancuerna a una mano', 'Pullover en polea', 'Encogimientos con mancuernas', 'Curl en polea']],
            ['Pierna 2', 6, ['Sentadilla búlgara', 'Hip thrust con barra', 'Extensión de cuádriceps', 'Curl femoral acostado', 'Plancha']],
        ]);

        $this->workout('Torso / Pierna (4 días)', Level::Intermediate, Goal::MuscleGain, 'Buen equilibrio entre volumen y descanso.', [
            ['Torso A', 1, ['Press de banca con barra', 'Remo con barra', 'Press de hombro con mancuernas', 'Jalón al pecho', 'Curl alterno con mancuernas', 'Extensión de tríceps en polea']],
            ['Pierna A', 2, ['Sentadilla con barra', 'Peso muerto rumano', 'Zancadas con mancuernas', 'Elevación de talones de pie', 'Plancha']],
            ['Torso B', 4, ['Press inclinado con mancuernas', 'Dominadas', 'Elevaciones laterales', 'Remo sentado en polea', 'Curl martillo', 'Press francés']],
            ['Pierna B', 5, ['Prensa de piernas', 'Hip thrust con barra', 'Curl femoral acostado', 'Extensión de cuádriceps', 'Elevación de piernas']],
        ]);

        $this->workout('Glúteos y piernas (3 días)', Level::Intermediate, Goal::Toning, 'Enfoque en tren inferior con un día de tren superior.', [
            ['Glúteo', 1, ['Hip thrust con barra', 'Sentadilla búlgara', 'Patada de glúteo en polea', 'Abducción de cadera en máquina', 'Caminata lateral con banda']],
            ['Tren superior', 3, ['Jalón al pecho', 'Press de hombro con mancuernas', 'Remo sentado en polea', 'Elevaciones laterales', 'Plancha']],
            ['Pierna', 5, ['Sentadilla goblet', 'Peso muerto rumano', 'Prensa de piernas', 'Curl femoral acostado', 'Puente de glúteos']],
        ]);

        $this->workout('Quema de grasa HIIT (3 días)', Level::Beginner, Goal::WeightLoss, 'Circuitos cortos con cardio para quemar calorías.', [
            ['Circuito 1', 1, ['Burpees', 'Sentadilla libre', 'Mountain climbers', 'Kettlebell swing', 'Caminadora']],
            ['Circuito 2', 3, ['Jumping jacks', 'Zancadas con mancuernas', 'Flexiones de pecho', 'Russian twist', 'Bicicleta estática']],
            ['Circuito 3', 5, ['Saltar la cuerda', 'Thruster con mancuernas', 'Remo con mancuerna a una mano', 'Plancha', 'Elíptica']],
        ]);

        $this->nutrition('Déficit 1600 kcal', Goal::WeightLoss, 'Beber 2-3 litros de agua al día. Evitar azúcares refinados.', [
            ['Desayuno', '07:00', [['Clara de huevo', 3], ['Huevo entero', 1], ['Avena en hojuelas', 1], ['Manzana', 1], ['Café negro', 1]]],
            ['Merienda AM', '10:00', [['Yogurt griego natural', 1], ['Almendras', 1]]],
            ['Almuerzo', '12:30', [['Pechuga de pollo a la plancha', 1], ['Arroz integral cocido', 0.75], ['Ensalada verde', 1], ['Aceite de oliva', 0.5]]],
            ['Merienda PM', '16:00', [['Atún en agua', 1], ['Zanahoria', 1], ['Pepino', 1]]],
            ['Cena', '19:00', [['Tilapia al horno', 1], ['Brócoli al vapor', 1], ['Camote cocido', 0.5], ['Aguacate', 1]]],
        ]);

        $this->nutrition('Volumen limpio 3000 kcal', Goal::MuscleGain, 'Comer cada 3 horas. Priorizar proteína en cada comida.', [
            ['Desayuno', '07:00', [['Huevo entero', 4], ['Avena en hojuelas', 1.5], ['Banano', 1], ['Mantequilla de maní', 1]]],
            ['Merienda AM', '10:00', [['Proteína whey', 1], ['Leche descremada', 1], ['Pan integral', 2]]],
            ['Almuerzo', '13:00', [['Carne de res magra', 1], ['Arroz blanco cocido', 2], ['Frijoles negros cocidos', 1], ['Aguacate', 1], ['Ensalada verde', 1]]],
            ['Merienda PM', '16:00', [['Yogurt griego natural', 1], ['Nueces', 1], ['Banano', 1]]],
            ['Cena', '19:30', [['Pechuga de pollo a la plancha', 1], ['Pasta cocida', 1.5], ['Vegetales salteados', 1], ['Aceite de oliva', 1]]],
        ]);

        $this->nutrition('Mantenimiento 2200 kcal', Goal::Maintenance, 'Plan balanceado para mantener el peso.', [
            ['Desayuno', '07:00', [['Gallo pinto', 1], ['Huevo entero', 2], ['Papaya', 1], ['Café negro', 1]]],
            ['Merienda AM', '10:00', [['Banano', 1], ['Mantequilla de maní', 1], ['Leche descremada', 1]]],
            ['Almuerzo', '12:30', [['Pechuga de pollo a la plancha', 1], ['Arroz blanco cocido', 1.5], ['Frijoles negros cocidos', 1], ['Ensalada verde', 1], ['Aguacate', 1]]],
            ['Merienda PM', '16:00', [['Yogurt griego natural', 1], ['Fresas', 1]]],
            ['Cena', '19:00', [['Salmón', 1], ['Papa cocida', 1], ['Vegetales salteados', 1]]],
        ]);
    }

    /** @param  list<array{0: string, 1: int, 2: list<string>}>  $days */
    private function workout(string $title, Level $level, Goal $goal, string $description, array $days): void
    {
        $plan = WorkoutPlan::updateOrCreate(['title' => $title, 'user_id' => null], ['level' => $level, 'goal' => $goal, 'description' => $description]);
        $plan->days()->delete();

        foreach ($days as $position => [$name, $weekday, $exerciseNames]) {
            $day = $plan->days()->create(['name' => $name, 'weekday' => $weekday, 'position' => $position]);
            $exercises = Exercise::whereIn('name', $exerciseNames)->get()->keyBy('name');

            foreach ($exerciseNames as $order => $exerciseName) {
                $exercise = $exercises[$exerciseName];
                $day->items()->create([
                    'exercise_id' => $exercise->id,
                    'sets' => $exercise->default_sets,
                    'reps' => $exercise->default_reps,
                    'rest_seconds' => $exercise->default_rest_seconds,
                    'position' => $order,
                ]);
            }
        }
    }

    /** @param  list<array{0: string, 1: string, 2: list<array{0: string, 1: float}>}>  $meals */
    private function nutrition(string $title, Goal $goal, string $notes, array $meals): void
    {
        $plan = NutritionPlan::updateOrCreate(['title' => $title, 'user_id' => null], ['goal' => $goal, 'notes' => $notes]);
        $plan->meals()->delete();

        foreach ($meals as $position => [$name, $time, $items]) {
            $meal = $plan->meals()->create(['name' => $name, 'time' => $time, 'position' => $position]);
            $foods = Food::whereIn('name', array_column($items, 0))->get()->keyBy('name');

            foreach ($items as $order => [$foodName, $servings]) {
                $meal->items()->create(['food_id' => $foods[$foodName]->id, 'servings' => $servings, 'position' => $order]);
            }
        }
    }
}
