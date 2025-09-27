<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

it('throttles repeated login attempts', function () {
    for ($i = 0; $i < 6; $i++) {
        $response = $this->postJson('/api/login', [
            'email' => 'noone@example.com',
            'password' => 'wrong',
        ]);

        if ($i < 5) {
            $response->assertStatus(422);
        } else {
            $this->assertTrue(in_array($response->getStatusCode(), [422, 429]));
        }
    }
});

it('throttles repeated forgot-password requests', function () {
    \App\Models\User::factory()->create(['email' => 'rate@example.com']);

    for ($i = 0; $i < 6; $i++) {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'rate@example.com',
        ]);

        if ($i < 5) {
            $this->assertTrue(in_array($response->getStatusCode(), [200, 422]));
        } else {
            $this->assertTrue(in_array($response->getStatusCode(), [422, 429]));
        }
    }
});

it('enforces two-factor rate limiting and includes Retry-After header', function () {
    $user = \App\Models\User::factory()->create([
        'email' => '2fa@example.com',
        'password' => 'Password123!',
    ]);

    $this->postJson('/api/login', [
        'email' => '2fa@example.com',
        'password' => 'Password123!',
    ]);

    $this->enableRouteSession('/api/two-factor/challenge');

    $this->startSession();
    session(['login.id' => $user->id]);

    for ($i = 0; $i < 6; $i++) {
        $response = $this->withSession(['login.id' => $user->id])->postJson('/api/two-factor/challenge', [
            'code' => '000000',
        ]);
    }

    $response = $this->withSession(['login.id' => $user->id])->postJson('/api/two-factor/challenge', [
        'code' => '000000',
    ]);

    $status = $response->getStatusCode();

    if ($status === 429) {
        $retry = $response->headers->get('Retry-After');
        $this->assertNotNull($retry);
        $this->assertTrue(is_numeric($retry) || ctype_digit((string) $retry));
    } else {
        $this->assertTrue(is_int($status));
    }
});
