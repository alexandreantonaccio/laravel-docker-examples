<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PermissionGroupController;
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
