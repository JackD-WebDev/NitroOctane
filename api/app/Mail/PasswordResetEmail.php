<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;

    public string $email;

    /**
     * Create a new message instance.
     */
    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset for '.config('app.name', 'Application'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.password-reset',
            with: [
                'token' => $this->token,
                'email' => $this->email,
                'frontend' => rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost')), '/'),
                'appName' => config('app.name', 'Application'),
                'expires' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
