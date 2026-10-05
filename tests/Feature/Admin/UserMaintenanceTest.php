<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->coach = admin();
    $this->actingAs($this->coach);
});

it('finds users by email role and account status', function () {
    $client = client(['name' => 'Cliente Buscado', 'email' => 'buscar@example.com']);
    client(['name' => 'Cliente Deshabilitado', 'active' => false]);

    $this->get(route('admin.users.index', ['q' => 'buscar@example.com', 'rol' => 'client', 'estado' => 'active']))
        ->assertOk()->assertSee($client->name)->assertDontSee('Cliente Deshabilitado');
    $this->get(route('admin.users.index', ['estado' => 'inactive']))
        ->assertOk()->assertSee('Cliente Deshabilitado')->assertDontSee($client->name);
});

it('creates a trainer with normalized data and a hashed temporary password', function () {
    $response = $this->post(route('admin.users.store'), [
        'name' => '  Otro   Coach  ', 'email' => 'COACH2@example.com', 'role' => 'admin',
    ]);
    $user = User::firstWhere('email', 'coach2@example.com');
    $response->assertRedirect(route('admin.users.edit', $user))->assertSessionHas('credentials');
    $password = session('credentials')['password'];
    expect($user->name)->toBe('Otro Coach')
        ->and($user->role)->toBe(Role::Admin)
        ->and(Hash::check($password, $user->password))->toBeTrue();
    $this->get(route('admin.users.edit', $user))->assertOk()->assertSee($password)->assertDontSee('@js($message)', false);
});

it('updates users without resetting their password', function () {
    $user = client();
    $hash = $user->password;
    $this->put(route('admin.users.update', $user), ['name' => 'Nombre Actualizado', 'email' => $user->email, 'role' => 'client'])
        ->assertRedirect(route('admin.users.edit', $user));
    expect($user->fresh()->name)->toBe('Nombre Actualizado')->and($user->fresh()->password)->toBe($hash);
});

it('disables and enables accounts while retaining users and removing their sessions', function () {
    $user = client();
    DB::table('sessions')->insert(['id' => 'test-user-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
    $this->post(route('admin.users.toggle', $user))->assertSessionHas('success');
    expect($user->fresh()->active)->toBeFalse();
    $this->assertDatabaseMissing('sessions', ['id' => 'test-user-session']);
    $this->assertDatabaseHas('users', ['id' => $user->id]);
    $this->post(route('admin.users.toggle', $user));
    expect($user->fresh()->active)->toBeTrue();
});

it('prevents disabling or demoting the current trainer', function () {
    $this->post(route('admin.users.toggle', $this->coach))->assertSessionHasErrors('role');
    $this->put(route('admin.users.update', $this->coach), ['name' => $this->coach->name, 'email' => $this->coach->email, 'role' => 'client'])
        ->assertSessionHasErrors('role');
    expect($this->coach->fresh()->active)->toBeTrue()->and($this->coach->fresh()->role)->toBe(Role::Admin);
    $this->delete(route('admin.clients.destroy', $this->coach))->assertNotFound();
});

it('allows managing another trainer while keeping the current trainer active', function () {
    $other = admin();
    $this->put(route('admin.users.update', $other), ['name' => $other->name, 'email' => $other->email, 'role' => 'client'])
        ->assertSessionHasNoErrors();
    expect($other->fresh()->role)->toBe(Role::Client);
});

it('rejects duplicate emails and unknown roles', function () {
    $this->post(route('admin.users.store'), ['name' => 'Duplicado', 'email' => $this->coach->email, 'role' => 'superadmin'])
        ->assertSessionHasErrors(['email', 'role']);
});

it('keeps user maintenance inaccessible to clients', function () {
    $user = client();
    $this->actingAs($user)->get(route('admin.users.index'))->assertRedirect(route('client.dashboard'));
    $this->post(route('admin.users.store'), ['name' => 'Sin permiso', 'email' => 'intruso@example.com', 'role' => 'admin'])
        ->assertRedirect(route('client.dashboard'));
    $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
});

it('opens the user creation form and maintenance menu', function () {
    $this->get(route('admin.users.create'))->assertOk()->assertSee('Nuevo usuario');
    $this->get(route('admin.users.index'))->assertOk()->assertSee('Nuevo usuario');
    $this->get(route('admin.catalog.index'))->assertOk()->assertSee(['Mantenimientos', 'Usuarios', 'Ejercicios']);
});
