<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum FoodCategory: string
{
    use HasOptions;

    case Protein = 'protein';
    case Carbs = 'carbs';
    case Fats = 'fats';
    case Vegetables = 'vegetables';
    case Fruits = 'fruits';
    case Dairy = 'dairy';
    case Drinks = 'drinks';
    case Supplements = 'supplements';

    public function label(): string
    {
        return match ($this) {
            self::Protein => 'Proteínas',
            self::Carbs => 'Carbohidratos',
            self::Fats => 'Grasas saludables',
            self::Vegetables => 'Vegetales',
            self::Fruits => 'Frutas',
            self::Dairy => 'Lácteos',
            self::Drinks => 'Bebidas',
            self::Supplements => 'Suplementos',
        };
    }
}
