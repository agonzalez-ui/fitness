<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use App\Enums\PaymentMethod;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'plan_id', 'starts_on', 'ends_on', 'price', 'payment_method', 'notes', 'created_by'])]
class Membership extends Model
{
    use Auditable, HasFactory;

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'price' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function status(): Attribute
    {
        return Attribute::get(fn () => MembershipStatus::fromEndDate($this->ends_on));
    }

    protected function daysLeft(): Attribute
    {
        return Attribute::get(fn () => (int) max(0, today()->diffInDays($this->ends_on, false)));
    }
}
