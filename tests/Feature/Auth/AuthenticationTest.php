<?php

use App\Enums\Goal;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('shows the login and landing pages', function () {
    $this->get('/')->assertOk()->assertSee('Crear mi cuenta gratis');
    $this->get('/login')->assertOk()->assertSee('Entrar');
});

it('sends each role to its own home after login', function (Role $role, string $home) {
    $user = User::factory()->create(['role' => $role, 'email' => 'coach@example.com']);

    $this->post('/login', ['email' => ' COACH@example.com ', 'password' => 'password'])
        ->assertRedirect(route($home));

    $this->assertAuthenticatedAs($user);
})->with([
    'admin' => [Role::Admin, 'admin.dashboard'],
    'client' => [Role::Client, 'client.dashboard'],
]);

it('rejects wrong passwords with a friendly message', function () {
    client(['email' => 'maria@example.com']);

    $this->from('/login')->post('/login', ['email' => 'maria@example.com', 'password' => 'incorrecta'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.']);

    $this->assertGuest();
});

it('blocks deactivated accounts at login and on later requests', function () {
    $user = client(['email' => 'off@example.com', 'active' => false]);

    $this->post('/login', ['email' => 'off@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();

    $this->actingAs($user)->get('/app')->assertRedirect('/login');
    $this->assertGuest();
});

it('accepts bcrypt hashes imported from the old site', function () {
    // Hash con costo 10, como los del sitio anterior (Laravel usa 12 por defecto).
    DB::table('users')->insert([
        'name' => 'Legacy', 'email' => 'legacy@example.com', 'role' => 'client', 'active' => true,
        'password' => password_hash('Prueba.123', PASSWORD_BCRYPT, ['cost' => 10]),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->post('/login', ['email' => 'legacy@example.com', 'password' => 'Prueba.123'])
        ->assertRedirect(route('client.dashboard'));
});

it('registers new clients with their goal', function () {
    $this->post('/registro', [
        'name' => '  Ana   Mora ',
        'email' => 'Ana@Example.com',
        'goal' => Goal::Toning->value,
        'password' => 'secreta123',
        'password_confirmation' => 'secreta123',
    ])->assertRedirect(route('client.dashboard'));

    $user = User::firstWhere('email', 'ana@example.com');
    expect($user->name)->toBe('Ana Mora')
        ->and($user->role)->toBe(Role::Client)
        ->and($user->goal)->toBe(Goal::Toning);
});

it('keeps each role inside its own area', function () {
    $this->actingAs(client())->get('/admin')->assertRedirect(route('client.dashboard'));
    $this->actingAs(admin())->get('/app')->assertRedirect(route('admin.dashboard'));
});

it('logs out', function () {
    $this->actingAs(client())->post('/logout')->assertRedirect('/login');
    $this->assertGuest();
});
