<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Models\User;
use App\Events\SessionLoggedOut;
use Illuminate\Support\Facades\Event;
use App\Listeners\BroadcastSessionLoggedOut;
use Illuminate\Auth\Events\OtherDeviceLogout;

it('broadcasts SessionLoggedOut when other device logout occurs', function () {
    Event::fake();

    $user = User::factory()->create();

    $event = new OtherDeviceLogout('web', $user);

    $listener = new BroadcastSessionLoggedOut;
    $listener->handle($event);

    $this->assertEventDispatched(SessionLoggedOut::class, function (SessionLoggedOut $e) use ($user) {
        return $e->user->id === $user->id;
    });
})->uses(TestCase::class);
