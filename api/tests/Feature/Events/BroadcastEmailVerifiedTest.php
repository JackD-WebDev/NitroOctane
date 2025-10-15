<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Models\User;
use App\Events\EmailVerified;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use App\Listeners\BroadcastEmailVerified;

it('broadcasts EmailVerified when a user is verified', function () {
    Event::fake();

    $user = User::factory()->create();

    $verifiedEvent = new Verified($user);

    $listener = new BroadcastEmailVerified;
    $listener->handle($verifiedEvent);

    $this->assertEventDispatched(EmailVerified::class, function (EmailVerified $event) use ($user) {
        return $event->user->id === $user->id;
    });
})->uses(TestCase::class);
