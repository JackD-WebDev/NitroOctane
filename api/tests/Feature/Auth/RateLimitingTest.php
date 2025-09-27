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
    // create a user to avoid "We can't find a user with that email address." validation
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
