<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PermissionGroupController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\SupportCatalogController;
use App\Http\Controllers\UserAdministrationController;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? view('dashboard')
        : view('auth.login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/email/verify', [AuthController::class, 'verificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('throttle:6,1')->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:3,60')->name('verification.send');
    Route::get('/forgot-password', [PasswordController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])
        ->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/users/{user}/enrollment-proof', function (User $user) {
            abort_unless($user->enrollment_proof_path, 404);

            return Storage::download($user->enrollment_proof_path);
        })->middleware('permission:usuarios.aprovar')->name('users.enrollment-proof');
    Route::get('/me', [UserController::class, 'profile'])->name('profile.show');
    Route::get('/me/password', [PasswordController::class, 'changeForm'])->name('password.change');
    Route::put('/me/password', [PasswordController::class, 'change'])->name('password.change.update');

    Route::get('/users/pending', [UserAdministrationController::class, 'pending'])
        ->middleware('permission:usuarios.aprovar')->name('users.pending');
    Route::post('/users/{user}/approve', [UserAdministrationController::class, 'approve'])
        ->middleware('permission:usuarios.aprovar')->name('users.approve');
    Route::post('/users/{user}/reject', [UserAdministrationController::class, 'reject'])
        ->middleware('permission:usuarios.aprovar')->name('users.reject');
    Route::get('/users/{user}/permissions', [UserAdministrationController::class, 'permissions'])
        ->middleware('permission:usuarios.gerenciar_permissoes')->name('users.permissions');
    Route::put('/users/{user}/permissions', [UserAdministrationController::class, 'updatePermissions'])
        ->middleware('permission:usuarios.gerenciar_permissoes')->name('users.permissions.update');
    Route::get('/users/{user}/profile', [UserAdministrationController::class, 'editProfile'])
        ->middleware('permission:usuarios.alterar_perfil')->name('users.profile.edit');
    Route::put('/users/{user}/profile', [UserAdministrationController::class, 'updateProfile'])
        ->middleware('permission:usuarios.alterar_perfil')->name('users.profile.update');
    Route::post('/users/{user}/deactivate', [UserAdministrationController::class, 'deactivate'])
        ->middleware('permission:usuarios.desativar')->name('users.deactivate');
    Route::post('/users/{user}/activate', [UserAdministrationController::class, 'activate'])
        ->middleware('permission:usuarios.desativar')->name('users.activate');
    Route::post('/users/{user}/reset-password', [PasswordController::class, 'sendAdminReset'])
        ->middleware('permission:usuarios.resetar_senha')->name('users.password.reset');

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:usuarios.listar')->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:usuarios.editar.qualquer')->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:usuarios.editar.qualquer')->name('users.update');

    Route::get('/groups', [PermissionGroupController::class, 'index'])
        ->middleware('permission:grupos_permissoes.criar|grupos_permissoes.editar|grupos_permissoes.excluir|grupos_permissoes.atribuir_usuarios')->name('groups.index');
    Route::get('/groups/create', [PermissionGroupController::class, 'create'])
        ->middleware('permission:grupos_permissoes.criar')->name('groups.create');
    Route::post('/groups', [PermissionGroupController::class, 'store'])
        ->middleware('permission:grupos_permissoes.criar')->name('groups.store');
    Route::get('/groups/{group}/edit', [PermissionGroupController::class, 'edit'])
        ->middleware('permission:grupos_permissoes.editar')->name('groups.edit');
    Route::put('/groups/{group}', [PermissionGroupController::class, 'update'])
        ->middleware('permission:grupos_permissoes.editar')->name('groups.update');
    Route::delete('/groups/{group}', [PermissionGroupController::class, 'destroy'])
        ->middleware('permission:grupos_permissoes.excluir')->name('groups.destroy');
    Route::get('/groups/{group}', [PermissionGroupController::class, 'show'])->name('groups.show');
        Route::get('/groups/{group}', [PermissionGroupController::class, 'show'])
            ->middleware('permission:grupos_permissoes.criar|grupos_permissoes.editar|grupos_permissoes.excluir|grupos_permissoes.atribuir_usuarios')
            ->name('groups.show');
    Route::put('/groups/{group}/members', [PermissionGroupController::class, 'syncUsers'])
        ->middleware('permission:grupos_permissoes.atribuir_usuarios')->name('groups.members.sync');

    Route::get('/catalogs/{catalog}', [SupportCatalogController::class, 'index'])
        ->middleware('permission:cursos.listar|cargos.listar|vinculos.listar')->name('catalogs.index');
    Route::get('/catalogs/{catalog}/create', [SupportCatalogController::class, 'create'])
        ->middleware('permission:cursos.criar|cargos.criar|vinculos.criar')->name('catalogs.create');
    Route::post('/catalogs/{catalog}', [SupportCatalogController::class, 'store'])
        ->middleware('permission:cursos.criar|cargos.criar|vinculos.criar')->name('catalogs.store');
    Route::get('/catalogs/{catalog}/{item}/edit', [SupportCatalogController::class, 'edit'])
        ->middleware('permission:cursos.editar|cargos.editar|vinculos.editar')->name('catalogs.edit');
    Route::put('/catalogs/{catalog}/{item}', [SupportCatalogController::class, 'update'])
        ->middleware('permission:cursos.editar|cargos.editar|vinculos.editar')->name('catalogs.update');
    Route::patch('/catalogs/{catalog}/{item}/toggle', [SupportCatalogController::class, 'toggle'])
        ->middleware('permission:cursos.desativar|cargos.desativar|vinculos.desativar')->name('catalogs.toggle');

    Route::get('/email-domains', [SupportCatalogController::class, 'domains'])
        ->middleware('permission:dominios_email.listar')->name('domains.index');
    Route::get('/email-domains/create', [SupportCatalogController::class, 'createDomain'])
        ->middleware('permission:dominios_email.criar')->name('domains.create');
    Route::post('/email-domains', [SupportCatalogController::class, 'storeDomain'])
        ->middleware('permission:dominios_email.criar')->name('domains.store');
    Route::get('/email-domains/{domain}/edit', [SupportCatalogController::class, 'editDomain'])
        ->middleware('permission:dominios_email.editar')->name('domains.edit');
    Route::put('/email-domains/{domain}', [SupportCatalogController::class, 'updateDomain'])
        ->middleware('permission:dominios_email.editar')->name('domains.update');
    Route::patch('/email-domains/{domain}/toggle', [SupportCatalogController::class, 'toggleDomain'])
        ->middleware('permission:dominios_email.desativar')->name('domains.toggle');
    Route::patch('/email-domains/{domain}/default', [SupportCatalogController::class, 'setDefaultDomain'])
        ->middleware('permission:dominios_email.editar')->name('domains.default');

    Route::get('/environments', [EnvironmentController::class, 'index'])
        ->middleware('permission:ambientes.listar')->name('environments.index');
    Route::get('/environments/create', [EnvironmentController::class, 'create'])
        ->middleware('permission:ambientes.criar')->name('environments.create');
    Route::post('/environments', [EnvironmentController::class, 'store'])
        ->middleware('permission:ambientes.criar')->name('environments.store');
    Route::get('/environments/{environment}/edit', [EnvironmentController::class, 'edit'])
        ->middleware('permission:ambientes.editar')->name('environments.edit');
    Route::put('/environments/{environment}', [EnvironmentController::class, 'update'])
        ->middleware('permission:ambientes.editar')->name('environments.update');
    Route::patch('/environments/{environment}/toggle', [EnvironmentController::class, 'toggle'])
        ->middleware('permission:ambientes.desativar')->name('environments.toggle');

    Route::get('/environment-groups', [EnvironmentController::class, 'groups'])
        ->middleware('permission:grupos_ambientes.listar')->name('environment-groups.index');
    Route::get('/environment-groups/create', [EnvironmentController::class, 'createGroup'])
        ->middleware('permission:grupos_ambientes.criar')->name('environment-groups.create');
    Route::post('/environment-groups', [EnvironmentController::class, 'storeGroup'])
        ->middleware('permission:grupos_ambientes.criar')->name('environment-groups.store');
    Route::get('/environment-groups/{environmentGroup}/edit', [EnvironmentController::class, 'editGroup'])
        ->middleware('permission:grupos_ambientes.editar')->name('environment-groups.edit');
    Route::put('/environment-groups/{environmentGroup}', [EnvironmentController::class, 'updateGroup'])
        ->middleware('permission:grupos_ambientes.editar')->name('environment-groups.update');
    Route::patch('/environment-groups/{environmentGroup}/toggle', [EnvironmentController::class, 'toggleGroup'])
        ->middleware('permission:grupos_ambientes.desativar')->name('environment-groups.toggle');

    Route::get('/agenda', [BookingController::class, 'calendar'])->name('bookings.calendar');
    Route::get('/agenda/solicitacoes', [BookingController::class, 'adminIndex'])
        ->middleware('permission:agendamentos.listar.qualquer')->name('bookings.admin');
    Route::get('/agenda/solicitar', [BookingController::class, 'create'])
        ->name('bookings.create');
    Route::post('/agenda/solicitar', [BookingController::class, 'store'])
        ->name('bookings.store');
    Route::get('/agenda/fixas/criar', [BookingController::class, 'fixedForm'])
        ->middleware('permission:agendamentos.fixo.criar')->name('bookings.fixed.create');
    Route::post('/agenda/fixas/preview', [BookingController::class, 'previewSeries'])
        ->middleware('permission:agendamentos.fixo.criar')->name('bookings.fixed.preview');
    Route::post('/agenda/fixas', [BookingController::class, 'storeSeries'])
        ->middleware('permission:agendamentos.fixo.criar')->name('bookings.fixed.store');
    Route::get('/agenda/configuracoes', [BookingController::class, 'rules'])
        ->middleware('permission:agendamentos.configurar')->name('bookings.rules');
    Route::get('/agenda/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/agenda/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/agenda/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::post('/agenda/{booking}/approve', [BookingController::class, 'approve'])
        ->middleware('permission:agendamentos.aprovar.qualquer')->name('bookings.approve');
    Route::post('/agenda/{booking}/reject', [BookingController::class, 'reject'])
        ->middleware('permission:agendamentos.aprovar.qualquer')->name('bookings.reject');
    Route::post('/agenda/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/booking-types', [BookingController::class, 'types'])
        ->middleware('permission:agendamentos.tipo.criar|agendamentos.tipo.editar|agendamentos.tipo.desativar')->name('booking-types.index');
    Route::post('/booking-types', [BookingController::class, 'storeType'])
        ->middleware('permission:agendamentos.tipo.criar')->name('booking-types.store');
    Route::put('/booking-types/{type}', [BookingController::class, 'updateType'])
        ->middleware('permission:agendamentos.tipo.editar')->name('booking-types.update');
    Route::patch('/booking-types/{type}/toggle', [BookingController::class, 'toggleType'])
        ->middleware('permission:agendamentos.tipo.desativar')->name('booking-types.toggle');
    Route::get('/teachers', [BookingController::class, 'teachers'])
        ->middleware('permission:agendamentos.docente.criar|agendamentos.docente.editar|agendamentos.docente.desativar')->name('teachers.index');
    Route::post('/teachers', [BookingController::class, 'storeTeacher'])
        ->middleware('permission:agendamentos.docente.criar')->name('teachers.store');
    Route::put('/teachers/{teacher}', [BookingController::class, 'updateTeacher'])
        ->middleware('permission:agendamentos.docente.editar')->name('teachers.update');
    Route::patch('/teachers/{teacher}/toggle', [BookingController::class, 'toggleTeacher'])
        ->middleware('permission:agendamentos.docente.desativar')->name('teachers.toggle');
    Route::post('/agenda/configuracoes/regras', [BookingController::class, 'saveRules'])
        ->middleware('permission:agendamentos.configurar')->name('bookings.rules.save');
    Route::delete('/agenda/configuracoes/regras/{rule}', [BookingController::class, 'removeEnvironmentRule'])
        ->middleware('permission:agendamentos.configurar')->name('bookings.rules.remove');
    Route::post('/agenda/configuracoes/bloqueios', [BookingController::class, 'storeBlock'])
        ->middleware('permission:agendamentos.configurar')->name('bookings.blocks.store');
    Route::get('/agenda/configuracoes/bloqueios/{block}/edit', [BookingController::class, 'editBlock'])
        ->middleware('permission:agendamentos.configurar')->name('bookings.blocks.edit');
    Route::put('/agenda/configuracoes/bloqueios/{block}', [BookingController::class, 'updateBlock'])
        ->middleware('permission:agendamentos.configurar')->name('bookings.blocks.update');
    Route::delete('/agenda/configuracoes/bloqueios/{block}', [BookingController::class, 'deleteBlock'])
        ->middleware('permission:agendamentos.configurar')->name('bookings.blocks.delete');
});

Route::get('/info', function () {
    Log::info('Phpinfo page visited');
    return phpinfo();
});

Route::get('/health', function () {
    $status = [];

    // Check Database Connection
    try {
        DB::connection()->getPdo();
        // Optionally, run a simple query
        DB::select('SELECT 1');
        $status['database'] = 'OK';
    } catch (\Exception $e) {
        $status['database'] = 'Error';
    }

    // Check Redis Connection
    try {
        Cache::store('redis')->put('health_check', 'OK', 10);
        $value = Cache::store('redis')->get('health_check');
        if ($value === 'OK') {
            $status['redis'] = 'OK';
        } else {
            $status['redis'] = 'Error';
        }
    } catch (\Exception $e) {
        $status['redis'] = 'Error';
    }

    // Check Storage Access
    try {
        $testFile = 'health_check.txt';
        Storage::put($testFile, 'OK');
        $content = Storage::get($testFile);
        Storage::delete($testFile);

        if ($content === 'OK') {
            $status['storage'] = 'OK';
        } else {
            $status['storage'] = 'Error';
        }
    } catch (\Exception $e) {
        $status['storage'] = 'Error';
    }

    // Determine overall health status
    $isHealthy = collect($status)->every(function ($value) {
        return $value === 'OK';
    });

    $httpStatus = $isHealthy ? 200 : 503;

    return response()->json($status, $httpStatus);
});
