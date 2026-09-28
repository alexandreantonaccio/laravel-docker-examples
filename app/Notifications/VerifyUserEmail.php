<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyUserEmail extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 1440)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
                'sent_at' => $notifiable->verification_sent_at->timestamp,
            ],
        );

        return (new MailMessage)
            ->subject('Confirme seu e-mail')
            ->line('Confirme seu endereço de e-mail para concluir o cadastro.')
            ->action('Confirmar e-mail', $url)
            ->line('O link expira em 24 horas.');
    }
}