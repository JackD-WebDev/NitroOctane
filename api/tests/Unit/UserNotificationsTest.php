<?php

use Tests\TestCase;
use App\Models\User;
use App\Notifications\QueuedVerifyEmail;
use App\Notifications\QueuedResetPassword;
use Illuminate\Support\Facades\Notification;

uses(TestCase::class);

it('sends queued reset password notification with correct email', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'notify@example.com']);

    $user->sendPasswordResetNotification('token123');
    Notification::assertSentTo(
        [$user],
        QueuedResetPassword::class,
        function ($notification, $channels) use ($user) {
            return isset($notification->email) && $notification->email === $user->email
                && isset($notification->token) && $notification->token === 'token123';
        }
    );
});

it('diagnostic: direct notify records queued reset password', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'direct@example.com']);

    $user->notify(new QueuedResetPassword('tokendirect', $user->email));

    Notification::assertSentTo([$user], QueuedResetPassword::class);
});

it('sends queued verify email notification', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'verify@example.com']);

    $user->sendEmailVerificationNotification();

    Notification::assertSentTo(
        [$user],
        QueuedVerifyEmail::class
    );
});
