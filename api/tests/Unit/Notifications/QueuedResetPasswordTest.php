<?php

use App\Notifications\QueuedResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

uses(TestCase::class);

it('builds a mail message with frontend reset url', function () {
    $token = 'secrettoken123';
    $email = 'user@example.com';

    $notification = new QueuedResetPassword($token, $email);

    $notifiable = new class {
        public function getEmailForPasswordReset()
        {
            return 'fallback@example.com';
        }
        public function getKey() { return 123; }
    };

    $mail = $notification->toMail($notifiable);

    expect($mail)->toBeInstanceOf(MailMessage::class);
    expect($mail->markdown)->toBe('emails.password-reset');
    expect($mail->viewData['token'])->toBe($token);
    expect($mail->viewData['email'])->toBe($email);

    expect(array_key_exists('token', $mail->viewData))->toBeTrue();
    expect(array_key_exists('email', $mail->viewData))->toBeTrue();
});

it('falls back to notifiable email when none provided', function () {
    $token = 'fallbacktoken';

    $notification = new QueuedResetPassword($token, null);

    $notifiable = new class {
        public function getEmailForPasswordReset()
        {
            return 'fallback@example.com';
        }
        public function getKey() { return 456; }
    };

    $mail = $notification->toMail($notifiable);

    expect($mail)->toBeInstanceOf(MailMessage::class);
    expect($mail->viewData['email'])->toBe('fallback@example.com');
});

it('handles notifiable getKey throwing and still logs', function () {
    $token = 'tkn-x';

    $notification = new QueuedResetPassword($token, 'provided@example.com');

    $notifiable = new class {
        public function getEmailForPasswordReset()
        {
            return 'ok@example.com';
        }
        public function getKey()
        {
            throw new \Exception('boom');
        }
    };

    \Illuminate\Support\Facades\Log::shouldReceive('info')->once();

    $mail = $notification->toMail($notifiable);

    expect($mail)->toBeInstanceOf(MailMessage::class);
});

it('via returns mail channel', function () {
    $notification = new QueuedResetPassword('x', null);
    expect($notification->via(new stdClass()))->toBe(['mail']);
});
