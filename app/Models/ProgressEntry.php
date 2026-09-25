<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'recorded_on', 'weight_kg', 'body_fat_pct', 'waist_cm', 'hips_cm', 'chest_cm', 'arms_cm', 'legs_cm', 'notes'])]
class ProgressEntry extends Model
{
    use HasFactory;

    public const MEASURES = [
        'weight_kg' => ['Peso', 'kg'],
        'body_fat_pct' => ['Grasa corporal', '%'],
        'waist_cm' => ['Cintura', 'cm'],
        'hips_cm' => ['Cadera', 'cm'],
        'chest_cm' => ['Pecho', 'cm'],
        'arms_cm' => ['Brazos', 'cm'],
        'legs_cm' => ['Piernas', 'cm'],
    ];

    protected function casts(): array
    {
        return array_fill_keys(array_keys(self::MEASURES), 'float') + ['recorded_on' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
