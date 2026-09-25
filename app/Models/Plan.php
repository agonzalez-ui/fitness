<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'duration_months', 'price', 'description', 'active'])]
class Plan extends Model
{
    use Auditable, HasFactory;

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'active' => 'boolean', 'duration_months' => 'integer'];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('active', true);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}
