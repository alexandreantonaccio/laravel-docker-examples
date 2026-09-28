<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserAuditLog;
use Illuminate\Console\Command;

class GrantUserPermission extends Command
{
    protected $signature = 'access:grant {email} {permission}';

    protected $description = 'Concede individualmente uma permissão a um usuário existente.';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();
        $permission = Permission::query()->where('key', $this->argument('permission'))->first();

        if (! $user || ! $permission) {
            $this->error('Usuário ou permissão não encontrados. Execute primeiro php artisan db:seed.');

            return self::FAILURE;
        }

        if ($user->groups()->whereHas('permissions', fn ($query) => $query->whereKey($permission->id))->exists()) {
            $this->warn('A permissão já é herdada de um grupo.');

            return self::SUCCESS;
        }

        $user->permissionAdjustments()->updateOrCreate(
            ['permission_id' => $permission->id],
            ['effect' => 'grant', 'updated_by' => null],
        );
        UserAuditLog::query()->create([
            'user_id' => $user->id,
            'action' => 'permission_granted_from_cli',
            'details' => ['permission' => $permission->key],
        ]);

        $this->info("Permissão {$permission->key} concedida a {$user->email}.");

        return self::SUCCESS;
    }
}