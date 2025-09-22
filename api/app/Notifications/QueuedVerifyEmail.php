<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * QueuedVerifyEmail
 *
 * This notification queues the email verification email and builds a verification URL
 * that points to the frontend (configured via config('app.frontend_url')).
 */
class QueuedVerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        Log::info('QueuedVerifyEmail: preparing mail', [
            'user_id' => $notifiable->getKey(),
            'email' => $notifiable->getEmailForVerification(),
        ]);

        return (new MailMessage)
            ->theme('nitrooctane')
            ->subject(Lang::get('Verify Your Email Address for :app', ['app' => config('app.name', 'Application')]))
            ->markdown('emails.verify-email', [
                'user' => $notifiable,
                'app' => config('app.name', 'Application'),
                'url' => $verificationUrl,
            ]);
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        $frontend = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');

        // Generate the signed URL using Laravel's URL facade
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Extract query parameters from the signed Laravel URL
        $parsedUrl = parse_url($signedUrl);
        $queryString = $parsedUrl['query'] ?? '';

        // Ensure id and hash are included in the frontend query parameters
        // (the signed route places id/hash in the path, and the signature/expires
        // become query params — frontend needs all four to call the BFF verify)
        $id = $notifiable->getKey();
        $hash = sha1($notifiable->getEmailForVerification());

        // If there are existing signed query params (expires, signature), append them
        $qsPrefix = $queryString ? $queryString.'&' : '';

        return $frontend.'/verify-email?'.$qsPrefix.http_build_query([
            'id' => $id,
            'hash' => $hash,
        ]);
    }
}
