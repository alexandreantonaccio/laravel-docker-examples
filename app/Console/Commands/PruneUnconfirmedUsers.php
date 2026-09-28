<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneUnconfirmedUsers extends Command
{
    protected $signature = 'users:prune-unconfirmed';

    protected $description = 'Remove cadastros que não confirmaram o e-mail em 24 horas.';

    public function handle(): int
    {
        $expiredUsers = User::query()
            ->where('registration_status', 'pending_email')
            ->where('created_at', '<', now()->subHours(config('users.pending_registration_expiration_hours')))
            ->get();

        foreach ($expiredUsers as $user) {
            if ($user->enrollment_proof_path) {
                Storage::delete($user->enrollment_proof_path);
            }

            $user->delete();
        }

        $this->info($expiredUsers->count().' cadastro(s) expirado(s) removido(s).');

        return self::SUCCESS;
    }
}