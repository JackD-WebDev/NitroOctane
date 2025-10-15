<?php

use App\Models\User;

beforeEach(function () {});

it('returns all users with the correct structure for the index endpoint', function () {
    $user = User::factory()->create();

    User::factory()->count(2)->create();

    $response = $this->actingAs($user)->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'data' => [
                        'type',
                        'user_id',
                        'attributes' => [
                            'name',
                            'username',
                            'email_verified_at',
                            'preferred_language',
                            'created_at_dates',
                            'updated_at_dates',
                        ],
                    ],
                    'links',
                    'meta',
                ],
            ],
            'links',
            'meta',
            'version',
        ]);

    $data = $response->json('data');
    expect(count($data))->toBe(3);
});

it('returns the authenticated user information for the get-me endpoint', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);

    $response = $this->actingAs($user)->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'type',
                'user_id',
                'has2FA',
                'attributes' => [
                    'name',
                    'username',
                    'email_verified_at',
                    'email',
                    'preferred_language',
                    'created_at_dates',
                    'updated_at_dates',
                ],
            ],
            'links',
            'meta',
            'version',
        ]);

    expect($response->json('data.attributes.username'))->toBe('testuser');
    expect($response->json('data.attributes.email'))->toBe('test@example.com');
});

it('returns unauthorized for unauthenticated requests to the get-me endpoint', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);
});

it('returns the user when finding by id and the user exists', function () {
    $authUser = User::factory()->create();
    $user = User::factory()->create([
        'username' => 'founduser',
        'email' => 'found@example.com',
    ]);

    $response = $this->actingAs($authUser)->getJson("/api/user/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'type',
                'user_id',
                'attributes' => [
                    'name',
                    'username',
                    'email_verified_at',
                    'preferred_language',
                    'created_at_dates',
                    'updated_at_dates',
                ],
            ],
            'links',
            'meta',
            'version',
        ]);

    expect($response->json('data.user_id'))->toBe($user->id);
    expect($response->json('data.attributes.username'))->toBe('founduser');
});

it('returns 404 when finding by id for a non-existent user', function () {
    $authUser = User::factory()->create();
    $nonExistentId = '99999999-9999-9999-9999-999999999999';

    $response = $this->actingAs($authUser)->getJson("/api/user/{$nonExistentId}");

    $response->assertStatus(404);
});

it('returns the user when finding by username and the user exists', function () {
    $authUser = User::factory()->create();

    $user = User::factory()->create([
        'username' => 'uniqueuser',
        'email' => 'unique@example.com',
    ]);

    $response = $this->actingAs($authUser)->postJson('/api/user/username', [
        'username' => $user->username,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'type',
                'user_id',
                'attributes' => [
                    'name',
                    'username',
                    'email_verified_at',
                    'preferred_language',
                    'created_at_dates',
                    'updated_at_dates',
                ],
            ],
            'links',
            'meta',
            'version',
        ]);

    expect($response->json('data.user_id'))->toBe($user->id);
    expect($response->json('data.attributes.username'))->toBe('uniqueuser');
});

it('returns success with null data when finding by username for a non-existent user', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser)->postJson('/api/user/username', [
        'username' => 'nonexistentuser',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => null,
        ]);
});

it('returns the user when finding by email and the user exists', function () {
    $authUser = User::factory()->create();

    $user = User::factory()->create([
        'username' => 'emailuser',
        'email' => 'email@example.com',
    ]);

    $response = $this->actingAs($authUser)->getJson("/api/user/email/{$user->email}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'type',
                'user_id',
                'attributes' => [
                    'name',
                    'username',
                    'email_verified_at',
                    'preferred_language',
                    'created_at_dates',
                    'updated_at_dates',
                ],
            ],
            'links',
            'meta',
            'version',
        ]);

    expect($response->json('data.user_id'))->toBe($user->id);
    expect($response->json('data.attributes.username'))->toBe('emailuser');
});

it('returns success with null data when finding by email for a non-existent address', function () {
    $authUser = User::factory()->create();

    $response = $this->actingAs($authUser)->getJson('/api/user/email/nonexistent@example.com');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => null,
        ]);
});

it('includes all required fields in the user resource structure', function () {
    $user = User::factory()->create([
        'name' => 'Resource Test User',
        'username' => 'resourceuser',
        'email' => 'resource@example.com',
    ]);

    $response = $this->actingAs($user)->getJson("/api/user/{$user->id}");

    $response->assertStatus(200);
    $data = $response->json('data');

    expect($data)->toHaveKeys(['type', 'user_id', 'attributes']);
    expect($data['type'])->toBe('user');
    expect($data['user_id'])->toBe($user->id);
    expect($data['attributes'])->toHaveKeys([
        'name',
        'username',
        'email_verified_at',
        'preferred_language',
        'created_at_dates',
        'updated_at_dates',
    ]);

    expect($data['attributes']['created_at_dates'])->toHaveKeys(['created_at_human', 'created_at']);
    expect($data['attributes']['updated_at_dates'])->toHaveKeys(['updated_at_human', 'updated_at']);
});
