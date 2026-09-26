<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'path', 'pose', 'taken_on'])]
class ProgressPhoto extends Model
{
    public const POSES = ['front' => 'Frente', 'side' => 'Perfil', 'back' => 'Espalda'];

    protected function casts(): array
    {
        return ['taken_on' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => route('photos.show', $this));
    }
}
