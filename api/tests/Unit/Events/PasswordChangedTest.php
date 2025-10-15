<?php

use Tests\TestCase;
use App\Models\User;
use App\Events\PasswordChanged;
use Illuminate\Broadcasting\PrivateChannel;

uses(TestCase::class);

it('implements ShouldBroadcast and returns the private user channel and name', function () {
    $user = User::factory()->create();

    $event = new PasswordChanged($user);

    $channel = $event->broadcastOn();
    expect($channel)->toBeInstanceOf(PrivateChannel::class);
    expect(str_contains($channel->name, 'user.'.$user->id))->toBeTrue();

    expect($event->broadcastAs())->toBe('password.changed');

    $reflection = new \ReflectionClass($event);
    expect($reflection->implementsInterface(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class))->toBeTrue();
});
