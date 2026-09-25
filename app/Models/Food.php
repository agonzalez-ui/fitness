<?php

namespace App\Models;

use App\Enums\FoodCategory;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category', 'portion_label', 'calories', 'protein_g', 'carbs_g', 'fats_g', 'active'])]
class Food extends Model
{
    use Auditable, HasFactory;

    protected $table = 'foods';

    protected function casts(): array
    {
        return [
            'category' => FoodCategory::class,
            'calories' => 'integer',
            'protein_g' => 'float',
            'carbs_g' => 'float',
            'fats_g' => 'float',
            'active' => 'boolean',
        ];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('active', true);
    }
}
