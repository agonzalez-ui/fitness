<?php

namespace App\Models;

use App\Enums\Goal;
use App\Enums\MembershipStatus;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'role', 'active', 'phone', 'goal', 'birth_date', 'gender', 'height_cm', 'avatar_path', 'trainer_notes'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'goal' => Goal::class,
            'active' => 'boolean',
            'birth_date' => 'date',
            'height_cm' => 'decimal:1',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isClient(): bool
    {
        return $this->role === Role::Client;
    }

    #[Scope]
    protected function clients(Builder $query): void
    {
        $query->where('role', Role::Client);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('active', true);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class)->latest('ends_on');
    }

    /** La membresía que termina más tarde, esté vigente o no. */
    public function latestMembership(): HasOne
    {
        return $this->hasOne(Membership::class)->ofMany('ends_on', 'max');
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function activeWorkoutPlan(): HasOne
    {
        return $this->hasOne(WorkoutPlan::class)->where('active', true)->latestOfMany();
    }

    public function nutritionPlans(): HasMany
    {
        return $this->hasMany(NutritionPlan::class);
    }

    public function activeNutritionPlan(): HasOne
    {
        return $this->hasOne(NutritionPlan::class)->where('active', true)->latestOfMany();
    }

    public function progressEntries(): HasMany
    {
        return $this->hasMany(ProgressEntry::class)->orderBy('recorded_on');
    }

    public function latestProgress(): HasOne
    {
        return $this->hasOne(ProgressEntry::class)->ofMany('recorded_on', 'max');
    }

    public function progressPhotos(): HasMany
    {
        return $this->hasMany(ProgressPhoto::class)->latest('taken_on');
    }

    public function workoutLogs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class);
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    protected function membershipStatus(): Attribute
    {
        return Attribute::get(fn () => MembershipStatus::fromEndDate($this->latestMembership?->ends_on));
    }

    protected function firstName(): Attribute
    {
        return Attribute::get(fn () => str($this->name)->before(' ')->toString());
    }

    protected function initials(): Attribute
    {
        return Attribute::get(fn () => mb_strtoupper(str($this->name)->explode(' ')->filter()->take(2)
            ->map(fn ($part) => mb_substr($part, 0, 1))->implode('')));
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null);
    }

    protected function age(): Attribute
    {
        return Attribute::get(fn () => $this->birth_date?->age);
    }
}
