<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;

it('returns the correct JSON structure after successful registration', function () {
    $response = $this->post('/api/register', [
        'firstname' => 'Test',
        'middlename' => 'Middle',
        'lastname' => 'User',
        'username' => 'testuser1',
        'lang' => 'en_US',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertJsonFragment([
        'message' => 'TESTUSER1 REGISTERED SUCCESSFULLY',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'user' => [
                'id',
                'name',
                'username',
                'preferred_language',
                'email',
                'created_at',
                'updated_at',
            ],
            'redirect_url',
        ]);
});

it('returns an error for missing registration fields', function () {
    $response = $this->post('/api/register', [], [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'The firstname field is required. (and 4 more errors)',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['firstname', 'lastname', 'username', 'email', 'password'],
        ]);
});

it('returns an error for duplicate registration', function () {
    User::factory()->create([
        'email' => 'testuser@example.com',
    ]);

    $response = $this->post('/api/register', [
        'name' => 'TestUser_1',
        'username' => 'testuser1',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ], [
        'Accept' => 'application/json',
    ]);

    $response->assertJsonFragment([
        'message' => 'The firstname field is required. (and 2 more errors)',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['email', 'firstname', 'lastname'],
        ]);
});
