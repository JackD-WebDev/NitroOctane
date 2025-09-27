<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;

it('returns an error for unauthenticated user', function () {
    $response = $this->get('/api/user', [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);
});

it('returns user data for authenticated user', function () {
    $user = User::factory()->create([
        'name' => 'TestUser_1',
        'username' => 'testuser1',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
    ]);

    $response = $this->actingAs($user)->get('/api/user', [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'USER TESTUSER1 RETRIEVED SUCCESSFULLY.',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'type',
                'user_id',
                'has2FA',
                'attributes' => [
                    'username',
                    'email',
                    'created_at_dates' => [
                        'created_at_human',
                        'created_at',
                    ],
                    'updated_at_dates' => [
                        'updated_at_human',
                        'updated_at',
                    ],
                ],
            ],
            'links',
            'meta',
            'version',
        ]);
});

it('returns 404 for non-existent user', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/api/user/9999', [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'errors' => [
                'message' => 'MODEL NOT FOUND.',
            ],
        ]);
});

it('returns the user collection with the correct structure from the users endpoint', function () {
    $user = User::factory()->create();
    User::factory()->count(2)->create(); 

    $response = $this->actingAs($user)->get('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'data' => [
                        'type',
                        'user_id',
                        'attributes'
                    ],
                    'links',
                    'meta'
                ]
            ],
            'links',
            'meta',
            'version'
        ]);
    
    $data = $response->json();
    expect($data['success'])->toBe(true);
    expect(count($data['data']))->toBeGreaterThan(0);
});
