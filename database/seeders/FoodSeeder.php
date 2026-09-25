<?php

namespace Database\Seeders;

use App\Enums\FoodCategory as C;
use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        // [nombre, categoría, porción, kcal, proteína, carbohidratos, grasa] — valores aproximados por porción.
        $foods = [
            // Proteínas
            ['Pechuga de pollo a la plancha', C::Protein, '150 g', 248, 46.5, 0, 5.4],
            ['Huevo entero', C::Protein, '1 unidad', 72, 6.3, 0.4, 4.8],
            ['Clara de huevo', C::Protein, '1 unidad', 17, 3.6, 0.2, 0.1],
            ['Atún en agua', C::Protein, '1 lata (120 g)', 130, 29, 0, 1],
            ['Tilapia al horno', C::Protein, '150 g', 192, 39, 0, 4],
            ['Salmón', C::Protein, '150 g', 312, 33, 0, 19.5],
            ['Carne de res magra', C::Protein, '150 g', 280, 40, 0, 13],
            ['Carne molida magra', C::Protein, '120 g', 210, 26, 0, 11],
            ['Lomo de cerdo', C::Protein, '150 g', 240, 39, 0, 8.5],
            ['Pavo en rodajas', C::Protein, '4 rebanadas (60 g)', 60, 11, 1.5, 1],
            ['Frijoles negros cocidos', C::Protein, '1/2 taza', 114, 7.6, 20, 0.5],
            ['Lentejas cocidas', C::Protein, '1/2 taza', 115, 9, 20, 0.4],
            ['Tofu firme', C::Protein, '100 g', 144, 17, 3, 8.7],
            // Carbohidratos
            ['Arroz blanco cocido', C::Carbs, '1 taza', 205, 4.3, 45, 0.4],
            ['Arroz integral cocido', C::Carbs, '1 taza', 216, 5, 45, 1.8],
            ['Gallo pinto', C::Carbs, '1 taza', 280, 10, 48, 5],
            ['Avena en hojuelas', C::Carbs, '1/2 taza (40 g)', 150, 5, 27, 2.5],
            ['Pan integral', C::Carbs, '1 rebanada', 80, 4, 14, 1],
            ['Tortilla de maíz', C::Carbs, '1 unidad', 52, 1.4, 11, 0.7],
            ['Papa cocida', C::Carbs, '1 mediana (150 g)', 130, 3, 30, 0.2],
            ['Camote cocido', C::Carbs, '1 mediano (130 g)', 112, 2, 26, 0.1],
            ['Plátano maduro cocido', C::Carbs, '1/2 unidad', 110, 1, 29, 0.3],
            ['Pasta cocida', C::Carbs, '1 taza', 220, 8, 43, 1.3],
            ['Yuca cocida', C::Carbs, '100 g', 160, 1.4, 38, 0.3],
            // Grasas
            ['Aguacate', C::Fats, '1/4 unidad', 80, 1, 4, 7.3],
            ['Aceite de oliva', C::Fats, '1 cucharada', 119, 0, 0, 13.5],
            ['Almendras', C::Fats, '12 unidades', 83, 3, 3, 7],
            ['Mantequilla de maní', C::Fats, '1 cucharada', 95, 3.5, 3.5, 8],
            ['Nueces', C::Fats, '7 mitades', 92, 2, 2, 9],
            ['Semillas de chía', C::Fats, '1 cucharada', 58, 2, 5, 3.7],
            // Vegetales
            ['Ensalada verde', C::Vegetables, '2 tazas', 20, 1.5, 4, 0.2],
            ['Brócoli al vapor', C::Vegetables, '1 taza', 55, 3.7, 11, 0.6],
            ['Vegetales salteados', C::Vegetables, '1 taza', 60, 2.5, 12, 0.5],
            ['Zanahoria', C::Vegetables, '1 mediana', 25, 0.6, 6, 0.1],
            ['Tomate', C::Vegetables, '1 mediano', 22, 1, 4.8, 0.2],
            ['Pepino', C::Vegetables, '1 taza', 16, 0.7, 3.8, 0.1],
            ['Espinaca', C::Vegetables, '1 taza cruda', 7, 0.9, 1.1, 0.1],
            // Frutas
            ['Banano', C::Fruits, '1 mediano', 105, 1.3, 27, 0.4],
            ['Manzana', C::Fruits, '1 mediana', 95, 0.5, 25, 0.3],
            ['Papaya', C::Fruits, '1 taza', 55, 0.9, 14, 0.2],
            ['Piña', C::Fruits, '1 taza', 82, 0.9, 22, 0.2],
            ['Fresas', C::Fruits, '1 taza', 49, 1, 12, 0.5],
            ['Sandía', C::Fruits, '1 taza', 46, 0.9, 11.5, 0.2],
            ['Mango', C::Fruits, '1 taza', 99, 1.4, 25, 0.6],
            // Lácteos
            ['Yogurt griego natural', C::Dairy, '1 taza (170 g)', 100, 17, 6, 0.7],
            ['Leche descremada', C::Dairy, '1 taza', 83, 8.3, 12, 0.2],
            ['Leche entera', C::Dairy, '1 taza', 149, 7.7, 12, 8],
            ['Queso fresco (turrialba)', C::Dairy, '30 g', 80, 5.5, 1, 6],
            ['Requesón', C::Dairy, '1/2 taza', 90, 12, 4, 2.5],
            // Bebidas
            ['Café negro', C::Drinks, '1 taza', 2, 0.3, 0, 0],
            ['Té verde', C::Drinks, '1 taza', 2, 0, 0, 0],
            // Suplementos
            ['Proteína whey', C::Supplements, '1 scoop (30 g)', 120, 24, 3, 1.5],
            ['Creatina', C::Supplements, '5 g', 0, 0, 0, 0],
        ];

        foreach ($foods as [$name, $category, $portion, $kcal, $protein, $carbs, $fats]) {
            Food::updateOrCreate(['name' => $name], [
                'category' => $category,
                'portion_label' => $portion,
                'calories' => $kcal,
                'protein_g' => $protein,
                'carbs_g' => $carbs,
                'fats_g' => $fats,
            ]);
        }
    }
}
