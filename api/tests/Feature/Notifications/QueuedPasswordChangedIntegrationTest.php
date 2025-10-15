<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Notifications\QueuedPasswordChanged;
use Illuminate\Support\Facades\Notification;

it('builds password changed mail with correct data', function () {
    Mail::fake();

    $user = User::factory()->create();

    $notification = new QueuedPasswordChanged;

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('Your password was changed');

    $viewData = $mailMessage->viewData ?? [];
    expect($viewData['user']->id)->toBe($user->id);
    expect($viewData['app'])->toBe(config('app.name', 'Application'));
});

it('sends the queued password changed notification when notified', function () {
    Notification::fake();
    Mail::fake();

    $user = User::factory()->create();

    $user->notify(new QueuedPasswordChanged);

    Notification::assertSentTo($user, QueuedPasswordChanged::class, function ($notification, $channels) use ($user) {
        $mailMessage = $notification->toMail($user);
        $viewData = $mailMessage->viewData ?? [];

        expect($viewData['user']->id)->toBe($user->id);
        expect($viewData['app'])->toBe(config('app.name', 'Application'));

        return true;
    });
});
