<?php

namespace App\Models;

use App\Enums\Equipment;
use App\Enums\MuscleGroup;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'muscle_group', 'equipment', 'instructions', 'video_url', 'image_path', 'default_sets', 'default_reps', 'default_rest_seconds', 'active'])]
class Exercise extends Model
{
    use Auditable, HasFactory;

    protected function casts(): array
    {
        return [
            'muscle_group' => MuscleGroup::class,
            'equipment' => Equipment::class,
            'active' => 'boolean',
        ];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('active', true);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path ? Storage::disk('public')->url($this->image_path) : null);
    }

    /** Enlace de búsqueda en YouTube cuando no hay video propio. */
    protected function videoLink(): Attribute
    {
        return Attribute::get(fn () => $this->video_url ?: 'https://www.youtube.com/results?search_query='.urlencode($this->name.' técnica'));
    }
}
