<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('changes the password and shows a persistent confirmation beside the form', function (Role $role) {
    $user = User::factory()->create(['role' => $role]);
    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'password', 'password' => 'NuevaClave123', 'password_confirmation' => 'NuevaClave123',
    ])->assertRedirect(route('profile.edit').'#change-password')->assertSessionHas('password_updated', true);

    expect(Hash::check('NuevaClave123', $user->fresh()->password))->toBeTrue()
        ->and(Hash::check('password', $user->fresh()->password))->toBeFalse();
    $this->get(route('profile.edit'))->assertOk()->assertSee('La próxima vez que inicies sesión, usa tu nueva contraseña.');

    $this->post(route('logout'));
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'NuevaClave123'])
        ->assertRedirect(route($role->home()));
    $this->assertAuthenticatedAs($user);
})->with([Role::Client, Role::Admin]);

it('explains validation failures without modifying the password or flashing secrets', function (array $input, string $field, string $message) {
    $user = client();
    $hash = $user->password;
    $this->actingAs($user)->put(route('profile.password'), $input)
        ->assertRedirect(route('profile.edit').'#change-password')
        ->assertSessionHasErrorsIn('password', $field)
        ->assertSessionMissing('_old_input.password')
        ->assertSessionMissing('_old_input.current_password');
    expect($user->fresh()->password)->toBe($hash);
    $this->withCookie(config('session.cookie'), session()->getId())
        ->get(route('profile.edit'))->assertOk()->assertSee('No se cambió la contraseña.')->assertSee($message);
})->with([
    'current password wrong' => [['current_password' => 'incorrecta', 'password' => 'NuevaClave123', 'password_confirmation' => 'NuevaClave123'], 'current_password', 'La contraseña actual no es correcta.'],
    'confirmation differs' => [['current_password' => 'password', 'password' => 'NuevaClave123', 'password_confirmation' => 'OtraClave123'], 'password', 'Las contraseñas nuevas no coinciden.'],
    'password too short' => [['current_password' => 'password', 'password' => 'abc123', 'password_confirmation' => 'abc123'], 'password', 'al menos 8 caracteres'],
    'password has no number' => [['current_password' => 'password', 'password' => 'SoloLetras', 'password_confirmation' => 'SoloLetras'], 'password', 'al menos un número'],
    'password has no letter' => [['current_password' => 'password', 'password' => '123456789', 'password_confirmation' => '123456789'], 'password', 'al menos una letra'],
    'confirmation missing' => [['current_password' => 'password', 'password' => 'NuevaClave123'], 'password_confirmation', 'Repite la nueva contraseña.'],
]);
