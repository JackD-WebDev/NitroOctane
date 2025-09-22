<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * QueuedResetPassword
 *
 * This notification queues the password reset email and builds a reset URL
 * that points to the frontend (configured via config('app.frontend_url')).
 */
class QueuedResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var string */
    public $token;

    /** @var string|null */
    public $email;

    /**
     * Create a new notification instance.
     * $email is optional for backward compatibility but recommended so
     * the queued job does not need to resolve the notifiable model.
     */
    public function __construct(string $token, ?string $email = null)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $email = $this->email ?? $notifiable->getEmailForPasswordReset();

        $frontend = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');
        $resetUrl = $frontend.'/reset-password?token='.urlencode($this->token).'&email='.urlencode($email);

        try {
            $notifiableId = is_object($notifiable) && method_exists($notifiable, 'getKey') ? $notifiable->getKey() : null;
        } catch (\Throwable $e) {
            $notifiableId = null;
        }

        Log::info('QueuedResetPassword: preparing mail', [
            'to' => $email,
            'token_snippet' => substr($this->token, 0, 8),
            'notifiable_id' => $notifiableId,
        ]);

        return (new MailMessage)
            ->theme('nitrooctane')
            ->subject(Lang::get('Password Reset for :app', ['app' => config('app.name', 'Application')]))
            ->markdown('emails.password-reset', [
                'token' => $this->token,
                'email' => $email,
                'frontend' => $frontend,
                'appName' => config('app.name', 'Application'),
                'expires' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]);
    }

    protected function resetUrl($notifiable): string
    {
        $frontend = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');
        $email = urlencode($this->email ?? $notifiable->getEmailForPasswordReset());
        $token = urlencode($this->token);

        return $frontend.'/reset-password?token='.$token.'&email='.$email;
    }
}
