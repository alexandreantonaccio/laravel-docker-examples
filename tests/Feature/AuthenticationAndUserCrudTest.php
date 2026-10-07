<?php

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Models\UserProfileOption;
use App\Notifications\VerifyUserEmail;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class)->beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    $this->seed(PermissionSeeder::class);
});

function grantPermission(User $user, string $key): void
{
    $permission = Permission::query()->where('key', $key)->firstOrFail();
    $user->permissionAdjustments()->updateOrCreate(
        ['permission_id' => $permission->id],
        ['effect' => 'grant'],
    );
}

test('default OUTRO options allow registration when no catalogs have been configured', function () {
    Notification::fake();
    Storage::fake();
    $this->seed(DatabaseSeeder::class);

    foreach (['course', 'job_title', 'employment_link'] as $type) {
        $this->assertDatabaseHas('user_profile_options', [
            'type' => $type,
            'value' => 'OUTRO',
            'active' => true,
        ]);
    }

    $this->get(route('register'))->assertOk()->assertSee('value="OUTRO"', false);

    $this->post(route('register.store'), [
        'profile' => 'aluno',
        'functional_id' => '20260010',
        'name' => 'Ana Silva',
        'email' => 'ana.outro@ufam.edu.br',
        'phone' => '92999990000',
        'course' => 'OUTRO',
        'enrollment_proof' => UploadedFile::fake()->create('matricula.pdf', 80, 'application/pdf'),
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertRedirectToRoute('login');

    $this->post(route('register.store'), [
        'profile' => 'tecnico',
        'functional_id' => '20260011',
        'name' => 'Bruno Costa',
        'email' => 'bruno.outro@ufam.edu.br',
        'phone' => '92999990000',
        'job_title' => 'OUTRO',
        'employment_link' => 'OUTRO',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertRedirectToRoute('login');

    $this->assertDatabaseHas('users', [
        'functional_id' => '20260010',
        'course' => 'OUTRO',
    ]);
    $this->assertDatabaseHas('users', [
        'functional_id' => '20260011',
        'job_title' => 'OUTRO',
        'employment_link' => 'OUTRO',
    ]);
});

test('registration creates a pending account and sends a confirmation link', function () {
    Notification::fake();
    Storage::fake();
    UserProfileOption::query()->create(['type' => 'course', 'value' => 'OUTRO']);

    $response = $this->post(route('register.store'), [
        'profile' => 'aluno',
        'functional_id' => '20260001',
        'name' => 'Ana Silva',
        'email' => 'ana@ufam.edu.br',
        'phone' => '92999990000',
        'course' => 'OUTRO',
        'enrollment_proof' => UploadedFile::fake()->create('matricula.pdf', 80, 'application/pdf'),
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $user = User::query()->where('email', 'ana@ufam.edu.br')->firstOrFail();
    $response->assertRedirectToRoute('login');
    $this->assertGuest();
    $this->assertSame('pending_email', $user->registration_status);
    $this->assertNotNull($user->enrollment_proof_path);
    Notification::assertSentTo($user, VerifyUserEmail::class);
});

test('registration rejects unauthorized domains and weak passwords', function () {
    UserProfileOption::query()->create(['type' => 'course', 'value' => 'OUTRO']);
    $payload = [
        'profile' => 'aluno',
        'functional_id' => '20260002',
        'name' => 'Bruno Costa',
        'email' => 'bruno@example.com',
        'phone' => '92999990000',
        'course' => 'OUTRO',
        'enrollment_proof' => UploadedFile::fake()->create('matricula.pdf', 80, 'application/pdf'),
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ];
    $response = $this->from(route('register'))->post(route('register.store'), $payload);
    $response->assertSessionHasErrors('email');

    $payload['email'] = 'bruno@ufam.edu.br';
    $payload['password'] = 'password';
    $payload['password_confirmation'] = 'password';
    $this->from(route('register'))->post(route('register.store'), $payload)->assertSessionHasErrors('password');
    $this->assertDatabaseCount('users', 0);
});

test('password reset links can be used once and apply the password policy', function () {
    $user = User::factory()->create(['email' => 'reset@ufam.edu.br']);
    $token = Password::broker()->createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertOk();
    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'updated123',
        'password_confirmation' => 'updated123',
    ])->assertRedirectToRoute('login');

    expect(Hash::check('updated123', $user->fresh()->password))->toBeTrue();
    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'another123',
        'password_confirmation' => 'another123',
    ])->assertSessionHasErrors('email');
});

test('authenticated users can change their own password with the current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('password.change.update'), [
        'current_password' => 'password',
        'password' => 'changed123',
        'password_confirmation' => 'changed123',
    ])->assertRedirectToRoute('profile.show');

    expect(Hash::check('changed123', $user->fresh()->password))->toBeTrue();
});

test('administrator accounts have all permissions independently of user profiles', function () {
    $admin = User::factory()->create(['account_type' => 'administrator', 'profile' => null]);

    expect($admin->isAdministrator())->toBeTrue();
    expect($admin->hasPermissionTo('usuarios.aprovar'))->toBeTrue();
    expect($admin->hasPermissionTo('grupos_permissoes.excluir'))->toBeTrue();
});

test('only confirmed active users can log in and rejected users may still authenticate', function () {
    $pending = User::factory()->create([
        'email' => 'pending@ufam.edu.br',
        'registration_status' => 'pending_email',
        'email_verified_at' => null,
    ]);

    $this->post(route('login.store'), ['email' => $pending->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();

    $rejected = User::factory()->create([
        'email' => 'rejected@ufam.edu.br',
        'registration_status' => 'rejected',
    ]);
    $this->post(route('login.store'), ['email' => $rejected->email, 'password' => 'password'])
        ->assertRedirectToRoute('home');
    $this->assertAuthenticatedAs($rejected);
});

test('email confirmation is signed, expires and advances the registration state', function () {
    $user = User::factory()->create([
        'email' => 'confirm@ufam.edu.br',
        'registration_status' => 'pending_email',
        'email_verified_at' => null,
        'verification_sent_at' => now(),
    ]);
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
        'id' => $user->id,
        'hash' => sha1($user->email),
        'sent_at' => $user->verification_sent_at->timestamp,
    ]);

    $this->get($url)->assertRedirectToRoute('login');
    $this->assertDatabaseHas('users', ['id' => $user->id, 'registration_status' => 'email_confirmed']);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('an expired email confirmation link offers a new confirmation request', function () {
    $user = User::factory()->create([
        'email' => 'expired@ufam.edu.br',
        'registration_status' => 'pending_email',
        'email_verified_at' => null,
        'verification_sent_at' => now()->subDay(),
    ]);
    $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), [
        'id' => $user->id,
        'hash' => sha1($user->email),
        'sent_at' => $user->verification_sent_at->timestamp,
    ]);

    $this->get($url)
        ->assertRedirectToRoute('verification.notice')
        ->assertSessionHasErrors('email');
});

test('an individual denial overrides a group and can be removed', function () {
    $permission = Permission::query()->where('key', 'usuarios.listar')->firstOrFail();
    $user = User::factory()->create();
    $group = PermissionGroup::query()->create(['name' => 'Consulta', 'description' => '']);
    $group->permissions()->attach($permission);
    $group->users()->attach($user);

    expect($user->hasPermissionTo('usuarios.listar'))->toBeTrue();
    $user->permissionAdjustments()->create(['permission_id' => $permission->id, 'effect' => 'deny']);
    expect($user->hasPermissionTo('usuarios.listar'))->toBeFalse();
    $user->permissionAdjustments()->delete();
    expect($user->hasPermissionTo('usuarios.listar'))->toBeTrue();
});

test('groups can be created and assigned to multiple users in one operation', function () {
        $operator = User::factory()->create();
        grantPermission($operator, 'grupos_permissoes.criar');
        grantPermission($operator, 'grupos_permissoes.atribuir_usuarios');
        $permission = Permission::query()->where('key', 'usuarios.listar')->firstOrFail();

        $this->actingAs($operator)->post(route('groups.store'), [
            'name' => 'Consulta de usuários',
            'description' => 'Acesso somente de consulta.',
            'permission_ids' => [$permission->id],
        ])->assertRedirect();

        $group = PermissionGroup::query()->where('name', 'Consulta de usuários')->firstOrFail();
        $users = User::factory()->count(2)->create();
        $this->put(route('groups.members.sync', $group), ['user_ids' => $users->modelKeys()])
            ->assertRedirect();

        foreach ($users as $user) {
            expect($user->fresh()->hasPermissionTo('usuarios.listar'))->toBeTrue();
        }
    });

    test('profile changes archive fields that no longer apply', function () {
        $operator = User::factory()->create();
        grantPermission($operator, 'usuarios.alterar_perfil');
        $user = User::factory()->create([
            'profile' => 'aluno',
            'course' => 'CURSO ANTERIOR',
        ]);
        UserProfileOption::query()->create(['type' => 'job_title', 'value' => 'Analista', 'active' => true]);
        UserProfileOption::query()->create(['type' => 'employment_link', 'value' => 'Servidor', 'active' => true]);

        $this->actingAs($operator)->put(route('users.profile.update', $user), [
            'profile' => 'tecnico',
            'job_title' => 'Analista',
            'employment_link' => 'Servidor',
        ])->assertRedirect();

        expect($user->fresh()->profile)->toBe('tecnico');
        $this->assertDatabaseHas('user_profile_history', [
            'user_id' => $user->id,
            'old_profile' => 'aluno',
            'new_profile' => 'tecnico',
        ]);
        expect($user->profileHistory()->firstOrFail()->archived_fields)->toHaveKey('course');
    });

test('approval requires a group and records the selected association', function () {
    $admin = User::factory()->create();
    grantPermission($admin, 'usuarios.aprovar');
    $user = User::factory()->create([
        'registration_status' => 'email_confirmed',
        'email' => 'new-user@ufam.edu.br',
    ]);
    $group = PermissionGroup::query()->create(['name' => 'Solicitantes']);

    $this->actingAs($admin)->post(route('users.approve', $user), [
        'group_ids' => [$group->id],
        'documentation_notes' => 'Documentação conferida.',
    ])->assertRedirect();

    expect($user->fresh()->registration_status)->toBe('approved');
    $this->assertDatabaseHas('group_user', ['permission_group_id' => $group->id, 'user_id' => $user->id]);
});

test('unprivileged users cannot list users and accounts are deactivated instead of deleted', function () {
    $viewer = User::factory()->create();
    $target = User::factory()->create();
    $this->actingAs($viewer)->get(route('users.index'))->assertForbidden();

    grantPermission($viewer, 'usuarios.desativar');
    $this->post(route('users.deactivate', $target), ['reason' => 'Solicitação administrativa'])
        ->assertRedirect();

    expect($target->fresh()->is_active)->toBeFalse();
    $this->assertDatabaseHas('users', ['id' => $target->id]);
    $this->assertDatabaseHas('user_audit_logs', ['user_id' => $target->id, 'action' => 'user_deactivated']);
});