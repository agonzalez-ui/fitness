<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function admin(array $attributes = []): User
{
    return User::factory()->create(['role' => Role::Admin, ...$attributes]);
}

function client(array $attributes = []): User
{
    return User::factory()->create(['role' => Role::Client, ...$attributes]);
}
