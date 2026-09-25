<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)->beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('a visitor can register and is authenticated', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirectToRoute('users.index');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Ana Silva',
        'email' => 'ana@example.com',
    ]);
});

test('a user can log in and log out', function () {
    $user = User::factory()->create([
        'email' => 'ana@example.com',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirectToRoute('users.index');
    $this->assertAuthenticatedAs($user);

    $response = $this->post(route('logout'));

    $response->assertRedirectToRoute('login');
    $this->assertGuest();
});

test('a guest cannot access the user crud', function () {
    $this->get(route('users.index'))
        ->assertRedirectToRoute('login');
});

test('an authenticated user can manage another user', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $this->post(route('users.store'), [
        'name' => 'Bruno Costa',
        'email' => 'bruno@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirectToRoute('users.index');

    $user = User::where('email', 'bruno@example.com')->firstOrFail();

    $this->put(route('users.update', $user), [
        'name' => 'Bruno Atualizado',
        'email' => 'bruno@example.com',
        'password' => '',
        'password_confirmation' => '',
    ])->assertRedirectToRoute('users.index');

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Bruno Atualizado']);

    $this->delete(route('users.destroy', $user))
        ->assertRedirectToRoute('users.index');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
