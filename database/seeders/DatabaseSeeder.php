<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        if ($administratorEmail = config('users.administrator_email')) {
            User::query()->updateOrCreate(
                ['email' => $administratorEmail],
                [
                    'name' => 'Administrador',
                    'password' => Hash::make(config('users.administrator_password')),
                    'account_type' => config('users.administrator_account_type'),
                    'registration_status' => 'approved',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }

        foreach (config('users.allowed_email_domains', []) as $domain) {
            \App\Models\EmailDomain::query()->firstOrCreate(['domain' => $domain], ['active' => true]);
        }

        foreach ([
            ['type' => 'course', 'value' => 'OUTRO'],
            ['type' => 'job_title', 'value' => 'OUTRO'],
            ['type' => 'job_title', 'value' => config('users.professor_job_title')],
            ['type' => 'employment_link', 'value' => 'OUTRO'],
        ] as $option) {
            \App\Models\UserProfileOption::query()->firstOrCreate($option, ['active' => true]);
        }
    }
}
