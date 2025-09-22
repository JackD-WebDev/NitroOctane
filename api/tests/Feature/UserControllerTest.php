<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake(); // Prevent emails during user creation
});

it('index returns all users with correct structure', function () {
    // Create authenticated user first
    $user = User::factory()->create();
    
    // Create additional users
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
                        // Note: has2FA is only included for some users (like authenticated user)
                        'attributes' => [
                            'name',
                            'username',
                            'email_verified_at',
                            'preferred_language',
                            'created_at_dates',
                            'updated_at_dates'
                            // Note: email only included for own user profile
                        ]
                    ],
                    'links',
                    'meta'
                ]
            ],
            'links',
            'meta',
            'version'
        ]);
    
    // Verify we get all users (3 total: authenticated user + 2 created)
    $data = $response->json('data');
    expect(count($data))->toBe(3);
});

it('get me returns authenticated user information', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com'
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
                    'updated_at_dates'
                ]
            ],
            'links',
            'meta',
            'version'
        ]);
    
    expect($response->json('data.attributes.username'))->toBe('testuser');
    expect($response->json('data.attributes.email'))->toBe('test@example.com');
});

it('get me returns unauthorized for unauthenticated user', function () {
    $response = $this->getJson('/api/user');
    
    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]);
});

it('find by id returns user when found', function () {
    $authUser = User::factory()->create(); // Create authenticated user
    
    $user = User::factory()->create([
        'username' => 'founduser',
        'email' => 'found@example.com'
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
                    'updated_at_dates'
                    // Note: has2FA and email not included when viewing other users
                ]
            ],
            'links',
            'meta',
            'version'
        ]);
    
    expect($response->json('data.user_id'))->toBe($user->id);
    expect($response->json('data.attributes.username'))->toBe('founduser');
});

it('find by id returns 404 for non-existent user', function () {
    $authUser = User::factory()->create(); // Create authenticated user
    $nonExistentId = '99999999-9999-9999-9999-999999999999';
    
    $response = $this->actingAs($authUser)->getJson("/api/user/{$nonExistentId}");
    
    $response->assertStatus(404);
});

it('find by username returns user when found', function () {
    $authUser = User::factory()->create(); // Create authenticated user
    
    $user = User::factory()->create([
        'username' => 'uniqueuser',
        'email' => 'unique@example.com'
    ]);
    
    // This should be a POST request according to the routes
    $response = $this->actingAs($authUser)->postJson('/api/user/username', [
        'username' => $user->username
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
                    'updated_at_dates'
                ]
            ],
            'links',
            'meta',
            'version'
        ]);
    
    expect($response->json('data.user_id'))->toBe($user->id);
    expect($response->json('data.attributes.username'))->toBe('uniqueuser');
});

it('find by username returns success with null data for non-existent user', function () {
    $authUser = User::factory()->create();
    
    $response = $this->actingAs($authUser)->postJson('/api/user/username', [
        'username' => 'nonexistentuser'
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => null
        ]);
});

it('find by email returns user when found', function () {
    $authUser = User::factory()->create(); // Create authenticated user
    
    $user = User::factory()->create([
        'username' => 'emailuser',
        'email' => 'email@example.com'
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
                    'updated_at_dates'
                ]
            ],
            'links',
            'meta',
            'version'
        ]);
    
    expect($response->json('data.user_id'))->toBe($user->id);
    expect($response->json('data.attributes.username'))->toBe('emailuser');
});

it('find by email returns success with null data for non-existent email', function () {
    $authUser = User::factory()->create();
    
    $response = $this->actingAs($authUser)->getJson('/api/user/email/nonexistent@example.com');
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => null
        ]);
});

it('user resource structure includes all required fields', function () {
    $user = User::factory()->create([
        'name' => 'Resource Test User',
        'username' => 'resourceuser',
        'email' => 'resource@example.com'
    ]);
    
    $response = $this->actingAs($user)->getJson("/api/user/{$user->id}");
    
    $response->assertStatus(200);
    $data = $response->json('data');
    
    // Verify resource structure
    expect($data)->toHaveKeys(['type', 'user_id', 'attributes']);
    expect($data['type'])->toBe('user');
    expect($data['user_id'])->toBe($user->id);
    expect($data['attributes'])->toHaveKeys([
        'name',
        'username',
        'email_verified_at',
        'preferred_language',
        'created_at_dates', 
        'updated_at_dates'
        // Note: email and has2FA not included when viewing other users
    ]);
    
    // Verify nested structures
    expect($data['attributes']['created_at_dates'])->toHaveKeys(['created_at_human', 'created_at']);
    expect($data['attributes']['updated_at_dates'])->toHaveKeys(['updated_at_human', 'updated_at']);
});