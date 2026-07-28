<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the branded password reset email.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);
        $broker = config('auth.defaults.passwords');

        return (new MailMessage)
            ->subject('Reset your TradeYatra password')
            ->view('emails.auth.reset-password', [
                'userName' => $notifiable->name ?: 'Trader',
                'resetUrl' => $resetUrl,
                'expireMinutes' => config("auth.passwords.{$broker}.expire", 60),
            ]);
    }
}
