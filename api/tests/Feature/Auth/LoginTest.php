<?php

namespace Tests\Feature\Auth;

it('returns the correct JSON structure after successful login', function () {
    $this->postJson('/api/register', [
        'firstname' => 'Test',
        'lastname' => 'User',
        'username' => 'testuser1',
        'lang' => 'en_US',
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response = $this->followingRedirects()->postJson('/api/login', [
        'email' => 'testuser@example.com',
        'password' => 'Password123!',
    ]);

    $this->assertTrue(in_array($response->getStatusCode(), [200, 302]));

    $this->assertAuthenticated();

    $contentType = $response->headers->get('content-type') ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $response->assertJsonStructure([
            'success',
            'message',
            'user',
            'two_factor',
            'redirect_url',
        ])->assertJson(['success' => true]);
    }
});

it('returns validation errors for missing credentials', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['email', 'password'],
        ]);
});

it('returns error for invalid credentials', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'noone@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors',
        ]);
});
