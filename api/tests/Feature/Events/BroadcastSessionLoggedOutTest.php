<?php

namespace Tests\Feature\Events;

use App\Events\SessionLoggedOut;
use Illuminate\Auth\Events\OtherDeviceLogout;
use App\Listeners\BroadcastSessionLoggedOut;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;

it('broadcasts SessionLoggedOut when other device logout occurs', function () {
    Event::fake();

    $user = User::factory()->create();

    $event = new OtherDeviceLogout('web', $user);

    $listener = new BroadcastSessionLoggedOut();
    $listener->handle($event);

    $this->assertEventDispatched(SessionLoggedOut::class, function (SessionLoggedOut $e) use ($user) {
        return $e->user->id === $user->id;
    });
})->uses(TestCase::class);
