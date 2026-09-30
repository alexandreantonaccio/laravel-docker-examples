<?php

return [
    'administrator_email' => env('USERS_ADMINISTRATOR_EMAIL'),
    'administrator_password' => env('USERS_ADMINISTRATOR_PASSWORD', 'password'),
    'administrator_account_type' => 'administrator',
    'default_account_type' => 'user',
    'verification_expiration_hours' => 24,
    'pending_registration_expiration_hours' => 24,
    'login_attempts' => 5,
    'login_decay_minutes' => 1,
    'allowed_email_domains' => array_filter(array_map(
        static fn (string $domain): string => strtolower(ltrim(trim($domain), '@')),
        explode(',', env('USERS_ALLOWED_EMAIL_DOMAINS', 'ufam.edu.br')),
    )),
    'profiles' => ['aluno', 'professor', 'tecnico'],
    'professor_job_title' => 'Professor do Magistério Superior',
];