<?php

use Tests\TestCase;
use App\Models\User;

uses(TestCase::class);

it('returns email for mail route when email is set', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    expect($user->routeNotificationForMail())->toBe('test@example.com');
});

it('handles object driver gracefully and still returns email', function () {
    $user = User::factory()->create(['email' => 'object@example.com']);

    $dummy = new class {};

    expect($user->routeNotificationForMail($dummy))->toBe('object@example.com');
});
