<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Đặt lại mật khẩu – DNT Store')
            // dùng view HTML custom
            ->view('emails.auth.reset-password', [
                'url' => $url,
                'expireMinutes' => $expireMinutes,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
    }
}
