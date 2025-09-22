<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedPasswordChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        Log::info('QueuedPasswordChanged: preparing mail', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
        ]);

        return (new MailMessage)
            ->theme('nitrooctane')
            ->subject(Lang::get('Your password was changed for :app', ['app' => config('app.name', 'Application')]))
            ->markdown('emails.password-changed', [
                'user' => $notifiable,
                'app' => config('app.name', 'Application'),
            ]);
    }
}
