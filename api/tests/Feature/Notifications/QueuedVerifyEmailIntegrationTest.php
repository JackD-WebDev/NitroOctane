<?php

use App\Models\User;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

it('builds verify email mail and includes frontend signed params', function () {
    Mail::fake();

    Config::set('app.frontend_url', 'http://test-frontend.local');

    $user = User::factory()->create();

    $notification = new QueuedVerifyEmail();

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('Verify Your Email Address');

    $viewData = $mailMessage->viewData ?? [];
    $url = $viewData['url'] ?? null;

    expect($url)->toContain('/verify-email');
    expect($url)->toContain('id='.$user->getKey());
    expect($url)->toContain('hash='.sha1($user->getEmailForVerification()));
});

it('sends the queued verify email notification when notified', function () {
    Notification::fake();
    Mail::fake();

    Config::set('app.frontend_url', 'http://test-frontend.local');

    $user = User::factory()->create();

    $user->notify(new QueuedVerifyEmail());

    Notification::assertSentTo($user, QueuedVerifyEmail::class, function ($notification, $channels) use ($user) {
        $mailMessage = $notification->toMail($user);
        $viewData = $mailMessage->viewData ?? [];
        $url = $viewData['url'] ?? null;

        expect($url)->toContain('/verify-email');
        expect($url)->toContain('id='.$user->getKey());
        expect($url)->toContain('hash='.sha1($user->getEmailForVerification()));

        return true;
    });
});
