<?php

namespace Tests\Feature\Auth;

use App\Models\User;

it('logs out an authenticated user and invalidates session', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'message']);

    $this->assertGuest();
});

it('returns 401 when logging out unauthenticated', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401);
});
