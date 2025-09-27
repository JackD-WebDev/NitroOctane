<?php

use App\Models\User;
use App\Notifications\QueuedResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

it('builds reset mail and includes frontend url when generating toMail', function () {
    Mail::fake();

    Config::set('app.frontend_url', 'http://test-frontend.local');

    $user = User::factory()->create();

    $token = 'test-token-123';

    $notification = new QueuedResetPassword($token, $user->email);

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('Password Reset');

    $viewData = $mailMessage->viewData ?? [];
    expect($viewData['token'])->toBe($token);
    expect($viewData['email'])->toBe($user->email);
    expect($viewData['frontend'])->toBe('http://test-frontend.local');
});

it('sends the queued reset password notification when notified', function () {
    Notification::fake();
    Mail::fake();

    Config::set('app.frontend_url', 'http://test-frontend.local');

    $user = User::factory()->create();

    $token = 'another-token-456';

    $user->notify(new QueuedResetPassword($token, $user->email));

    Notification::assertSentTo($user, QueuedResetPassword::class, function ($notification, $channels) use ($token, $user) {
        $mailMessage = $notification->toMail($user);
        $viewData = $mailMessage->viewData ?? [];

        expect($viewData['token'])->toBe($token);
        expect($viewData['email'])->toBe($user->email);
        expect($viewData['frontend'])->toBe('http://test-frontend.local');

        return true;
    });
});
